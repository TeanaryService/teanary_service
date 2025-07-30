<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $paymentService)
    {
        try {
            $payload = $request->all();
            Log::info('PayPal Webhook received', $payload);

            if ($payload['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
                $orderId = $payload['resource']['custom_id'] ?? null;
                if ($orderId && $order = Order::where('order_no', $orderId)->first()) {
                    $paymentService->handlePaymentSuccess($order);
                    return response()->json(['message' => 'success']);
                }
            }

            return response()->json(['message' => 'unhandled event']);
        } catch (\Exception $e) {
            Log::error('PayPal Webhook Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
