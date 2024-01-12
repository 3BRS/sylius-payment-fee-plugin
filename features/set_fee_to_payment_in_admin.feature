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

	@ui @javascript
	Scenario: Set fee to a payment method in admin panel
		When I am logged in as an administrator
		Then I should see the payment method "UPS" in the list
		And I should see the payment method "Cash on Delivery" in the list

		When I want to modify the "Cash on Delivery" payment method
		Then I should see that payment method "Cash on Delivery" has no fee

		When I want to modify the "Cash on Delivery" payment method
		And I set fee "2.00" to "Cash on Delivery" payment method
		Then I should see that payment method "Cash on Delivery" has fee "2.00"
		And I want to modify the "UPS" payment method
		And I should see that payment method "UPS" has no fee

		When I want to modify the "Cash on Delivery" payment method
		And I set fee "0.00" to "Cash on Delivery" payment method
		Then I should see that payment method "Cash on Delivery" has no fee
		And I want to modify the "UPS" payment method
		And I should see that payment method "UPS" has no fee

		When I want to modify the "Cash on Delivery" payment method
		And I set fee "999.00" to "Cash on Delivery" payment method
		Then I should see that payment method "Cash on Delivery" has fee "999.00"
		And I want to modify the "UPS" payment method
		And I should see that payment method "UPS" has no fee

		When I want to modify the "Cash on Delivery" payment method
		And I remove fee from "Cash on Delivery" payment method
		Then I should see that payment method "Cash on Delivery" has no fee
		And I want to modify the "UPS" payment method
		And I should see that payment method "UPS" has no fee
