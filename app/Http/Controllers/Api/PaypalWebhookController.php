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

            // 基本验证：检查必要字段
            if (! isset($payload['event_type']) || ! isset($payload['resource'])) {
                Log::warning('PayPal Webhook: Invalid payload structure', $payload);

                return response()->json(['error' => 'Invalid payload'], 400);
            }

            Log::info('PayPal Webhook received', [
                'event_type' => $payload['event_type'],
                'resource_id' => $payload['resource']['id'] ?? null,
            ]);

            if ($payload['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
                $orderId = $payload['resource']['custom_id'] ?? null;

                if (! $orderId) {
                    Log::warning('PayPal Webhook: Missing custom_id (order_no)');

                    return response()->json(['error' => 'Missing order identifier'], 400);
                }

                $order = Order::where('order_no', $orderId)->first();

                if (! $order) {
                    Log::warning('PayPal Webhook: Order not found', ['order_no' => $orderId]);

                    return response()->json(['error' => 'Order not found'], 404);
                }

                $paymentService->handlePaymentSuccess($order);

                return response()->json(['message' => 'success']);
            }

            return response()->json(['message' => 'unhandled event']);
        } catch (\Exception $e) {
            Log::error('PayPal Webhook Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
