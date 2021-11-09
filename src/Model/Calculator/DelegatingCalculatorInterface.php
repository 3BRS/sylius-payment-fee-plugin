<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Payment\Model\PaymentInterface;

interface DelegatingCalculatorInterface
{
    public function calculate(PaymentInterface $subject): ?int;
}
