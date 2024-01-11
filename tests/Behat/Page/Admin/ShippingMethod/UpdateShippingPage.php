<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\ShippingMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\UpdatePage as BasePaymentMethodUpdatePage;

final class UpdateShippingPage extends BasePaymentMethodUpdatePage implements UpdateShippingPageInterface
{
    public const AMOUNT_INPUT = 'amount';

    public function changeInput(string $elementName, ?string $value): void
    {
        $this->getElement($elementName)->setValue($value);
    }

    public function iSeeZasilkovnaBranchInsteadOfShippingAddress(): bool
    {
        $shippingAddress = $this->getElement('shippingAddress')->getText();

        return false !== strpos($shippingAddress, 'Zásilkovna branch');
    }

    public function getInputValue(): array|bool|string
    {
        return $this->getElement(self::AMOUNT_INPUT)->getValue();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            self::AMOUNT_INPUT => $this->isAvailableInChannel(),
            'senderLabel' => '#sylius_shipping_method_zasilkovnaConfig_senderLabel',
            'carrierId' => '#sylius_shipping_method_zasilkovnaConfig_carrierId',
            'shippingAddress' => '#shipping-address',
        ]);
    }
}
