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
$pays = new Pays('{merchant-id}', '{shop-id}', '{secret}');
```

Create payment

```php
$paysPayment = $pays->createPayment('{shop-payment-id}', '{customer-email}', '{price}', '{currency}'); 

// get Pays.cz gateway url (E.g. for payment button)
$url = $pays->buildPaymentUrl($paysPayment);

// redirect to Pays.cz gateway directly
$pays->redirectToPaymentUrl($paysPayment);
```

Validate Pays.cz status request

```php 
$query = [ … ]; // query params array from HTTP request

try {
    $paysPayment = $pays->validatePaymentRequestQuery($query);
    if ($paysPayment->isSuccess()) {
        // handle successful payment
        $clientPaymentId = $paysPayment->getClientPaymentId();
    } else {
        // handle failed|cancelled payment
    }
} catch(Exception $e) {
    // invalid request, some parameter missing or invalid signature hash, output 400 Bad Request
}

// all OK, output 202 Accepted
```