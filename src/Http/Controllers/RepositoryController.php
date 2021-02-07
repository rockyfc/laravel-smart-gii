<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\FormRepository;
use Smart\Gii\Http\Repository\RepositoryRepository;
use Smart\Gii\Http\request\RepositoryRequestForm;

class RepositoryController extends Controller
{
    /**
     * @var FormRepository
     */
    public $repository;

    /**
     * RepositoryController constructor.
     * @param RepositoryRepository $repository
     */
    public function __construct(RepositoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::repository');
    }

    /**
     * 预览
     * @param RepositoryRequestForm $request
     * @return array[]
     */
    public function preview(RepositoryRequestForm $request)
    {
        return [
            'files' => [
                $this->repository->checkFile(
                    $request->input('repository'),
                    $request->input('model')
                ),
            ],
        ];
    }

    /**
     * 生成
     * @param RepositoryRequestForm $request
     * @return array
     */
    public function generate(RepositoryRequestForm $request)
    {
        $file = $this->repository->runCommand(
            $class = $request->input('repository'),
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
