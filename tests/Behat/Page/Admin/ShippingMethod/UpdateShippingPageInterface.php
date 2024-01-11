<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\ShippingMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\UpdatePageInterface as BaseUpdatePageInterface;

interface UpdateShippingPageInterface extends BaseUpdatePageInterface
{
    public function changeInput(string $elementName, ?string $value): void;

    public function getInputValue(): array|bool|string;
}
