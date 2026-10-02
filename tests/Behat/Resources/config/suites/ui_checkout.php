<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup\PaymentMethodSetupContext;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui\PaymentFeeContext;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('ui_checkout_with_payment_fee'))
                    ->withContexts(
                        'sylius.behat.context.hook.doctrine_orm',
                        'sylius.behat.context.hook.guest_cart',
                        'sylius.behat.context.transform.address',
                        'sylius.behat.context.transform.product',
                        'sylius.behat.context.transform.shared_storage',
                        'sylius.behat.context.transform.lexical',
                        'sylius.behat.context.setup.channel',
                        'sylius.behat.context.setup.currency',
                        'sylius.behat.context.setup.locale',
                        'sylius.behat.context.setup.zone',
                        'sylius.behat.context.setup.shipping',
                        'sylius.behat.context.setup.payment',
                        'sylius.behat.context.setup.product',
                        'sylius.behat.context.setup.customer',
                        'sylius.behat.context.setup.cart',
                        'sylius.behat.context.ui.shop.checkout',
                        'sylius.behat.context.ui.shop.checkout.addressing',
                        'sylius.behat.context.ui.shop.checkout.shipping',
                        'sylius.behat.context.ui.shop.checkout.payment',
                        'sylius.behat.context.ui.shop.cart',
                        'sylius.behat.context.ui.shop.product',
                        PaymentMethodSetupContext::class,
                        PaymentFeeContext::class,
                    )
                    ->withFilter(new TagFilter('@checkout_payment_fee&&@ui')),
            ),
    );
