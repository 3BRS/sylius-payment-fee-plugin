<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Shop\Checkout\CompletePage as BaseCompletePage;

class CompletePage extends BaseCompletePage implements CompletePageInterface
{
    public function getPaymentFeeTotal(): string
    {
        return $this->getElement('payment_fee_total')->getText();
    }

    public function hasPaymentFeeTotal(): bool
    {
        return $this->hasElement('payment_fee_total');
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'payment_fee_total' => '[data-test-payment-fee-total]',
        ]);
    }
}
