<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Taxation\Applicator;

use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Taxation\Applicator\OrderTaxesApplicatorInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\AdjustmentInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;

class OrderPaymentTaxesApplicator implements OrderTaxesApplicatorInterface
{
    public function __construct(
        private CalculatorInterface $calculator,
        private AdjustmentFactoryInterface $adjustmentFactory,
        private TaxRateResolverInterface $taxRateResolver,
    ) {
    }

    private function getPaymentFee(OrderInterface $order): int
    {
        $paymentFees = $order->getAdjustmentsRecursively(AdjustmentInterface::PAYMENT_ADJUSTMENT);
        if (!$paymentFees->count()) {
            return 0;
        }

        $paymentFee = $paymentFees->first();
        assert($paymentFee instanceof \Sylius\Component\Core\Model\AdjustmentInterface);

        return $paymentFee->getAmount();
    }

    public function apply(OrderInterface $order, ZoneInterface $zone): void
    {
        $paymentTotal = $this->getPaymentFee($order);

        if (0 === $paymentTotal) {
            return;
        }

        $paymentMethod = $this->getPaymentMethod($order);
        if ($paymentMethod === null) {
            return;
        }

        $taxRate = $this->taxRateResolver->resolve($paymentMethod, ['zone' => $zone]);
        if (null === $taxRate) {
            return;
        }

        $taxAmount = $this->calculator->calculate($paymentTotal, $taxRate);
        if (0.00 === $taxAmount) {
            return;
        }

        $label = $taxRate->getLabel() ?? 'payment tax';
        $this->addAdjustment($order, (int) $taxAmount, $label, $taxRate->isIncludedInPrice());
    }

    private function addAdjustment(OrderInterface $order, int $taxAmount, string $label, bool $included): void
    {
        $paymentTaxAdjustment = $this->adjustmentFactory
            ->createWithData(AdjustmentInterface::TAX_ADJUSTMENT, $label, $taxAmount, $included);
        $order->addAdjustment($paymentTaxAdjustment);
    }

    private function getPaymentMethod(OrderInterface $order): ?PaymentMethodWithFeeInterface
    {
        $payment = $order->getPayments()->first();
        if (false === $payment) {
            return null;
        }

        assert($payment instanceof PaymentInterface);

        $method = $payment->getMethod();
        \assert($method instanceof PaymentMethodWithFeeInterface);

        return $method;
    }
}
