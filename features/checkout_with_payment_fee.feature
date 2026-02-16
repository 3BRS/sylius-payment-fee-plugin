@checkout_payment_fee @ui
Feature: Checkout with payment fee
    In order to complete my purchase with payment fees
    As a Customer
    I want to see payment fees applied during checkout

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for Free
        And the store allows paying offline

    Scenario: Checkout with non-zero payment fee
        Given the store has a product "Blue T-Shirt" priced at "$20.00"
        And the store has a payment method "Card Payment" with "$5.00" payment fee
        And I added product "Blue T-Shirt" to the cart
        And I am at the checkout addressing step
        When I specify the email as "customer@example.com"
        And I specify the billing address as "New York", "Wall Street", "10005", "United States" for "John Doe"
        And I complete the addressing step
        And I select "Free" shipping method
        And I complete the shipping step
        And I choose "Card Payment" payment method
        Then I should see payment fee of "$5.00"

    Scenario: Checkout with zero payment fee
        Given the store has a product "Green Mug" priced at "$15.00"
        And the store has a payment method "Wire Transfer" without payment fee
        And I added product "Green Mug" to the cart
        And I am at the checkout addressing step
        When I specify the email as "customer@example.com"
        And I specify the billing address as "New York", "Wall Street", "10005", "United States" for "John Doe"
        And I complete the addressing step
        And I select "Free" shipping method
        And I complete the shipping step
        And I choose "Wire Transfer" payment method
        Then I should not see any payment fee
