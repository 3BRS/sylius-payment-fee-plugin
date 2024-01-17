@set_fee_to_payment_in_admin
Feature: Admin can set fee to a payment method in admin panel
	In order to charge payment method
	As an Administrator
	I want to set a fee to a payment method and see that the fee was set in admin panel

	Background:
		Given the store operates on a single channel in "United States"
		And the store has a product "Angel T-Shirt"
		And the store allows paying with "UPS"
		And the store also allows paying with "Cash on Delivery"

	@ui
	Scenario: Set fee to a payment method in admin panel
		When I am logged in as an administrator
		And I browse payment methods
		Then I should see the payment method "UPS" in the list
		And I should see the payment method "Cash on Delivery" in the list

# TODO Behat with JS because amount field is rendered by JS
