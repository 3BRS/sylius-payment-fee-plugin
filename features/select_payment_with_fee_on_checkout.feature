@select_payment_with_fee_on_checkout
Feature: Selecting charged payment in checkout
	In order to payment method with or without fee to pay
	As a Customer
	I want to select payment method and see in final checkout step if I have to pay for that payment method

	Background:
		Given the store operates on a channel named "3BRS Channel"
		And the store operates in "Czechia"
		And the store also has a zone "EU" with code "EU"
		And this zone has the "Czechia" country member
		And the store has a product "PHP T-Shirt" priced at "$19.99"
		And the store has "DHL" shipping method with "$5" fee within the "EU" zone
		And the store allows paying with "CSOB"
		And the store allows paying with "Cash on delivery"
		And the payment method "CSOB" is free of charge
		And the payment method "Cash on delivery" is charged for "$2"

	@ui
	Scenario: Complete order with free of charge payment method
		When I add "PHP T-Shirt" product to the cart
		Then my cart's total should be "$19.99"
		When I complete addressing step with email "john@example.com" and "Czechia" based billing address
		Then I should be on the checkout shipping step
		When I select "DHL" shipping method
		And I complete the shipping step
		Then I should be on the checkout payment step
		And I select "CSOB" payment method
		And I complete the payment step
		Then I should be on the checkout complete step
		And my order shipping should be "$5.00"
		And I should not see payment fee total
		And my order total should be "$24.99"
		When I confirm my order
		Then I should see the thank you page

	@ui
	Scenario: Complete order with charged payment method
		When I add "PHP T-Shirt" product to the cart
		Then my cart's total should be "$19.99"
		When I complete addressing step with email "john@example.com" and "Czechia" based billing address
		Then I should be on the checkout shipping step
		When I select "DHL" shipping method
		And I complete the shipping step
		Then I should be on the checkout payment step
		And I select "Cash on delivery" payment method
		And I complete the payment step
		Then I should be on the checkout complete step
		And my order shipping should be "$5.00"
		And I should see payment fee total
		And my order payment fee should be "$2.00"
		And my order total should be "$26.99"
		When I confirm my order
		Then I should see the thank you page
