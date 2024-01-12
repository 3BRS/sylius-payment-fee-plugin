<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\PaymentMethod;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\PaymentMethod\UpdatePage as BasePaymentMethodUpdatePage;
use Sylius\Component\Core\Model\ChannelInterface;

final class UpdatePaymentPage extends BasePaymentMethodUpdatePage implements UpdatePaymentPageInterface
{
    public const AMOUNT_INPUT = 'amount';

    public function changeAmount(?string $value, ChannelInterface $channel): void
    {
        $this->getAmountElement($channel->getCode())->setValue($value);
    }

    public function getAmount(ChannelInterface $channel): array|bool|string
    {
        return $this->getAmountElement($channel->getCode())->getValue();
    }

    private function getAmountElement(string $channelCode): NodeElement
    {
        return $this->getElement(self::AMOUNT_INPUT, ['%channel%' => $channelCode]);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            self::AMOUNT_INPUT => 'sylius_payment_method_calculatorConfiguration_%channel%_amount',
        ]);
    }
}
