<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;

final class DelegatingCalculator implements DelegatingCalculatorInterface
{
    public function __construct(private ServiceRegistryInterface $registry)
    {
    }

    public function calculate(PaymentInterface $subject): ?int
    {
        $method = $subject->getMethod();
        if ($method === null) {
            throw new UndefinedPaymentMethodException(
                'Cannot calculate charge for payment without a defined payment method.',
            );
        }

        if (!($method instanceof PaymentMethodWithFeeInterface)) {
            return 0;
        }
        if ($method->getCalculator() === null) {
            return 0;
        }

        $calculator = $this->registry->get($method->getCalculator());
        assert($calculator instanceof CalculatorInterface);

        return $calculator->calculate($subject, $method->getCalculatorConfiguration());
    }
}
