<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator\FlatRateCalculator;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;

final class PaymentMethodContext implements Context
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Given /^(?:the )?payment method "(?P<paymentMethod>[^"]+)" is free of charge$/
     */
    public function thePaymentMethodIsFreeOfCharge(PaymentMethodWithFeeInterface $paymentMethod): void
    {
        $paymentMethod->setCalculator(null);
        $paymentMethod->setCalculatorConfiguration([]);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush();
    }

    /**
     * @Given /^(?:the )?payment method "(?P<paymentMethod>[^"]+)" is charged for (?P<price>"[^"]+")$/
     */
    public function thisPaymentMethodIsChargedForPrice(PaymentMethodInterface $paymentMethod, int $price): void
    {
        assert($paymentMethod instanceof PaymentMethodWithFeeInterface);

        $paymentMethod->setCalculator((new FlatRateCalculator())->getType());
        $paymentMethod->setCalculatorConfiguration([$this->getChannel()->getCode() => ['amount' => $price]]);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush();
    }

    private function getChannel(): ChannelInterface
    {
        $channel = $this->sharedStorage->get('channel');

        assert($channel instanceof ChannelInterface);

        return $channel;
    }
}
