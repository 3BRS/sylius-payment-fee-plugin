<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Webmozart\Assert\Assert;

final class PaymentFeeContext extends RawMinkContext implements Context
{
    /**
     * @Then I should see payment fee of :expectedFee
     */
    public function iShouldSeePaymentFeeOf(string $expectedFee): void
    {
        $page = $this->getSession()->getPage();

        // Look for payment fee in order summary - the label is "Payment fees total"
        $paymentFeeLabelElement = $page->find('xpath', '//td[contains(text(), "Payment fees total")]');

        Assert::notNull($paymentFeeLabelElement, 'Payment fees total label not found in order summary');

        // Get the amount from the following sibling td
        $paymentFeeAmountElement = $page->find('xpath', '//td[contains(text(), "Payment fees total")]/following-sibling::td');

        Assert::notNull($paymentFeeAmountElement, 'Payment fee amount not found');

        $text = $paymentFeeAmountElement->getText();
        Assert::contains($text, $expectedFee, sprintf('Expected payment fee "%s" but got "%s"', $expectedFee, $text));
    }

    /**
     * @Then I should not see any payment fee
     */
    public function iShouldNotSeeAnyPaymentFee(): void
    {
        $page = $this->getSession()->getPage();

        // Check for "Payment fees total" text which is used in the template
        $paymentFeeElement = $page->find('xpath', '//*[contains(text(), "Payment fees total")]');

        Assert::null($paymentFeeElement, 'Payment fee should not be displayed');
    }
}
