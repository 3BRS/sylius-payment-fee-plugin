<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator\DelegatingCalculatorInterface;

final class PaymentChargesProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly FactoryInterface $adjustmentFactory,
        private readonly DelegatingCalculatorInterface $paymentChargesCalculator,
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        assert($order instanceof OrderInterface);

        if (!$order->canBeProcessed()) {
            return;
        }

        $order->removeAdjustments(AdjustmentInterface::PAYMENT_ADJUSTMENT);

        foreach ($order->getPayments() as $payment) {
            $paymentCharge = $this->paymentChargesCalculator->calculate($payment);

            if ($paymentCharge === null) {
                continue;
            }

            $adjustment = $this->adjustmentFactory->createNew();
            assert($adjustment instanceof BaseAdjustmentInterface);

            $adjustment->setType(AdjustmentInterface::PAYMENT_ADJUSTMENT);
            $adjustment->setAmount($paymentCharge);
            $adjustment->setLabel(
                $payment->getMethod() !== null
                    ? $payment->getMethod()->getName()
                    : null,
            );
            $adjustment->setOriginCode(
                $payment->getMethod() !== null
                    ? $payment->getMethod()->getCode()
                    : null,
            );
            $adjustment->setNeutral(false);

            $order->addAdjustment($adjustment);
        }
    }
}
