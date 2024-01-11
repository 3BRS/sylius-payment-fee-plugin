<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\ShippingMethod\UpdateShippingPage;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Admin\ShippingMethod\UpdateShippingPageInterface;
use ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Webmozart\Assert\Assert;

final class ManagingShippingMethodContext implements Context
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChannelRepositoryInterface $channelRepository,
        private UpdateShippingPageInterface $updatePage,
    ) {}

    /**
     * @When /I remove charge from ^(this payment method)$/
     */
    public function iRemoveChargeFromPaymentMethod(PaymentMethodInterface $paymentMethod): void
    {
        $this->updatePage->changeInput(UpdateShippingPage::AMOUNT_INPUT, '');
    }

    /**
     * @Then /^(this payment method) should be free of charge$/
     */
    public function paymentMethodShouldBeFreeOfCharge(PaymentMethodInterface $paymentMethod): void
    {
        Assert::same($this->updatePage->getInputValue(), '');
    }

    /**
     * @When /^I set charge for (this payment method) to :amount $/
     */
    public function iSetChargeForPaymentMethodToAmount(PaymentMethodInterface $paymentMethod, float $amount): void
    {
        $this->updatePage->changeInput(UpdateShippingPage::AMOUNT_INPUT, (string) $amount);
    }

    /**
     * @Then /^(this payment method) should be free of charge$/
     */
    public function paymentMethodShouldFeeShouldBeSetTo(PaymentMethodInterface $paymentMethod, float $amount): void
    {
        Assert::same($this->updatePage->getInputValue(), (string) $amount);
    }

    /**
     * @Then the Zásilkovna Carrier pickup point for this shipping method should be :arg1
     */
    public function theZasilkovnaCarrierIdForThisShippingMethodShouldBe($arg1)
    {
        Assert::eq($this->updatePage->isSingleResourceOnPage('carrierId'), $arg1);
    }

    /**
     * @When I change Zásilkovna api key to :apiKey
     */
    public function iChangeZasilkovnaApiKeyTo(string $apiKey)
    {
        $this->updatePage->changeApiKey($apiKey);
    }

    /**
     * @Then the Zásilkovna api key for this shipping method should be :apiKey
     */
    public function theZasilkovnaApiKeyForThisShippingMethodShouldBe(string $apiKey)
    {
        Assert::eq($this->updatePage->isSingleResourceOnPage('apiKey'), $apiKey);
    }

    /**
     * @Then it should be shipped to Zásilkovna branch
     */
    public function itShouldBeShippedToZasilkovnaBranch()
    {
        Assert::true($this->updatePage->iSeeZasilkovnaBranchInsteadOfShippingAddress());
    }
}
