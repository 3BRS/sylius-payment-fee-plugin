<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Payment\Model\PaymentInterface;

interface CalculatorInterface
{
    public function calculate(PaymentInterface $subject, array $configuration): ?int;

    public function getType(): string;
}
