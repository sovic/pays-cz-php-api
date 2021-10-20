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