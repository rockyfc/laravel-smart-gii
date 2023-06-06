<?php

namespace Smart\Gii\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Smart\Gii\Http\Repository\SdkRepository;
use Smart\Gii\Http\request\SdkRequestForm;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SdkController extends Controller
{
    /**
     * @var SdkRepository
     */
    public $repository;

    /**
     * SdkController constructor.
     * @param SdkRepository $repository
     */
    public function __construct(SdkRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('gii::sdk');
    }

    /**
     * 预览
     * @param SdkRequestForm $request
     * @throws FileNotFoundException
     * @return array[]
     */
    public function preview(SdkRequestForm $request)
    {
        return [
            'files' => $this->repository->checkFiles(
                $request->input('namespace')
            ),
        ];
    }

    /**
     * 生成
     * @param SdkRequestForm $request
     * @return array
     */
    public function generate(SdkRequestForm $request)
    {
        return [
            'sdk' => $this->repository->runCommand($request->input('namespace')),
            'zip' => $this->repository->generateSdkZip(),
        ];
    }

    /**
     * 下载
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function download(Request $request)
    {
        $file = $request->input('file') or exit('file params is missing');
        $target = str_replace('//', '/', $this->repository->getBasePath() . $file);

        if (file_exists($target)) {
            return response()->download($target);
        }

        exit('没有找到文件' . $file);
    }
}
