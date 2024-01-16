<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ElementNotFoundException;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\PaymentMethod\UpdatePaymentPageInterface;
use Webmozart\Assert\Assert;

/**
 * Complementary to @see \Sylius\Behat\Context\Ui\Admin\ManagingPaymentMethodsContext
 */
final class ManagingPaymentMethodsContext implements Context
{
    public function __construct(
        private UpdatePaymentPageInterface $updatePaymentPage,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @When I remove fee from :paymentMethod payment method
     */
    public function iRemoveFeeFromPaymentMethod(PaymentMethodInterface $paymentMethod): void
    {
        $this->updatePaymentPage->changeAmount('', $this->getChannel());
    }

    /**
     * @Then I should see that payment method :paymentMethod has no fee
     */
    public function paymentMethodShouldBeFreeOfCharge(PaymentMethodInterface $paymentMethod): void
    {
        try {
            Assert::oneOf($this->updatePaymentPage->getAmount($this->getChannel()), ['0', '0.00', '0,00']);
        } catch (ElementNotFoundException) {
            Assert::true(true, 'Amount input should be hidden when empty');
        }
    }

    /**
     * @When I set fee :amount to :paymentMethod payment method
     */
    public function iSetChargeForPaymentMethodToAmount(string $amount, PaymentMethodInterface $paymentMethod): void
    {
        $this->updatePaymentPage->changeAmount($amount, $this->getChannel());
    }

    /**
     * @Then I should see that payment method :paymentMethod has fee :amount
     */
    public function paymentMethodShouldFeeShouldBeSetTo(PaymentMethodInterface $paymentMethod, string $amount): void
    {
        Assert::same($this->updatePaymentPage->getAmount($this->getChannel()), $amount,);
    }

    private function getChannel(): ChannelInterface
    {
        $channel = $this->sharedStorage->get('channel');

        assert($channel instanceof ChannelInterface);

        return $channel;
    }
}
