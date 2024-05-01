<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateIcc;
use App\Services\IccService;
use App\Transformers\FontTransformer;
use App\Transformers\IccTransformer;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class IccController extends BaseController
{
    protected IccService $iccService;

    public function __construct(IccService $iccService)
    {
        parent::__construct();
        $this->iccService = $iccService;
    }

    /**
     * @param CreateIcc $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateIcc $request)
    {
        if ($this->iccService->getByName($request->name)) {
            throw new BadRequestHttpException('Icc already exists');
        }

        $filename = $request->get('name').'.icc';
        Storage::disk('icc')->put($filename, base64_decode($request->get('icc_file')));
        $result = $this->iccService->create(['name' => $request->get('name'), 'filename' => $filename]);

        return response()->json(['success' => $result]);
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successResponse(new Collection(
            $this->iccService->all(),
            new IccTransformer()
        ));
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getByName(Request $request)
    {
        if (! $icc = $this->iccService->getByName($request->get('name'))) {
            throw new NotFoundResourceException('Icc is not found', 404);
        }

        return $this->successResponse(new Item($icc, new IccTransformer()));
    }
}
