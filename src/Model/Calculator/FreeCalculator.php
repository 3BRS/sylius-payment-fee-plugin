<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;

final class FreeCalculator implements CalculatorInterface
{
    /**
     * @param array<mixed> $configuration
     *
     * @throws \Sylius\Component\Core\Exception\MissingChannelConfigurationException
     */
    public function calculate(BasePaymentInterface $subject, array $configuration): ?int
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'free';
    }
}
