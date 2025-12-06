<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpoint;

    public function __construct()
    {
        $this->partnerCode = env('MOMO_PARTNER_CODE');
        $this->accessKey = env('MOMO_ACCESS_KEY');
        $this->secretKey = env('MOMO_SECRET_KEY');
        $this->endpoint = env('MOMO_ENDPOINT');
    }

    public function createPayment($orderId, $amount, $orderInfo = 'Thanh toán đơn hàng PolyHair')
    {
        // Order ID của MoMo yêu cầu Unique, nên ta có thể thêm time() vào nếu cần,
        // nhưng tốt nhất là dùng mã Invoice Code (duy nhất) từ PaymentService.
        $requestId = (string)time() . "_" . $orderId;
        
        $redirectUrl = route('payment.momo.return'); 
        $ipnUrl = route('payment.momo.return');      
        $requestType = "captureWallet";
        $extraData = ""; // Pass empty string if not needed

        // Format amount to string/integer as required (no decimals usually allowed in signature raw string unless specified)
        $amount = (string)round($amount);

        // Construct signature string (Must be alphabetical order of keys)
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . $amount .
                   "&extraData=" . $extraData .
                   "&ipnUrl=" . $ipnUrl .
                   "&orderId=" . $orderId .
                   "&orderInfo=" . $orderInfo .
                   "&partnerCode=" . $this->partnerCode .
                   "&redirectUrl=" . $redirectUrl .
                   "&requestId=" . $requestId .
                   "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "PolyHair Store",
            'storeId'     => "PolyHair",
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        ];

        try {
            $response = Http::post($this->endpoint, $data);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Momo Payment Error: ' . $e->getMessage());
            return ['resultCode' => 500, 'message' => $e->getMessage()];
        }
    }
}
