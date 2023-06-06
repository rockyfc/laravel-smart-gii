<?php

namespace Smart\Gii\Http\Repository;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Smart\Gii\Console\ControllerCreator;
use Smart\Gii\Services\ControllerCreatorService;

class CtrlRepository extends BaseRepository
{
    /**
     * 生成controller所对应的路由
     * @var
     */
    protected $routeName;

    /**
     * 路由格式代码
     * @var
     */
    protected $routeStyleCode = [];

    /**
     * 检查文件，返回一个检查结果数组
     * @param $class
     * @param $baseClass
     * @param $formClass
     * @param $resourceClass
     * @param $modelClass
     * @param $repositoryClass
     * @return array
     */
    public function checkFile($class, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)
    {
        $exist = $this->isExistClass($class);

        return [
            'file' => $class . '.php',
            'action' => $action = $this->getAction($exist, $this->hasSameContent($class, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)),
            'checked' => 'create' == $action,
            'isExistClass' => $exist,
        ];
    }

    /**
     * 生成Controller
     * @param $controllerClass
     * @param $baseClass
     * @param $formClass
     * @param $resourceClass
     * @param $modelClass
     * @param $repositoryClass
     * @return bool|false|string
     */
    public function runCommand($controllerClass, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)
    {
        Artisan::call(
            app(ControllerCreator::class)->getName(),
            [
                'name' => $controllerClass,
                'baseClass' => $baseClass,
                'form' => $formClass,
                'resource' => $resourceClass,
                'model' => $modelClass,
                'repository' => $repositoryClass,
                '--force' => true,
            ]
        );

        $this->routeName = $this->service(
            $controllerClass,
            $baseClass,
            $formClass,
            $resourceClass,
            $modelClass,
            $repositoryClass
        )->getRouteName();

        // $this->routeStyleCode = "Route::resource('prefix_name/" . $this->routeName . "', '{$controllerClass}')->only(['index', 'show', 'store', 'update', 'destroy']);";

        $routePrefix = strtolower(Str::snake($this->routeName, '-'));
        $this->routeStyleCode[0] = "Route::get('{$routePrefix}/index', ['{$controllerClass}', 'index'])->name('{$routePrefix}.index');";
        $this->routeStyleCode[1] = "Route::get('{$routePrefix}/show/{" . $this->routeName . "}', ['{$controllerClass}', 'show'])->name('{$routePrefix}.show');";
        $this->routeStyleCode[2] = "Route::post('{$routePrefix}/store', ['{$controllerClass}', 'store'])->name('{$routePrefix}.store');";
        $this->routeStyleCode[3] = "Route::put('{$routePrefix}/update/{" . $this->routeName . "}', ['{$controllerClass}', 'update'])->name('{$routePrefix}.update');";
        $this->routeStyleCode[4] = "Route::delete('{$routePrefix}/destroy/{" . $this->routeName . "}', ['{$controllerClass}', 'destroy'])->name('{$routePrefix}.destroy');";

        try {
            return $this->getFilePathByClassName($controllerClass);
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * 获取建议的路由代码
     * @return mixed
     */
    public function getSuggestRouteCode()
    {
        return $this->routeStyleCode;
    }

    /**
     * 根据controller联想其他类名称
     * @param $controller
     * @return array
     */
    public function guess($controller)
    {
        $arr = explode('\\', $controller);
        $name = ucfirst(array_pop($arr));
        array_pop($arr);
        $namespace = implode('\\', $arr);

        if (Str::endsWith($name, 'Controller')) {
            $name = substr($name, 0, -10);
        }

        return [
            'controller' => $this->formatClass($controller, $name ? $this->getControllerSuffix() : ''),
            'form' => $name ? $this->guessForm($namespace, $name) : '',
            'resource' => $name ? $this->guessResource($namespace, $name) : '',
            'model' => $name ? $this->guessModel($namespace, $name) : '',
            'repository' => $name ? $this->formatClass($namespace . '\Repositories\\' . $name, $this->getRepositorySuffix()) : '',
        ];
    }

    /**
     * 判断需要生成的类文件是否和已经存在的文件内容一致
     * @param $class
     * @param $baseClass
     * @param $formClass
     * @param $resourceClass
     * @param $modelClass
     * @param $repositoryClass
     * @return bool
     */
    protected function hasSameContent($class, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)
    {
        if (!$this->isExistClass($class)) {
            return false;
        }

        try {
            $service = $this->service($class, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass);
            $newFileContent = $service->getFileContent();
            $this->routeName = $service->getRouteName();

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

    /**
     * @param $controllerClass
     * @param $baseClass
     * @param $formClass
     * @param $resourceClass
     * @param $modelClass
     * @param $repositoryClass
     * @return ControllerCreatorService
     */
    protected function service($controllerClass, $baseClass, $formClass, $resourceClass, $modelClass, $repositoryClass)
    {
        $service = (new ControllerCreatorService())
            ->setClassName($controllerClass)
            ->setBaseClass($baseClass)
            ->setFormClass($formClass)
            ->setResourceClass($resourceClass)
            ->setModelClass($modelClass)
            ->setRepositoryClass($repositoryClass);

        try {
            $service->getFileContent();
        } catch (FileNotFoundException $e) {
        }

        return $service;
    }
}
