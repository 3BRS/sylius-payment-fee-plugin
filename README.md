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
    <a href="http://travis-ci.com/3brs/sylius-payment-fee-plugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/3brs/sylius-payment-fee-plugin/master.svg" />
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

1. Run `$ composer require mangoweb-sylius/sylius-payment-fee-plugin`.
2. Register `\MangoSylius\PaymentFeePlugin\MangoSyliusPaymentFeePlugin` in your Kernel.
3. Your Entity `PaymentMethod` has to implement `\MangoSylius\PaymentFeePlugin\Model\PaymentMethodWithFeeInterface`. You can use Trait `MangoSylius\PaymentFeePlugin\Model\PaymentMethodWithFeeTrait`. 

For guide how to use your own entity see [Sylius docs - Customizing Models](https://docs.sylius.com/en/1.3/customization/model.html)

### Admin

1. Add this to `@SyliusAdmin/PaymentMethod/_form.html.twig` template.

```twig

<div class="ui segment">
	<h4 class="ui dividing header">{{ 'mango-sylius.ui.payment_charges'|trans }}</h4>
	{{ form_row(form.calculator) }}
	{% for name, calculatorConfigurationPrototype in form.vars.prototypes %}
		<div id="{{ form.calculator.vars.id }}_{{ name }}" data-container=".calculatorConfiguration"
			 data-prototype="{{ form_widget(calculatorConfigurationPrototype)|e }}">
		</div>
	{% endfor %}
	<div class="ui segment calculatorConfiguration">
		{% if form.calculatorConfiguration is defined %}
			{% for field in form.calculatorConfiguration %}
				{{ form_row(field) }}
			{% endfor %}
		{% endif %}
	</div>
</div>
```

2. Add this to `AdminBundle/Resources/views/Order/Show/Summary/_totals.html.twig`.

```twig

{% set paymentFeeAdjustment = constant('MangoSylius\\PaymentFeePlugin\\Model\\AdjustmentInterface::PAYMENT_ADJUSTMENT') %}

{% set paymentFeeAdjustments = order.getAdjustmentsRecursively(paymentFeeAdjustment) %}
{% if paymentFeeAdjustments is not empty %}
	<tr>
		<td colspan="4" id="payment-fee">

			<div class="ui relaxed divided list">
				{% for paymentFeeLabel, paymentFeeAmount in sylius_aggregate_adjustments(paymentFeeAdjustments) %}
					<div class="item">
						<div class="content">
							<span class="header">{{ paymentFeeLabel }}</span>
							<div class="description">
								{{ money.format(paymentFeeAmount, order.currencyCode) }}
							</div>
						</div>
					</div>
				{% endfor %}
			</div>

		</td>
		<td colspan="4" id="paymentFee-total" class="right aligned">
			<strong>{{ 'mango-sylius.ui.paymentFee_total'|trans }}</strong>:
			{{ money.format(order.getAdjustmentsTotal(paymentFeeAdjustment) ,order.currencyCode) }}
		</td>
	</tr>
{% endif %}
```

## Development

### Usage

- Create symlink from .env.dist to .env or create your own .env file
- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.
* Easy Coding Standard
  ```bash
  bin/ecs.sh
  ```
* PHPStan
  ```bash
  bin/phpstan.sh
  ```
License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusPaymentFeePlugin).
