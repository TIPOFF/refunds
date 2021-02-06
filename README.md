# Laravel Package for issuing refunds for Ecommerce payments

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tipoff/refunds.svg?style=flat-square)](https://packagist.org/packages/tipoff/refunds)
![Tests](https://github.com/tipoff/refunds/workflows/Tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/tipoff/refunds.svg?style=flat-square)](https://packagist.org/packages/tipoff/refunds)


This is where your description should go.

## Installation

You can install the package via composer:

```bash
composer require tipoff/refunds
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Tipoff\Refunds\RefundsServiceProvider" --tag="refunds-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Tipoff\Refunds\RefundsServiceProvider" --tag="refunds-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$refunds = new Tipoff\Refunds();
echo $refunds->echoPhrase('Hello, Tipoff!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tipoff](https://github.com/tipoff)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
