<?php

namespace Smart\Gii\Http\Repository;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Smart\Gii\Services\ConfigService;
use Smart\Gii\Services\ModelCreatorService;
use Smart\Gii\Services\ModelFixerServices;
use Symfony\Component\Finder\SplFileInfo;

class ModelRepository extends BaseRepository
{
    /**
     * 获取数据库配置
     * @return array
     */
    public function getConnections()
    {
        return config('database.connections');
    }

    /**
     * 获取mysql数据库配置
     * @return array
     */
    public function getMysqlConnections()
    {
        return array_filter($this->getConnections(), function ($value) {
            if ('mysql' == $value['driver']) {
                return true;
            }
        });
    }

    /**
     * 获取数据库默认连接
     * @return array
     */
    public function getDefaultConnection()
    {
        return config('database.default');
    }

    /**
     * 根据某个连接获取下面的所有数据表
     * @param $connectionName
     * @return mixed
     */
    public function getTablesForConnection($connectionName)
    {
        return DB::connection($connectionName)
            ->getDoctrineSchemaManager()
            ->listTableNames();
    }

    /**
     * 检查文件，返回一个检查结果数组
     * @param $class
     * @param $baseModelClass
     * @param $table
     * @param $connectionName
     * @return array
     */
    public function checkFile($class, $baseModelClass, $table, $connectionName)
    {
        $exist = $this->isExistClass($class);

        return [
            'file' => $class . '.php',
            'action' => $action = $this->getAction($exist, $this->hasSameContent($class, $baseModelClass, $table, $connectionName)),
            'checked' => 'create' == $action,
            'isExistClass' => $exist,
        ];
    }

    /**
     * 判断需要生成的类文件是否和已经存在的文件内容一致
     * @param $class
     * @param $baseModelClass
     * @param $table
     * @param $connectionName
     * @return bool
     */
    protected function hasSameContent($class, $baseModelClass, $table, $connectionName)
    {
        if (!$this->isExistClass($class)) {
            return false;
        }

        try {
            $newFileContent = (new ModelCreatorService(
                $class,
                $baseModelClass,
                $table,
                $connectionName
            ))->getFileContent();

            $new = md5($newFileContent);
            $old = md5_file($this->getFilePathByClassName($class));

            return $new == $old;
        } catch (FileNotFoundException $e) {
            echo '没有找到模板文件';

            return false;
        } catch (\ReflectionException $e) {
            echo '没有找到类文件';

            return false;
        }
    }


    public function getModifiedModels()
    {

        /** @var SplFileInfo[] $files */
        $models = [];
        foreach (ConfigService::modelPath() as $path) {
            $classMap = ClassMapGenerator::createMap($path);
            $models = array_merge($models, $classMap);
        }

        //print_r(ConfigService::modelPath());exit;

        $files = [];
        foreach ($models as $class => $filename) {
            $service = new ModelFixerServices($class);

            if ($service->getNewComment() !== $service->getOriginComment()) {
                //logger('new--->'.$service->getNewComment());
                //logger('old--->'.$service->getOriginComment());
                $files[$class] = $filename;
            }
        }


        return $files;
    }
}
