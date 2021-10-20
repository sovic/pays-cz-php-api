<?php

namespace Pays;

use InvalidArgumentException;
use RuntimeException;

class PaysPayment
{
    private const AVAILABLE_CURRENCIES = ['CZK', 'EUR', 'USD'];
    private const DEFAULT_CURRENCY = 'CZK';

    private string $clientPaymentId;
    private ?int $paysPaymentId;
    private ?string $email;
    private ?float $price;
    private string $currency = self::DEFAULT_CURRENCY;

    /**
     * @param string $clientOrderId Shop payment identified (string 1..100 chars)
     * @param int|null $paysPaymentId
     */
    public function __construct(string $clientOrderId, ?int $paysPaymentId = null)
    {
        $this->setClientPaymentId($clientOrderId);
        $this->setPaysPaymentId($paysPaymentId);
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

    public function setPaysPaymentId(?int $paysPaymentId): void
    {
        $this->paysPaymentId = $paysPaymentId;
    }

    public function getPaysPaymentId(): ?int
    {
        return $this->paysPaymentId;
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

    /**
     * @param string $currency CZK|EUR|USD, default: CZK
     */
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
