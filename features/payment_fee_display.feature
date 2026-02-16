@checkout_payment_fee @verification
Feature: Payment fee display during checkout
    In order to understand the total cost
    As a Customer
    I want to see payment fees displayed when I select a payment method

    Background:
        Given the store operates on a single channel in "USD"

    Scenario: Payment fee is calculated for payment method with fee
        Given the store has a payment method "Credit Card" with "$5.00" payment fee
        And the store has a payment method "Bank Transfer" without payment fee
        Then the payment method "Credit Card" should have fee calculator configured
        And the payment method "Credit Card" fee amount should be "500" cents
        And the payment method "Bank Transfer" fee amount should be "0" cents
