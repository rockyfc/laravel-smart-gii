<?php

namespace Smart\Gii\Console;

use Doctrine\DBAL\DBALException;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\ModelCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个model类
 *
 * @author Rocky<softfc@163.com>
 */
class ModelCreator extends BaseCreator
{
    /**
     * @var string
     */
    protected $name = 'smart:make-model';

    /**
     * @var string
     */
    protected $description = '创建一个新的数据库实体类，相较与系统提供的make:model命令功能更加丰富，对文档生成更加友好';

    /**
     * @var string
     */
    protected $type = 'Model';

    /**
     * @throws FileNotFoundException
     * @return null|bool
     */
    public function handle()
    {
        return parent::handle();
    }

    /**
     * @param string $name
     *
     * @throws DBALException|FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     */
    protected function buildClass($name)
    {
        return (new ModelCreatorService(
            $this->argument('name'),
            $this->argument('baseModel'),
            $this->argument('table'),
            $this->argument('connection')
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
     * @return array|array[]
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Model类名称'],
            ['table', InputArgument::REQUIRED, '数据表名称'],
            ['baseModel', InputArgument::OPTIONAL, 'Model基类', 'Illuminate\Database\Eloquent\Model'],
            ['connection', InputArgument::OPTIONAL, '数据库连接名，在配置文件中可查看。', 'mysql'],
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
