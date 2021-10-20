# Pays.cz PHP SDK for payments REST API

## Requirements

- PHP >= 7.4

## Installation

Using [Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require sovic/pays-cz-php-api
```

## Usage

Init

```php
$pays = new Pays({merchantId}, {shopId}, {secret});
```

Create payment

```php
$paysPayment = $pays->createPayment({shop-payment-id}, 'info@customer.com', 1500, 'CZK'); 
// get pays url and redirect (or add href to button ...) 
$url = $pays->buildPaymentUrl($paysPayment);
header('Location: ' . $url);
```

Validate pays request

```php 
$query = [ ... ]; // query params from HTTP request

try {
    $paysPayment = $pays->validatePaymentRequestQuery($query);
} catch(Exception $e) {
    // invalid request, output 400 Bad Request
}

if (null !== $paysPayment && $paysPayment->isSuccess()) {
    // successful payment
    $paysId = $paysPayment->getPaysPaymentId();
    ... 
} else {
    // failure|cancelled
    ...
}

// all OK, output 202 Accepted
```