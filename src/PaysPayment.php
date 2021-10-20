<?php

namespace Pays;

use InvalidArgumentException;
use RuntimeException;

class PaysPayment
{
    private const AVAILABLE_CURRENCIES = ['CZK', 'EUR', 'USD'];
    private const DEFAULT_CURRENCY = 'CZK';

    private string $clientPaymentId;
    private ?string $email;
    private ?float $price;
    private string $currency = self::DEFAULT_CURRENCY;

    /**
     * @param string $clientOrderId Shop payment identified (string 1..100 chars)
     * @param string|null $email Customer e-mail, Pays gateway will send confirmation to this address
     * @param float|null $price Order price
     * @param string $currency CZK|EUR|USD, default: CZK
     */
    public function __construct(
        string  $clientOrderId,
        ?string $email = null,
        ?float  $price = null,
        string  $currency = self::DEFAULT_CURRENCY
    ) {
        $this->setClientPaymentId($clientOrderId);
        $this->setEmail($email);
        $this->setPrice($price);
        $this->setCurrency($currency);
    }

    /**
     * @param string $clientPaymentId Shop payment identified (string 1..100 chars)
     */
    public function setClientPaymentId(string $clientPaymentId): void
    {
        $this->clientPaymentId = $clientPaymentId;
    }

    public function getClientPaymentId(): string
    {
        return $this->clientPaymentId;
    }

    /**
     * @param string|null $email Customer e-mail, Pays gateway will send confirmation to this address
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setCurrency(string $currency): void
    {
        if (!in_array($currency, self::AVAILABLE_CURRENCIES)) {
            throw new InvalidArgumentException(
                'Invalid currency [use: ' . implode(',', self::AVAILABLE_CURRENCIES) . ']'
            );
        }
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAmount(): int
    {
        if (null === $this->price) {
            throw new RuntimeException('Invalid price, use PaysPayment::setPrice');
        }

        return $this->convertPriceToAmount($this->price);
    }

    private function convertPriceToAmount(float $price): int
    {
        // TODO better convert price to amount for different currencies

        return round($price * 100);
    }

    private function convertAmountToPrice(int $amount): float
    {
        // TODO better convert price to amount for different currencies

        return $amount / 100;
    }
}
