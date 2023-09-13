<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
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
            if (isset($order->transaction_id)) {
                return response()->json(['status' => 'error', 'message' => 'Đơn hàng đã được thanh toán']);
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
                'amount' => ($order->total_order * 100) / 100,
                'currency' => 'VND',
                'source' => $request->input('token'),
            ]);
//
//            return response()->json($res['outcome']->risk_score);
//            Check kết quả thực hiên xử lý sau khi giao dịch thành công....
            if ($res['status'] === "succeeded") {
                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'payment_method' => "stripe",
                    'amount' => $res['amount'],
                    'status' => 'done',
                ]);
                $transaction->transaction_id_return = $res['id'];
                $transaction->gateway_response = $res['outcome']->risk_score;
                $transaction->user_id = $order->user_id;
                $order->transaction_id = $transaction->id;
                $order->save();
                $transaction->save();
            }
            return response()->json($res['status']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Check error - TransactionController.createStripePayment'
            ]);
        }
    }

    public function createVNPayLink(Request $request): JsonResponse
    {

        $order = Order::where('order_code', $request->input('order_code'))->first();
        if (!isset($order)) {
            return response()->json(['status' => 'error', 'message' => 'Khong tim thay san pham']);
        }
        $vnp_TmnCode = "OWHBWUCN"; //Mã website tại VNPAY
        $vnp_HashSecret = "MYOJXZUEFEVHPYULUCHQCRJMTHPXHLFA";
        $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Locale = $request->get('lang') ?? 'vi';
        $vnp_IpAddr = request()->ip();
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $order->total_order * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $startTime,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => "Pay for invoice: ".$order->order_code,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $request->input('url_return'),
            "vnp_TxnRef" => $order->order_code,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key)."=".urlencode($value);
            } else {
                $hashdata .= urlencode($key)."=".urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key)."=".urlencode($value).'&';
        }

        $vnp_Url = $vnp_Url."?".$query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);//
        $vnp_Url .= 'vnp_SecureHash='.$vnpSecureHash;

        return response()->json($vnp_Url);
    }

    public function runVNPay(Request $request): JsonResponse
    {
        $order = Order::where('order_code', $request->vnp_TxnRef)->first();
        if (!isset($order)) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
        }
        if (isset($order->transaction_id)) {
            return response()->json(['status' => 'error', 'message' => 'Đơn hàng đã được thanh toán']);
        }
        $vnp_HashSecret = "MYOJXZUEFEVHPYULUCHQCRJMTHPXHLFA";
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData.'&'.urlencode($key)."=".urlencode($value);
            } else {
                $hashData = $hashData.urlencode($key)."=".urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'payment_method' => "VNPay",
                    'amount' => $request->vnp_Amount / 100,
                    'status' => 'done',
                ]);
                $transaction->transaction_id_return = $request->vnp_TransactionNo;
                $transaction->gateway_response = $request->vnp_ResponseCode;
                $transaction->user_id = $order->user_id;
                $order->transaction_id = $transaction->id;
                $order->save();
                $transaction->save();

                return response()->json(['status' => "ok", 'message' => "Thanh toán thành công"]);
            } else {
                return response()->json([
                    'status' => "error", 'message' => "Lỗi trong quá trình thanh toán phí dịch vụ"
                ]);
            }
        } else {
            return response()->json(['status' => "error", 'message' => "Dữ liệu không đúng"]);
        }
    }

//    public function fetchListTransaction(Request $request): JsonResponse
//    {
//        $transaction = Transaction::with('order:id,order_code')->orderByDesc('created_at');
//        if($request->query_id !== null){
//            $transaction = $transaction->find($request->query_id);
//        }
//        if($request->payment_method !== null){
//            $transaction = $transaction->where('payment_method',$request->payment_method);
//        }
//        $transactionList = $transaction->paginate(10);
//        return response()->json([$transactionList]);
//    }
    public function fetchListTransaction(): JsonResponse
    {
        $transaction = Transaction::with('order:id,order_code')->get();

        return response()->json($transaction);
    }
}
