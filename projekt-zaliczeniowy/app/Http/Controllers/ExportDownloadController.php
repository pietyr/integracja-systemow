<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\IndicatorApiController;
use Illuminate\Http\Request;

class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, IndicatorApiController $api)
    {
        return $api->export($request);
    }
}
