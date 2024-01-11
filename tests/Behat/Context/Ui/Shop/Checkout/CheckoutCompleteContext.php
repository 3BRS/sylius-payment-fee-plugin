<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui\Shop\Checkout;

use Behat\Behat\Context\Context;
use Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Page\Shop\Checkout\CompletePageInterface;
use Webmozart\Assert\Assert;

/**
 * Complementary to @see \Sylius\Behat\Context\Ui\Shop\Checkout\CheckoutCompleteContext
 */
class CheckoutCompleteContext implements Context
{
    public function __construct(
        private CompletePageInterface $completePage,
    ) {
    }

    /**
     * @Then my order payment fee should be :price
     */
    public function myOrderPaymentFeeShouldBe(string $price): void
    {
        Assert::contains($this->completePage->getPaymentFeeTotal(), $price);
    }

    /**
     * @Then I should see payment fee total
     */
    public function iShouldSeePaymentFeeTotal(): void
    {
        Assert::true($this->completePage->hasPaymentFeeTotal());
    }

    /**
     * @Then I should not see payment fee total
     */
    public function iShouldNotSeePaymentFeeTotal(): void
    {
        Assert::false($this->completePage->hasPaymentFeeTotal());
    }
}
