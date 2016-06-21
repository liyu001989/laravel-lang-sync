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
    protected $signature = 'lang:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync lang files';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //先写死，应该用参数传进来
        $path = base_path('resources/lang/zh');
        $targetPath = base_path('resources/lang/en');
        $dir = opendir($path);

        // 计算文件个数
        $fileNum = sizeof(scandir($path));
        $fileNum = $fileNum > 2 ? $fileNum - 2 : 0;
        $bar = $this->output->createProgressBar($fileNum);

        //列出 images 目录中的文件
        while (($file = readdir($dir)) !== false) {
            // 忽略隐藏文件
            if ($file == '.' || $file == '..' || starts_with($file, '.') || filetype($path.$file) == 'dir') {
                continue;
            }

            //源文件
            $transArr = require $path.$file;
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
