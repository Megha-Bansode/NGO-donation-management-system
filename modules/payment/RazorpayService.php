<?php
/**
 * Purpose: Razorpay Service for handling payment integrations
 * Module: Payment
 */

// Include the Razorpay configuration
require_once __DIR__ . '/../../config/razorpay.php';
// Include Composer autoload for Razorpay SDK
require_once __DIR__ . '/../../vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService {
    
    private $keyId;
    private $keySecret;

    public function __construct() {
        $this->keyId = RAZORPAY_KEY_ID;
        $this->keySecret = RAZORPAY_KEY_SECRET;
    }

    /**
     * Prepare a new Razorpay order
     * 
     * @param float $amount Amount in INR
     * @param string $receipt Unique receipt identifier
     * @return array Order details
     */
    public function createOrder($amount, $receipt, $campaignId = null) {
        $amountInPaise = intval($amount * 100);
        
        $url = 'https://api.razorpay.com/v1/orders';
        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $receipt
        ];

        if ($campaignId) {
            $payload['notes'] = [
                'campaign_id' => (string)$campaignId
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }

        $responseData = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMessage = isset($responseData['error']['description']) 
                ? $responseData['error']['description'] 
                : 'Unknown Razorpay API Error';
            throw new Exception("Razorpay API Error: " . $errorMessage);
        }

        return $responseData;
    }
    
    /**
     * Verify payment signature from Razorpay
     * 
     * @param string $razorpayOrderId
     * @param string $razorpayPaymentId
     * @param string $razorpaySignature
     * @return bool
     */
    public function verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
        try {
            $api = new Api($this->keyId, $this->keySecret);
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ];
            $api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            return false;
        }
    }
}
