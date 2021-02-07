<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\ResourceRepository;
use Smart\Gii\Http\request\ResourceRequestForm;

class ResourceController extends Controller
{
    /**
     * @var ResourceRepository
     */
    public $repository;

    /**
     * ResourceController constructor.
     * @param ResourceRepository $repository
     */
    public function __construct(ResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::resource');
    }

    /**
     * 预览
     * @param ResourceRequestForm $request
     * @return array[]
     */
    public function preview(ResourceRequestForm $request)
    {
        return [
            'files' => [
                $this->repository->checkFile(
                    $request->input('resource'),
                    $request->input('model')
                ),
            ],
        ];
    }

    /**
     * 生成
     * @param ResourceRequestForm $request
     * @return array
     */
    public function generate(ResourceRequestForm $request)
    {
        $file = $this->repository->runCommand(
            $class = $request->input('resource'),
            $request->input('model')
        );

        if (!$file) {
            return [[
                'isDone' => false,
                'file' => $class,
            ]];
        }

        return [[
            'isDone' => (bool)$file,
            'file' => $file,
        ]];
    }
}
