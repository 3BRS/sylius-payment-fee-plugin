<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface as BaseCompletePageInterface;

interface CompletePageInterface extends BaseCompletePageInterface
{
    public function getPaymentFeeTotal(): string;

    public function hasPaymentFeeTotal(): bool;
}
