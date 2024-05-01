<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePdf;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\DataArraySerializer;

class BaseController extends Controller
{
    /**
     * @var Manager
     */
    protected $responseManager;

    public function __construct()
    {
        $this->responseManager = new Manager();
        $this->responseManager->setSerializer(new DataArraySerializer());
    }

    /**
     * @param ResourceAbstract $resource
     * @param array $additionalParams
     * @return array|\Illuminate\Http\JsonResponse
     */
    protected function successResponse(ResourceAbstract $resource, $additionalParams = [])
    {
        $response = $this->responseManager->createData($resource)->toArray();
        if (! empty($additionalParams)) {
            return response()->json(
                array_merge(
                    $response,
                    $additionalParams
                )
            );
        }

        return $response;
    }
}
