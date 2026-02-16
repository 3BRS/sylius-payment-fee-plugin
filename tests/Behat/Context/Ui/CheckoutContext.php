<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class CheckoutContext extends RawMinkContext implements Context
{
    public function __construct(
        private readonly FactoryInterface $productFactory,
        private readonly FactoryInterface $productVariantFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly FactoryInterface $customerFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly RepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @Given the store has a product :productName priced at :price
     */
    public function theStoreHasAProductPricedAt(string $productName, string $price): void
    {
        $productCode = strtolower(str_replace(' ', '_', $productName));

        // Check if product already exists
        $existingProduct = $this->productRepository->findOneBy(['code' => $productCode]);
        if ($existingProduct !== null) {
            return; // Product already exists, skip creation
        }

        $channel = $this->getDefaultChannel();

        $priceInCents = (int) (((float) preg_replace('/[^0-9.]/', '', $price)) * 100);

        /** @var ProductInterface $product */
        $product = $this->productFactory->createNew();
        $product->setCode($productCode);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($productName);
        $product->setSlug(strtolower(str_replace(' ', '-', $productName)));
        $product->addChannel($channel);

        /** @var ProductVariantInterface $variant */
        $variant = $this->productVariantFactory->createNew();
        $variant->setCode($productCode . '_variant');
        $variant->setProduct($product);

        /** @var \Sylius\Component\Core\Model\ChannelPricingInterface $channelPricing */
        $channelPricing = $this->channelPricingFactory->createNew();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice($priceInCents);
        $variant->addChannelPricing($channelPricing);

        $product->addVariant($variant);

        $this->productRepository->add($product);
        $this->entityManager->flush();
    }

    /**
     * @Given I am a logged in customer
     */
    public function iAmALoggedInCustomer(): void
    {
        // Check if customer already exists
        $customer = $this->customerRepository->findOneBy(['email' => 'test@example.com']);

        if ($customer === null) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            $customer->setEmail('test@example.com');
            $customer->setFirstName('John');
            $customer->setLastName('Doe');

            $this->customerRepository->add($customer);
            $this->entityManager->flush();
        }

        // Visit the shop homepage
        $this->visitPath('/en_US/');
    }

    /**
     * @When I add product :productName to the cart
     */
    public function iAddProductToTheCart(string $productName): void
    {
        $productCode = strtolower(str_replace(' ', '_', $productName));
        $product = $this->productRepository->findOneBy(['code' => $productCode]);
        Assert::notNull($product, sprintf('Product "%s" (code: %s) not found', $productName, $productCode));

        $this->visitPath(sprintf('/en_US/products/%s', $product->getSlug()));

        $page = $this->getSession()->getPage();
        $addToCartButton = $page->find('css', 'button[type="submit"]');

        Assert::notNull($addToCartButton, 'Add to cart button not found');
        $addToCartButton->click();
    }

    /**
     * @When I proceed to checkout
     */
    public function iProceedToCheckout(): void
    {
        $this->visitPath('/en_US/checkout/address');

        $page = $this->getSession()->getPage();

        $page->fillField('sylius_shop_checkout_address[customer][email]', 'test@example.com');
        $page->fillField('sylius_shop_checkout_address[billingAddress][firstName]', 'John');
        $page->fillField('sylius_shop_checkout_address[billingAddress][lastName]', 'Doe');
        $page->fillField('sylius_shop_checkout_address[billingAddress][street]', '123 Main St');
        $page->fillField('sylius_shop_checkout_address[billingAddress][city]', 'New York');
        $page->fillField('sylius_shop_checkout_address[billingAddress][postcode]', '10001');
        $page->selectFieldOption('sylius_shop_checkout_address[billingAddress][countryCode]', 'US');

        $nextButton = $page->find('css', 'button[type="submit"]');
        Assert::notNull($nextButton, 'Next button not found on address step');

        $nextButton->click();

        $page = $this->getSession()->getPage();
        $nextButton = $page->find('css', 'button[type="submit"]');
        if ($nextButton !== null) {
            $nextButton->click();
        }
    }

    /**
     * @When I select :paymentMethodName payment method
     */
    public function iSelectPaymentMethod(string $paymentMethodName): void
    {
        $page = $this->getSession()->getPage();

        // Find the payment method radio button by label text
        $paymentMethodLabel = $page->find('xpath', sprintf('//label[contains(text(), "%s")]', $paymentMethodName));
        Assert::notNull($paymentMethodLabel, sprintf('Payment method "%s" not found', $paymentMethodName));

        $paymentMethodLabel->click();
    }

    /**
     * @Then I should see payment fee of :expectedFee
     */
    public function iShouldSeePaymentFeeOf(string $expectedFee): void
    {
        $page = $this->getSession()->getPage();

        // Look for payment fee in order summary
        $paymentFeeElement = $page->find('xpath', '//td[contains(text(), "Payment fee")]/following-sibling::td | //div[contains(text(), "Payment fee")]/following-sibling::div');

        Assert::notNull($paymentFeeElement, 'Payment fee not found in order summary');
        Assert::contains($paymentFeeElement->getText(), $expectedFee, sprintf('Expected payment fee "%s" but got "%s"', $expectedFee, $paymentFeeElement->getText()));
    }

    /**
     * @Then I should not see any payment fee
     */
    public function iShouldNotSeeAnyPaymentFee(): void
    {
        $page = $this->getSession()->getPage();

        $paymentFeeElement = $page->find('xpath', '//td[contains(text(), "Payment fee")] | //div[contains(text(), "Payment fee")]');

        Assert::null($paymentFeeElement, 'Payment fee should not be displayed');
    }

    private function getDefaultChannel(): ChannelInterface
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy([]);

        Assert::notNull($channel, 'No channel found in the database');

        return $channel;
    }
}
