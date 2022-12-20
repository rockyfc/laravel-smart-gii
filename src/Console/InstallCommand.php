<?php

namespace Smart\Gii\Console;

use Illuminate\Console\Command;

class InstallCommand extends BaseCreator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smart:install-gii';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '安装所有的gii资源';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->comment('Publishing Gii Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'gii-provider']);

        $this->comment('Publishing Gii Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'gii-assets']);

        $this->comment('Publishing Gii Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'gii-config']);

        $this->info('gii installed successfully.');
    }
}
