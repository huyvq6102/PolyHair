<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VnpayService
{
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $vnp_Url;
    protected $vnp_Returnurl;

    public function __construct()
    {
        $this->vnp_TmnCode = env('VNP_TMN_CODE'); // Mã website tại VNPAY 
        $this->vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật
        $this->vnp_Url = env('VNP_URL');
        $this->vnp_Returnurl = route('site.payments.vnpay.return');
    }

    public function createPayment($orderId, $amount, $orderInfo = 'Thanh toan don hang')
    {
        $vnp_TxnRef = $orderId; // Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = $orderInfo;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $amount * 100; // VNPAY nhân 100 (ví dụ 10.000 VND = 1000000)
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();
        
        if ($vnp_IpAddr == '::1') {
            $vnp_IpAddr = '127.0.0.1';
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    // Hàm kiểm tra chữ ký khi VNPAY trả về
    public function checkSignature($requestData)
    {
        $vnp_SecureHash = $requestData['vnp_SecureHash'] ?? '';
        
        // Loại bỏ các tham số không tham gia vào quá trình tạo hash
        unset($requestData['vnp_SecureHash']);
        unset($requestData['vnp_SecureHashType']);

        ksort($requestData);
        $i = 0;
        $hashData = "";
        foreach ($requestData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        
        return $secureHash === $vnp_SecureHash;
    }
}
