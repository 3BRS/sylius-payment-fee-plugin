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

* Charge extra fee for a payment method
* Typical usage: _Cash on Delivery_
* Taxes are implemented the same way as taxes for shipping fees

<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-payment-fee-plugin/master/doc/admin.png"/>
</p>

## Installation

1. Run `composer require 3brs/sylius-payment-fee-plugin`
1. Add plugin class to your `config/bundles.php`

   ```php
   return [
      ...
      ThreeBRS\SyliusPaymentFeePlugin\ThreeBRSSyliusPaymentFeePlugin::class => ['all' => true],
   ];
   ```
1. Your Entity `PaymentMethod` has to implement `\ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface`. You can use Trait `ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeTrait`.

   - see [test PaymentMethod](tests/Application/src/Entity/PaymentMethod.php) for inspiration

For guide how to use your own entity see [Sylius docs - Customizing Models](https://docs.sylius.com/en/latest/customization/model.html)

### Admin

1. Include `@ThreeBRSSyliusPaymentFeePlugin/Admin/_form.html.twig` into `@SyliusAdmin/PaymentMethod/_form.html.twig`.

```twig
{# ... #}

{% include '@ThreeBRSSyliusPaymentFeePlugin/Admin/_form.html.twig' %}
```

1. Include `@ThreeBRSSyliusPaymentFeePlugin/Admin/_order_show.html.twig` into `@AdminBundle/Order/Show/Summary/_totals.html.twig`.

```twig
{# ... #}

{% include '@ThreeBRSSyliusPaymentFeePlugin/Admin/_order_show.html.twig' %}
```

### Shop

1. Include `@ThreeBRSSyliusPaymentFeePlugin/Shop/Checkout/SelectPayment/_choice.html.twig` into `@ShopBundle/Checkout/SelectPayment/_choice.html.twig`.

```twig
{# ... #}

{% include '@ThreeBRSSyliusPaymentFeePlugin/Shop/Checkout/SelectPayment/_choice.html.twig' %}
```

1. Add variable fee `{% set fee = form.method.vars.payment_costs[choice_form.vars.value] %}` into `@ShopBundle/Checkout/SelectPayment/_payment.html.twig` into `foreach`.

```twig
{# ... #}

{% for key, choice_form in form.method %}
    {% set fee = form.method.vars.payment_costs[choice_form.vars.value] %}
    {% include '@SyliusShop/Checkout/SelectPayment/_choice.html.twig' with {'form': choice_form, 'method': form.method.vars.choices[key].data} %}
{% else %}
    {% include '@SyliusShop/Checkout/SelectPayment/_unavailable.html.twig' %}
{% endfor %}
```

1. Include `@ThreeBRSSyliusPaymentFeePlugin/Shop/Common/Order/Table/_payment.html.twig` into `@ShopBundle/Common/Order/Table/_totals.html.twig`.

```twig
{# ... #}

<tr>
    {% include '@SyliusShop/Common/Order/Table/_shipping.html.twig' with {'order': order} %}
</tr>
{% include '@ThreeBRSSyliusPaymentFeePlugin/Shop/Common/Order/Table/_payment.html.twig' with {'order': order} %}
```

1. Create and run doctrine database migrations
    ```bash
    bin/console doctrine:migrations:diff
    bin/console doctrine:migrations:migrate
    ```

- how to use your own entity
  see [Sylius docs - Customizing Models](https://docs.sylius.com/en/1.12/customization/model.html)

## Development

### Usage

- Alter plugin in `/src`
- See `bin/` dir for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
composer install
bin/console doctrine:database:create --if-not-exists --env=test
bin/console doctrine:schema:update --complete --force --env=test
yarn --cwd tests/Application install
yarn --cwd tests/Application build

bin/behat
bin/phpstan.sh
bin/ecs.sh
vendor/bin/phpspec run
```

### Opening Sylius with your plugin

1. Install symfony CLI command: https://symfony.com/download
    - hint: for Docker (with Ubuntu) use _Debian/Ubuntu — APT based
      Linux_ installation steps as `root` user and without `sudo` command
        - you may need to install `curl` first ```apt-get update && apt-get install curl --yes```
2. Run app

```bash
(cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
(cd tests/Application && APP_ENV=test symfony server:start --dir=public --port=8080)
```

- change `APP_ENV` to `dev` if you need it

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusPaymentFeePlugin).
