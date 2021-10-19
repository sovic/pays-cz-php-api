<?php

namespace Pays;

use InvalidArgumentException;

class Pays
{
    private const AVAILABLE_CURRENCIES = ['CZK', 'EUR', 'USD'];
    private const DEFAULT_CURRENCY = 'CZK';

    private const AVAILABLE_LOCALES = ['CS-CZ', 'SK-SK', 'EN-US', 'RU-RU', 'JA-JP'];
    private const DEFAULT_LOCALE = 'CS-CZ';

    private int $merchantId;
    private int $shopId;
    private string $locale = self::DEFAULT_LOCALE;
    private bool $isProduction;

    public function __construct(int $merchantId, int $shopId, bool $isProduction = true)
    {
        if (empty($merchantId)) {
            throw new InvalidArgumentException('Invalid merchant ID');
        }
        if (empty($shopId)) {
            throw new InvalidArgumentException('Invalid shop ID');
        }

        $this->merchantId = $merchantId;
        $this->shopId = $shopId;
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
     * @param string $clientOrderId Shop payment identified (string 1..100 chars)
     * @param string $email Customer e-mail, Pays gateway will send confirmation to this address
     * @param int $amount Order price, the smallest units for currency
     * @param string $currency
     * @param string|null $returnUrl Optional, Pays gateway will use return url specified during activation as default
     *
     * @return string
     */
    public function buildPaymentUrl(
        string  $clientOrderId,
        string  $email,
        int     $amount,
        string  $currency = self::DEFAULT_CURRENCY,
        ?string $returnUrl = null
    ): string {
        if (!in_array($currency, self::AVAILABLE_CURRENCIES)) {
            throw new InvalidArgumentException(
                'Invalid currency [use: ' . implode(',', self::AVAILABLE_CURRENCIES) . ']'
            );
        }

        $query = [
            'Merchant' => $this->merchantId,
            'Shop' => $this->shopId,
            'Currency' => $currency,
            'Amount' => $amount,
            'MerchantOrderNumber' => $clientOrderId,
            'Email' => $email,
            'Lang' => $this->locale,
        ];
        if ($returnUrl) {
            $query['ReturnURL'] = $returnUrl;
        }

        return $this->getGatewayUrl() . '?' . http_build_query($query);
    }

    private function getGatewayUrl(): string
    {
        if ($this->isProduction) {
            return 'https://www.pays.cz/paymentorder';
        } else {
            return 'https://www.pays.cz/test-paymentorder';
        }
    }
}
