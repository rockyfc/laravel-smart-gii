<?php

namespace Smart\Gii\Http\Controllers;

use App\Models\Content;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use ReflectionException;
use Smart\Common\Helpers\Inflector;
use Smart\Gii\Console\ModelCreator;
use Smart\Gii\Http\Repository\ModelRepository;
use Smart\Gii\Http\request\ModelRequestForm;
use Smart\Gii\Services\ModelFixerServices;

class ModelController extends Controller
{
    /**
     * @var ModelRepository
     */
    public $repository;

    /**
     * ModelController constructor.
     * @param ModelRepository $repository
     */
    public function __construct(ModelRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::model', [
            'connections' => $this->repository->getMysqlConnections(),
            'defaultConnections' => $default = $this->repository->getDefaultConnection(),
            'tables' => $this->repository->getTablesForConnection($default),
        ]);
    }

    public function showFixer()
    {
        return view('gii::model_fixer', [
            'models' => $this->repository->getModifiedModels()
        ]);
    }

    /**
     * @param ModelRequestForm $form
     * @return int
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    public function fixer(ModelRequestForm $form)
    {
        $service = new ModelFixerServices($form->post('model'));
        if ($form->post('type') == 1) {
            $service->setIsReset(true);
        }

        $service->fix();
        $form->session()->flash('_message', '操作成功');
        return true;

    }

    /**
     * ajax 根据连接获取表名称
     * @param $connection
     * @return array
     */
    public function tables($connection)
    {
        try {
            $tables = $this->repository->getTablesForConnection($connection);

            return [
                'connection;' => $connection,
                'tables' => $tables,
            ];
        } catch (\Doctrine\DBAL\Driver\PDOException $e) {
            return [
                'error' => '请检查是否正确配置了' . $connection . '连接',
                'connection;' => $connection,
            ];
        }
    }

    /**
     * 将数据表名称转化成model名称
     * @param $table
     * @return array[]
     */
    public function tableToModel($table)
    {
        return [
            'name' => Inflector::classify(
                Inflector::spellOut($table)
            ),
        ];
    }

    /**
     * 预览
     * @param ModelRequestForm $request
     * @return array[]
     */
    public function preview(ModelRequestForm $request)
    {
        return [
            'files' => [
                $this->repository->checkFile(
                    $request->input('model'),
                    $request->input('baseModel'),
                    $request->input('table'),
                    $request->input('connection')
                ),
            ],
        ];
    }

    /**
     * @param ModelRequestForm $request
     * @return array[]
     * @throws ReflectionException
     */
    public function generate(ModelRequestForm $request)
    {
        Artisan::call(
            app(ModelCreator::class)->getName(),
            [
                'name' => $class = $request->input('model'),
                'table' => $request->input('table'),
                'baseModel' => $request->input('baseModel'),
                'connection' => $request->input('connection'),
                '--force' => true,
            ]
        );

        $file = $this->repository->getFilePathByClassName($class);

        return [[
            'isDone' => file_exists($file),
            'file' => $file,
        ]];
    }
}
