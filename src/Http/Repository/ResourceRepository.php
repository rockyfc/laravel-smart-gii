<?php

namespace Smart\Gii\Http\Repository;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Smart\Gii\Console\ResourceCreator;
use Smart\Gii\Services\ResourceCreatorService;

class ResourceRepository extends BaseRepository
{
    /**
     * 检查文件，返回一个检查结果数组
     * @param $class 要创建的类名称
     * @param $modelClass model类名称
     * @return array
     */
    public function checkFile($class, $modelClass)
    {
        $exist = $this->isExistClass($class);

        return [
            'file' => $class . '.php',
            'action' => $action = $this->getAction($exist, $this->hasSameContent($class, $modelClass)),
            'checked' => 'create' == $action,
            'isExistClass' => $exist,
        ];
    }

    /**
     * 执行系统命令，生成resource类
     * @param $resourceClass
     * @param $modelClass
     * @return bool|string
     */
    public function runCommand($resourceClass, $modelClass)
    {
        Artisan::call(
            app(ResourceCreator::class)->getName(),
            [
                'name' => $resourceClass,
                'model' => $modelClass,
                '--force' => true,
            ]
        );

        try {
            return $this->getFilePathByClassName($resourceClass);
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * 判断需要生成的类文件是否和已经存在的文件内容一致
     * @param $class
     * @param $modelClass
     * @return bool
     */
    protected function hasSameContent($class, $modelClass)
    {
        if (!$this->isExistClass($class)) {
            return false;
        }

        try {
            $newFileContent = (new ResourceCreatorService(
                $class,
                $modelClass
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
}
