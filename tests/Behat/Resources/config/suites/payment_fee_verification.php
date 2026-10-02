<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup\ChannelContext;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup\PaymentMethodSetupContext;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup\PaymentMethodVerificationContext;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('payment_fee_verification'))
                    ->withContexts(
                        ChannelContext::class,
                        PaymentMethodSetupContext::class,
                        PaymentMethodVerificationContext::class,
                    )
                    ->withFilter(new TagFilter('@checkout_payment_fee&&@verification')),
            ),
    );
