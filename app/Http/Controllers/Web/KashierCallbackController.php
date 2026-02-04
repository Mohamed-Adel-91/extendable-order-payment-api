<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Payments\Callback\KashierPaymentCallbackService;
use Illuminate\Http\Request;

class KashierCallbackController extends Controller
{
    public function __construct(private readonly KashierPaymentCallbackService $callbackService)
    {
    }

    public function handle(Request $request)
    {
        $result = $this->callbackService->handleKashierCallback($request->query());

        return response()->json([
            'ok' => true,
            'message' => 'Callback processed',
            'data' => $result,
        ]);
    }
}
