<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Entity\PaymentMethod;
use Webmozart\Assert\Assert;

final readonly class PaymentMethodVerificationContext implements Context
{
    public function __construct(
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    /**
     * @Then the payment method :paymentMethodName should have fee calculator configured
     */
    public function thePaymentMethodShouldHaveFeeCalculatorConfigured(string $paymentMethodName): void
    {
        $paymentMethod = $this->findPaymentMethod($paymentMethodName);

        Assert::notNull($paymentMethod->getCalculator(), sprintf('Payment method "%s" has no calculator configured', $paymentMethodName));
        Assert::eq($paymentMethod->getCalculator(), 'flat_rate', sprintf('Expected calculator "flat_rate" but got "%s"', $paymentMethod->getCalculator()));
    }

    /**
     * @Then the payment method :paymentMethodName fee amount should be :expectedAmount cents
     */
    public function thePaymentMethodFeeAmountShouldBeCents(string $paymentMethodName, string $expectedAmount): void
    {
        $paymentMethod = $this->findPaymentMethod($paymentMethodName);

        $configuration = $paymentMethod->getCalculatorConfiguration();
        Assert::notEmpty($configuration, sprintf('Payment method "%s" has no calculator configuration', $paymentMethodName));

        // Get the first channel's configuration
        $channelConfig = reset($configuration);
        Assert::isArray($channelConfig, 'Calculator configuration must be an array');
        Assert::keyExists($channelConfig, 'amount', 'Calculator configuration must have "amount" key');

        Assert::integer($channelConfig['amount'], 'Amount must be an integer');
        $actualAmount = $channelConfig['amount'];
        $expectedAmountInt = (int) $expectedAmount;

        Assert::eq($actualAmount, $expectedAmountInt, sprintf(
            'Expected payment fee amount %d cents but got %d cents',
            $expectedAmountInt,
            $actualAmount
        ));
    }

    private function findPaymentMethod(string $name): PaymentMethod
    {
        $code = strtolower(str_replace(' ', '_', $name));
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $code]);

        Assert::notNull($paymentMethod, sprintf('Payment method "%s" (code: %s) not found', $name, $code));
        Assert::isInstanceOf($paymentMethod, PaymentMethod::class, sprintf('Payment method "%s" must be instance of PaymentMethod', $name));

        return $paymentMethod;
    }
}
