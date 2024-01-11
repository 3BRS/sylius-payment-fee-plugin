<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model;

use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Taxation\Model\TaxableInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

interface PaymentMethodWithFeeInterface extends PaymentMethodInterface, TaxableInterface
{
    public function getCalculator(): ?string;

    public function setCalculator(?string $calculator): void;

    public function getCalculatorConfiguration(): array;

    public function setCalculatorConfiguration(array $calculatorConfiguration): void;

    public function getTaxCategory(): ?TaxCategoryInterface;

    public function setTaxCategory(?TaxCategoryInterface $category): void;
}
