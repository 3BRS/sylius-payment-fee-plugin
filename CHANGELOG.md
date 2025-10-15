# CHANGELOG

## v2.0.0 (2025-10-15)

- Add support for Sylius 2.0
- Drop support for Sylius <= 1.14

### Migration from v1 to v2

1. **Remove legacy Admin template overrides for your local template `bundles`:**
   - Remove `@SyliusAdmin/PaymentMethod/_form.html.twig`
   - Remove `@AdminBundle/Order/Show/Summary/_totals.html.twig` override

2. **Remove legacy Shop template overrides from your local template `bundles`:**
   - Remove `@ShopBundle/Checkout/SelectPayment/_choice.html.twig` override
   - Remove `@ShopBundle/Checkout/SelectPayment/_payment.html.twig` override
   - Remove `@ShopBundle/Common/Order/Table/_totals.html.twig` override

3. **Add JavaScript/Stimulus integration:**
   - In your admin entrypoint file (e.g., `assets/admin/entrypoint.js` or similar), register the payment calculator controller:
     ```javascript
     import { startStimulusApp } from '@symfony/stimulus-bridge';
     import PaymentCalculatorController from '../vendor/3brs/sylius-payment-fee-plugin/src/Resources/assets/admin/controllers/payment-calculator_controller';

     export const app = startStimulusApp();
     app.register('payment-calculator', PaymentCalculatorController);
     ```
   - Rebuild your assets:
     ```bash
     yarn install
     yarn build
     ```

## v1.3.0 (2025-06-02)

- Add support for Doctrine PHP attributes by `#[ORM\Column]` etc.

## v1.2.0 (2025-03-20)

### Details

 - Add support for Sylius 1.14
 - Drop support for Sylius <= 1.13

## v1.1.0 (2023-01-05)

#### Details

- Add support for Sylius 1.11|1.12, Symfony ^5.4|^6.0, PHP ^8.0
- Drop support for Sylius <= 1.10 and consequentially for Symfony <= 5.3, <= PHP 7.4

## v1.0.0 (2021-11-10)

#### Details

- Support for Sylius 1.8|1.9|1.10, Symfony ^4.4|^5.2, PHP ^7.3|^8.0
- Change namespace from `MangoSylius\PaymentFeePlugin` to `ThreeBRS\SyliusPaymentFeePlugin`
- ⚠️ *BC break: Version 1.0.0 is NOT compatible with previous versions due to **namespace change***
