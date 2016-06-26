<?php

namespace Liyu\LaravelLangSync;

use Illuminate\Console\Command;

class LangSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:sync {source?} {target?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync lang files';

    protected $source;

    protected $target;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function check()
    {
        // check source dir
        if (! $source = $this->argument('source')) {
            $source = $this->ask('source dir in resources/lang/');

            if (! is_dir(base_path('resources/lang/').$source)) {
                $this->error("you don't have resources/lang/{$source} dir");

                return false;
            }

            $this->source = $source;
        }

        // check target dir
        if (! $target = $this->argument('target')) {
            $target = $this->ask('target dir in resources/lang/');

            if (! is_dir(base_path('resources/lang/').$target)) {
                $this->error("you don't have resources/lang/{$target} dir");

                return false;
            }

            $this->target = $target;
        }

        return true;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->check()) {
            return;
        }

        //先写死，应该用参数传进来
        $sourcePath = base_path('resources/lang/'.$this->source.'/');
        $targetPath = base_path('resources/lang/'.$this->target.'/');

        $dir = opendir($sourcePath);

        // 计算文件个数
        $fileNum = count(scandir($sourcePath));
        $fileNum = $fileNum > 2 ? $fileNum - 2 : 0;
        $bar = $this->output->createProgressBar($fileNum);

        //列出 images 目录中的文件
        while (($file = readdir($dir)) !== false) {
            // 忽略隐藏文件
            if (starts_with($file, '.') || filetype($sourcePath.$file) == 'dir') {
                continue;
            }

            //源文件
            $transArr = require $sourcePath.$file;
            //翻译后的目标文件
            $targetFile = $targetPath.$file;

            $targetArr = file_exists($targetFile) ? require($targetFile) : [];

            //进行数组调整
            array_walk($transArr, function (&$item, $key) use ($targetArr) {
                if (array_key_exists($key, $targetArr)) {
                    $item = $targetArr[$key];
                }
            });

            // 写入英文文件
            $content = '<?php'.PHP_EOL.'return '.var_export($transArr, true).';';
            file_put_contents($targetFile, $content);
            $bar->advance();
        }

        closedir($dir);
        $bar->finish();
        echo PHP_EOL;
    }
}
