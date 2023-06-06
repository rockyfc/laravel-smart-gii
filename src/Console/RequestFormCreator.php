<?php

namespace Smart\Gii\Console;

use Doctrine\DBAL\Types\Type;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\RequestFormCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个RequestForm类
 * @author Rocky<softfc@163.com>
 */
class RequestFormCreator extends BaseCreator
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'smart:make-request-form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个RequestForm表单验证类并且使用一个model类作为资源。';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'FormRequest';

    /**
     * @throws FileNotFoundException
     * @return bool|null
     */
    public function handle()
    {
        return parent::handle();
    }

    /**
     * @param string $name
     * @throws FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     * @date 2020-07-14 16:01
     */
    protected function buildClass($name)
    {
        return (new RequestFormCreatorService(
            $this->argument('name'),
            $this->argument('model')
        ))->getFileContent();
    }

    /**
     * 获取模板文件地址
     *
     * @return string
     */
    protected function getStub()
    {
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, '表单类名称'],
            ['model', InputArgument::OPTIONAL, '表单对应的model类'],
        ];
    }

    /**
     * @return array|array[]
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, '是否强制执行'],
        ];
    }
}
