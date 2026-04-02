<?php

namespace App\Http\Controllers;

use App\Services\BrainBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrainBotController extends Controller
{
    public function __invoke(Request $request, BrainBotService $brainBot): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = $brainBot->answer($data['message']);

        return response()->json($result);
    }
}
