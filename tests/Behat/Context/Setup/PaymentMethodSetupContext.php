<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Entity\PaymentMethod;
use Webmozart\Assert\Assert;

final readonly class PaymentMethodSetupContext implements Context
{
    public function __construct(
        private PaymentMethodFactoryInterface $paymentMethodFactory,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private RepositoryInterface $channelRepository,
        private EntityManagerInterface $entityManager,
        private ?FactoryInterface $gatewayConfigFactory = null,
    ) {
    }

    /**
     * @Given the store has a payment method :paymentMethodName with :amount payment fee
     */
    public function theStoreHasAPaymentMethodWithPaymentFee(string $paymentMethodName, string $amount): void
    {
        $paymentMethodCode = strtolower(str_replace(' ', '_', $paymentMethodName));

        // Check if payment method already exists
        $existingPaymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $paymentMethodCode]);
        if ($existingPaymentMethod !== null) {
            return; // Payment method already exists
        }

        $channel = $this->getDefaultChannel();
        $paymentMethod = $this->createPaymentMethod($paymentMethodName, $channel);

        // Convert amount string to cents (e.g., "$5.00" to 500, "$0.00" to 0)
        $amountInCents = (int) (((float) preg_replace('/[^0-9.]/', '', $amount)) * 100);

        // Set calculator type and configuration
        Assert::isInstanceOf($paymentMethod, PaymentMethod::class);
        $paymentMethod->setCalculator('flat_rate');
        $paymentMethod->setCalculatorConfiguration([
            $channel->getCode() => [
                'amount' => $amountInCents,
            ],
        ]);

        $this->entityManager->flush();
    }

    /**
     * @Given the store has a payment method :paymentMethodName without payment fee
     */
    public function theStoreHasAPaymentMethodWithoutPaymentFee(string $paymentMethodName): void
    {
        $this->theStoreHasAPaymentMethodWithPaymentFee($paymentMethodName, '$0.00');
    }

    private function createPaymentMethod(string $name, ChannelInterface $channel): PaymentMethodInterface
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodFactory->createNew();
        $paymentMethod->setCode(strtolower(str_replace(' ', '_', $name)));
        $paymentMethod->setName($name);

        // Create gateway config for offline payment
        Assert::notNull($this->gatewayConfigFactory, 'Gateway config factory is not available');
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $this->gatewayConfigFactory->createNew();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('offline');

        // Persist gateway config first
        $this->entityManager->persist($gatewayConfig);

        $paymentMethod->setGatewayConfig($gatewayConfig);
        $paymentMethod->addChannel($channel);
        $paymentMethod->setEnabled(true);

        $this->paymentMethodRepository->add($paymentMethod);
        $this->entityManager->flush();

        return $paymentMethod;
    }

    private function getDefaultChannel(): ChannelInterface
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy([]);

        Assert::notNull($channel, 'No channel found in the database');

        return $channel;
    }
}
