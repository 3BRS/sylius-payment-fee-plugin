<p align="center">
    <a href="https://www.3brs.com" target="_blank">
        <img src="https://3brs1.fra1.cdn.digitaloceanspaces.com/3brs/logo/3BRS-logo-sylius-200.png"/>
    </a>
</p>
<h1 align="center">
Payment Fee Plugin
<br />
    <a href="https://packagist.org/packages/3brs/sylius-payment-fee-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/3brs/sylius-payment-fee-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/3brs/sylius-payment-fee-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/3brs/sylius-payment-fee-plugin.svg" />
    </a>
    <a href="https://circleci.com/gh/3BRS/sylius-payment-fee-plugin" title="Build status" target="_blank">
        <img src="https://circleci.com/gh/3BRS/sylius-payment-fee-plugin.svg?style=shield" />
    </a>
</h1>

## Features

* Charge extra fee for using payment method.
* Typical usage: Cash on Delivery.
* Taxes are implemented the same way as taxes for shipping fees.

<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-payment-fee-plugin/master/doc/admin.png"/>
</p>

## Installation

1. Run `$ composer require 3brs/sylius-payment-fee-plugin`.
2. Register `\ThreeBRS\SyliusPaymentFeePlugin\ThreeBRSSyliusPaymentFeePlugin` in your Kernel.
3. Your Entity `PaymentMethod` has to implement `\ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface`. You can use Trait `ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait`. 

For guide how to use your own entity see [Sylius docs - Customizing Models](https://docs.sylius.com/en/latest/customization/model.html)

### Admin

1. Include `@ThreeBRSSyliusPaymentFeePlugin/Admin/_form.html.twig` into `@SyliusAdmin/PaymentMethod/_form.html.twig`.

```twig
...
{{ include('@ThreeBRSSyliusPaymentFeePlugin/Admin/_form.html.twig') }}
```

2. Include `@ThreeBRSSyliusPaymentFeePlugin/Admin/_order_show.html.twig` into `@AdminBundle/Resources/views/Order/Show/Summary/_totals.html.twig`.

```twig
...
{{ include('@ThreeBRSSyliusPaymentFeePlugin/Admin/_order_show.html.twig') }}
```

## Development

### Usage

- Create symlink from .env.dist to .env or create your own .env file
- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/console doctrine:schema:create -e test
$ bin/behat
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusPaymentFeePlugin).
