<?php

namespace Pays;

use InvalidArgumentException;
use RuntimeException;

class Pays
{
    private const AVAILABLE_LOCALES = ['CS-CZ', 'SK-SK', 'EN-US', 'RU-RU', 'JA-JP'];
    private const DEFAULT_LOCALE = 'CS-CZ';

    private int $merchantId;
    private int $shopId;
    private string $secret;
    private bool $isProduction;
    private string $locale = self::DEFAULT_LOCALE;

    public function __construct(int $merchantId, int $shopId, string $secret, bool $isProduction = true)
    {
        $this->merchantId = $merchantId;
        $this->shopId = $shopId;
        $this->secret = $secret;
        $this->isProduction = $isProduction;
    }

    public function setLocale(string $locale)
    {
        if (!in_array($locale, self::AVAILABLE_LOCALES)) {
            throw new InvalidArgumentException(
                'Invalid currency [use: ' . implode(',', self::AVAILABLE_LOCALES) . ']'
            );
        }
        $this->locale = $locale;
    }

    /**
     * @param string $shopPaymentId
     * @param float $price
     * @param string $currency CZK|EUR|USD, default: CZK
     * @return PaysPayment
     */
    public function createPayment(
        string $shopPaymentId,
        float  $price,
        string $currency = PaysPayment::DEFAULT_CURRENCY
    ): PaysPayment {
        $paysPayment = new PaysPayment($shopPaymentId);
        $paysPayment->setPrice($price);
        $paysPayment->setCurrency($currency);

        return $paysPayment;
    }

    /**
     * @param PaysPayment $payment
     * @param string|null $returnUrl Optional, Pays gateway will use return url specified during activation as default
     * @return string
     */
    public function buildPaymentUrl(PaysPayment $payment, ?string $returnUrl = null): string
    {
        $query = [
            'Merchant' => $this->merchantId,
            'Shop' => $this->shopId,
            'Currency' => $payment->getCurrency(),
            'Amount' => $payment->getAmount(),
            'MerchantOrderNumber' => $payment->getClientPaymentId(),
            'Lang' => $this->locale,
        ];
        if ($payment->getEmail()) {
            $query['Email'] = $payment->getEmail();
        }
        if ($returnUrl) {
            $query['ReturnURL'] = $returnUrl;
        }

        return $this->getGatewayUrl() . '?' . http_build_query($query);
    }

    public function redirectToPaymentUrl(PaysPayment $payment, ?string $returnUrl = null)
    {
        $url = $this->buildPaymentUrl($payment, $returnUrl);

        header('Location: ' . $url);
        exit;
    }

    private function getGatewayUrl(): string
    {
        // apparently there is no test gateway, it is in dev state at first after registration
        // after successful tests from Pays it is switched to production mode
        if ($this->isProduction) {
            return 'https://www.pays.cz/paymentorder';
        } else {
            throw new RuntimeException('Not yet implemented, apparently there is no test gateway on Pays.cz');
        }
    }

    public function validatePaymentRequestQuery(array $query): PaysPayment
    {
        if (empty($query['MerchantOrderNumber'])) {
            throw new InvalidArgumentException('Missing MerchantOrderNumber');
        }
        $clientOrderId = $query['MerchantOrderNumber'];

        if (empty($query['PaymentOrderID'])) {
            throw new InvalidArgumentException('Missing PaymentOrderID');
        }
        $paysPaymentId = (int) $query['PaymentOrderID'];

        if (empty($query['Amount'])) {
            throw new InvalidArgumentException('Missing Amount');
        }
        $amount = (int) $query['Amount'];

        if (empty($query['CurrencyID'])) {
            throw new InvalidArgumentException('Missing CurrencyID');
        }
        $currency = $query['CurrencyID'];

        if (empty($query['CurrencyBaseUnits'])) {
            throw new InvalidArgumentException('Missing CurrencyBaseUnits');
        }
        $currencyBaseUnits = (int) $query['CurrencyBaseUnits'];

        if (empty($query['PaymentOrderStatusID'])) {
            throw new InvalidArgumentException('Missing PaymentOrderStatusID');
        }
        $status = $query['PaymentOrderStatusID'];
        $statusDescription = $query['PaymentOrderStatusDescription'] ?? null;

        if (empty($query['hash'])) {
            throw new InvalidArgumentException('Missing hash');
        }
        $hash = $query['hash'];

        $paysPayment = new PaysPayment($clientOrderId, $paysPaymentId);
        $paysPayment->setPrice($amount / $currencyBaseUnits);
        $paysPayment->setCurrency($currency);
        $paysPayment->setStatus($status);
        $paysPayment->setStatusDescription($statusDescription);

        $paymentHashData =
            $paysPayment->getPaysPaymentId() .
            $paysPayment->getClientPaymentId() .
            $paysPayment->getStatus() .
            $paysPayment->getCurrency() .
            $paysPayment->getAmount() .
            $paysPayment->getCurrencyBaseUnits();
        $paymentHash = hash_hmac('md5', $paymentHashData, $this->secret);
        if ($hash !== $paymentHash) {
            throw new InvalidArgumentException('Invalid hash');
        }

        return $paysPayment;
    }
}
