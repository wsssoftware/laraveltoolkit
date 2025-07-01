<?php

namespace Laraveltoolkit\Actions\Flash;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laraveltoolkit\Facades\Flash;
use Laraveltoolkit\Flash\FlashResource;

class GetMessages
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(FlashResource::collection(Flash::pullMessages()));
    }
}
