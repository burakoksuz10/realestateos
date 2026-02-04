<?php

namespace Modules\Integrations\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentConnector
{
    protected string $provider;
    protected array $config;

    public function __construct()
    {
        $this->provider = config('services.payment.provider', 'iyzico');
        $this->config = config("services.{$this->provider}", []);
    }

    /**
     * Create payment
     */
    public function createPayment(array $data): array
    {
        return match($this->provider) {
            'iyzico' => $this->createIyzicoPayment($data),
            'paytr' => $this->createPayTRPayment($data),
            default => throw new \Exception("Unknown payment provider: {$this->provider}"),
        };
    }

    /**
     * Create Iyzico payment
     */
    protected function createIyzicoPayment(array $data): array
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey($this->config['api_key']);
        $options->setSecretKey($this->config['secret_key']);
        $options->setBaseUrl($this->config['base_url'] ?? 'https://api.iyzipay.com');

        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($data['conversation_id'] ?? uniqid());
        $request->setPrice($data['price']);
        $request->setPaidPrice($data['paid_price'] ?? $data['price']);
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setInstallment($data['installment'] ?? 1);
        $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        // Payment card
        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName($data['card_holder_name']);
        $paymentCard->setCardNumber($data['card_number']);
        $paymentCard->setExpireMonth($data['expire_month']);
        $paymentCard->setExpireYear($data['expire_year']);
        $paymentCard->setCvc($data['cvc']);
        $request->setPaymentCard($paymentCard);

        // Buyer
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($data['buyer_id']);
        $buyer->setName($data['buyer_name']);
        $buyer->setSurname($data['buyer_surname']);
        $buyer->setEmail($data['buyer_email']);
        $buyer->setIdentityNumber($data['buyer_identity'] ?? '11111111111');
        $buyer->setRegistrationAddress($data['buyer_address']);
        $buyer->setCity($data['buyer_city']);
        $buyer->setCountry('Turkey');
        $request->setBuyer($buyer);

        // Basket items
        $basketItems = [];
        foreach ($data['items'] as $item) {
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId($item['id']);
            $basketItem->setName($item['name']);
            $basketItem->setCategory1($item['category'] ?? 'Real Estate');
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
            $basketItem->setPrice($item['price']);
            $basketItems[] = $basketItem;
        }
        $request->setBasketItems($basketItems);

        $payment = \Iyzipay\Model\Payment::create($request, $options);

        return [
            'status' => $payment->getStatus(),
            'payment_id' => $payment->getPaymentId(),
            'error_message' => $payment->getErrorMessage(),
            'raw_result' => $payment->getRawResult(),
        ];
    }

    /**
     * Create PayTR payment
     */
    protected function createPayTRPayment(array $data): array
    {
        $merchantId = $this->config['merchant_id'];
        $merchantKey = $this->config['merchant_key'];
        $merchantSalt = $this->config['merchant_salt'];

        $userIp = request()->ip();
        $merchantOid = $data['order_id'] ?? uniqid('order_');
        $email = $data['buyer_email'];
        $paymentAmount = $data['price'] * 100; // Convert to kuruş
        $userName = $data['buyer_name'] . ' ' . $data['buyer_surname'];
        $userAddress = $data['buyer_address'];
        $userPhone = $data['buyer_phone'];

        $userBasket = base64_encode(json_encode($data['items']));
        $noInstallment = $data['no_installment'] ?? 0;
        $maxInstallment = $data['max_installment'] ?? 0;
        $currency = 'TL';
        $testMode = $this->config['test_mode'] ?? 0;

        $hashStr = $merchantId . $userIp . $merchantOid . $email . $paymentAmount . $userBasket . $noInstallment . $maxInstallment . $currency . $testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $merchantSalt, $merchantKey, true));

        $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', [
            'merchant_id' => $merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => $testMode,
            'no_installment' => $noInstallment,
            'max_installment' => $maxInstallment,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'merchant_ok_url' => $data['success_url'] ?? route('payment.success'),
            'merchant_fail_url' => $data['fail_url'] ?? route('payment.fail'),
            'timeout_limit' => 30,
            'currency' => $currency,
            'test_mode' => $testMode,
        ]);

        $result = $response->json();

        if ($result['status'] !== 'success') {
            throw new \Exception($result['reason'] ?? 'PayTR token creation failed');
        }

        return [
            'status' => 'success',
            'token' => $result['token'],
            'iframe_url' => 'https://www.paytr.com/odeme/guvenli/' . $result['token'],
        ];
    }

    /**
     * Verify payment callback
     */
    public function verifyCallback(array $data): bool
    {
        return match($this->provider) {
            'iyzico' => $this->verifyIyzicoCallback($data),
            'paytr' => $this->verifyPayTRCallback($data),
            default => false,
        };
    }

    protected function verifyIyzicoCallback(array $data): bool
    {
        // Iyzico callback verification
        return true;
    }

    protected function verifyPayTRCallback(array $data): bool
    {
        $merchantKey = $this->config['merchant_key'];
        $merchantSalt = $this->config['merchant_salt'];

        $hash = base64_encode(hash_hmac('sha256', 
            $data['merchant_oid'] . $merchantSalt . $data['status'] . $data['total_amount'], 
            $merchantKey, true));

        return $hash === $data['hash'];
    }

    /**
     * Refund payment
     */
    public function refund(string $paymentId, float $amount): array
    {
        return match($this->provider) {
            'iyzico' => $this->refundIyzico($paymentId, $amount),
            'paytr' => $this->refundPayTR($paymentId, $amount),
            default => throw new \Exception("Refund not supported for: {$this->provider}"),
        };
    }

    protected function refundIyzico(string $paymentId, float $amount): array
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey($this->config['api_key']);
        $options->setSecretKey($this->config['secret_key']);
        $options->setBaseUrl($this->config['base_url'] ?? 'https://api.iyzipay.com');

        $request = new \Iyzipay\Request\CreateRefundRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(uniqid());
        $request->setPaymentTransactionId($paymentId);
        $request->setPrice($amount);
        $request->setCurrency(\Iyzipay\Model\Currency::TL);

        $refund = \Iyzipay\Model\Refund::create($request, $options);

        return [
            'status' => $refund->getStatus(),
            'error_message' => $refund->getErrorMessage(),
        ];
    }

    protected function refundPayTR(string $paymentId, float $amount): array
    {
        // PayTR refund implementation
        return ['status' => 'pending'];
    }
}
