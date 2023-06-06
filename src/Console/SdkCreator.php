<?php

namespace Smart\Gii\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\SdkCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个sdk包
 *
 * @author Rocky<softfc@163.com>
 */
class SdkCreator extends BaseCreator
{
    /**
     * @var string
     */
    protected $name = 'smart:make-sdk';

    /**
     * @var string
     */
    protected $description = '创建一个sdk包';

    /**
     * @var string
     */
    protected $type = 'SDK';

    /**
     * @throws FileNotFoundException
     * @return bool|null
     */
    public function handle()
    {
        return parent::handle();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildClass($name)
    {
        return (new SdkCreatorService())
            ->setClassName($this->argument('name'))
            ->setPackage($this->argument('package'))
            ->getFileContent();
    }

    /**
     * {@inheritdoc}
     */
    protected function getStub()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getPath($name)
    {
        if ($path = $this->option('path')) {
            return $path . '/' . str_replace('\\', '/', $name) . '.php';
        }

        return parent::getPath($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Sdk相关类名称'],
            ['package', InputArgument::REQUIRED, 'Sdk包'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, '是否强制执行'],
            ['path', null, InputOption::VALUE_NONE, '类的存放路径'],
        ];
    }
}
