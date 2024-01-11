# CHANGELOG

## v1.1.0 (2023-01-05)

#### Details

```diff
+ Add support for Sylius 1.11|1.12, Symfony ^5.4|^6.0, PHP ^8.0

- Drop support for Sylius <= 1.10 and consequentially for Symfony <= 5.3, <= PHP 7.4
```
- ⚠️ *BC break: interface \ThreeBRS\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface enforces new methods `setCalculator`, `setCalculatorConfiguration` and `getTaxCategory`*
- 👎 Deprecated translation key `paymentFee_total` in favor of `payment_fee_total`
- 👎 Deprecated [Doctrine `@annotations`](https://www.doctrine-project.org/projects/doctrine-annotations/en/stable/index.html) in favor of [Doctrine `#[Atributtes]`](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/attributes-reference.html)

## v1.0.0 (2021-11-10)

#### Details

- Support for Sylius 1.8|1.9|1.10, Symfony ^4.4|^5.2, PHP ^7.3|^8.0
- Change namespace from `MangoSylius\PaymentFeePlugin` to `ThreeBRS\SyliusPaymentFeePlugin`
- ⚠️ *BC break: Version 1.0.0 is NOT compatible with previous versions due to **namespace change***
