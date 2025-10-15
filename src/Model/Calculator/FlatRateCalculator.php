<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusPaymentFeePlugin\Model\Calculator;

use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Webmozart\Assert\Assert;

final class FlatRateCalculator implements CalculatorInterface
{
    /**
     * @throws MissingChannelConfigurationException|\ErrorException
     */
    // @phpstan-ignore-next-line return.unusedType
    public function calculate(
        BasePaymentInterface $subject,
        array $configuration,
    ): ?int {
        assert($subject instanceof PaymentInterface);

        $order = $subject->getOrder();
        assert($order instanceof OrderInterface);

        if ($order->getChannel() === null) {
            throw new \ErrorException('$order->getChannel() cannot by NULL');
        }

        $channelCode = $order->getChannel()->getCode();

        if (!isset($configuration[$channelCode])) {
            throw new MissingChannelConfigurationException(
                sprintf(
                    'Channel %s has no amount defined for shipping method %s',
                    $order->getChannel()->getName(),
                    $subject->getMethod() !== null
                        ? $subject->getMethod()->getName()
                        : 'null',
                ),
            );
        }

        Assert::true(
            is_array($configuration[$channelCode]) || $configuration[$channelCode] instanceof \ArrayAccess,
            'Configuration for channel must be an array',
        );
        Assert::numeric($configuration[$channelCode]['amount'] ?? null, '"amount" value must be a number');

        return (int) $configuration[$channelCode]['amount'];
    }

    public function getType(): string
    {
        return 'flat_rate';
    }
}
