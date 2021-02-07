<?php

namespace Smart\Gii\Http\Repository;

use Illuminate\Support\Str;

class CurdRepository extends BaseRepository
{
    /**
     * 检查文件，返回一个检查结果数组
     * @param $controllerClass
     * @param $baseClass
     * @param $formClass
     * @param $resourceClass
     * @param $modelClass
     * @param $repositoryClass
     * @return array
     */
    public function checkFile($controllerClass, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)
    {
        return [
            (new FormRepository())->checkFile($formClass, $modelClass),
            (new ResourceRepository())->checkFile($resourceClass, $modelClass),
            (new RepositoryRepository())->checkFile($repositoryClass, $modelClass),
            (new CtrlRepository())->checkFile($controllerClass, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass),
        ];
    }

    /**
     * 生成Controller
     * @param $params
     * @param $classes
     * @return array
     */
    public function runCommand(array $params, array $classes)
    {
        $controllerClass = $params['ctrl'];
        $baseClass = $params['baseCtrl'];
        $formClass = $params['form'];
        $resourceClass = $params['resource'];
        $modelClass = $params['model'];
        $repositoryClass = $params['repository'];

        $ctrlRepository = new CtrlRepository();
        $result = [];

        //生成controller class
        if ($controllerClass && $this->inArray($controllerClass, $classes)) {
            if ($file = $ctrlRepository->runCommand($controllerClass, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)) {
                $result[] = ['isDone' => true, 'file' => $file];
            } else {
                $result[] = ['isDone' => false, 'file' => $controllerClass];
            }
        }

        //生成form class
        if ($formClass && $this->inArray($formClass, $classes)) {
            if ($file = (new FormRepository())->runCommand($formClass, $modelClass)) {
                $result[] = ['isDone' => true, 'file' => $file];
            } else {
                $result[] = ['isDone' => false, 'file' => $formClass];
            }
        }

        //生成resource class
        if ($resourceClass && $this->inArray($resourceClass, $classes)) {
            if ($file = (new ResourceRepository())->runCommand($resourceClass, $modelClass)) {
                $result[] = ['isDone' => true, 'file' => $file];
            } else {
                $result[] = ['isDone' => false, 'file' => $resourceClass];
            }
        }

        //生成repository class
        if ($repositoryClass && $this->inArray($repositoryClass, $classes)) {
            if ($file = (new RepositoryRepository())->runCommand($repositoryClass, $modelClass)) {
                $result[] = ['isDone' => true, 'file' => $file];
            } else {
                $result[] = ['isDone' => false, 'file' => $repositoryClass];
            }
        }

        return [
            'files' => $result,
            'code' => $ctrlRepository->getSuggestRouteCode(),
        ];
    }

    /**
     * 根据controller联想其他类名称
     * @param $controller
     * @return array
     */
    public function guessByController($controller)
    {
        $arr = explode('\\', $controller);
        $name = ucfirst(array_pop($arr));
        array_pop($arr);
        $namespace = implode('\\', $arr);

        if (Str::endsWith($name, 'Controller')) {
            $name = substr($name, 0, -10);
        }

        return [
            'controller' => $this->formatClass($controller, $this->getControllerSuffix()),
            'form' => $this->formatClass($namespace . '\Requests\\' . $name, $this->getFormRequestSuffix()),
            'resource' => $this->formatClass($namespace . '\Resources\\' . $name, $this->getResourceSuffix()),
            'repository' => $name ? $this->formatClass($namespace . '\Repositories\\' . $name, $this->getRepositorySuffix()) : '',
        ];
    }

    /**
     * 根据model联想其他类名称
     * @param $model
     * @return array
     */
    public function guessByModel($model)
    {
        $arr = explode('\\', $model);
        $name = ucfirst(array_pop($arr));
        $namespace = implode('\\', $arr);

        if (Str::endsWith($name, 'Model')) {
            $name = substr($name, 0, -5);
        }

        return [
            'controller' => $name ? $this->formatClass('App\Http\Controllers\\' . $name, $this->getControllerSuffix()) : '',
            'form' => $name ? $this->formatClass('App\Http\Requests\\' . $name, $this->getFormRequestSuffix()) : '',
            'resource' => $name ? $this->formatClass('App\Http\Resources\\' . $name, $this->getResourceSuffix()) : '',
            'repository' => $name ? $this->formatClass('App\Http\Repositories\\' . $name, $this->getRepositorySuffix()) : '',
            'model' => $this->formatClass($model, $this->getModelSuffix()),
        ];
    }

    /**
     * @param string $class
     * @param array $classes
     * @return bool
     */
    protected function inArray(string $class, array $classes)
    {
        $class = trim($class, '\\');
        foreach ($classes as &$cls) {
            $cls = str_ireplace('\\\\', '\\', $cls);
            $cls = trim($cls, '.php');
        }

        return in_array($class, $classes);
    }
}
