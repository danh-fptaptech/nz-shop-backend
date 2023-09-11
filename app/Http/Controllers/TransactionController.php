<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe;
class TransactionController extends Controller
{
    public function createStripePayment(Request $request): JsonResponse
    {
        try {
            $secretKey = config('services.stripe.secret_key');
            $stripe = new \Stripe\StripeClient($secretKey);
            $order = Order::where('order_code', $request->input('order_code'))->first();
            if (!isset($order)) {
                return response()->json(['status' => 'error', 'message' => 'Khong tim thay san pham']);
            }
            \Stripe\Stripe::setApiKey($secretKey);
//            $source = $stripe->tokens->create([
//                'card' => [
//                    'number' => 'tok_visa_debit',
//                    'exp_month' => $request->input('exp_month'),
//                    'exp_year' => $request->input('exp_year'),
//                    'cvc' => $request->input('cvc'),
//                ],
//            ]);

//            $customer = $stripe->customers->create([
//                'email'=>$order->email_buyer,
//                'description' => 'Pay for order code: '.$order->order_code,
//            ]);

            $res = $stripe->charges->create([
                'amount' => ($order->total_order*100)/100,
                'currency' => 'VND',
                'source' => $request->input('token'),
            ]);

//            Check kết quả thực hiên xử lý sau khi giao dịch thành công....
//            Chưa kịp viết tiếp.

            return response()->json($res['status']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - TransactionController.createStripePayment'
            ]);
        }
    }

    public function createVNPayPayment()
    {

    }
}
