/*
 * Application Admin Entrypoint
 */

import { startStimulusApp } from '@symfony/stimulus-bridge';
import PaymentCalculatorController from '../../../../src/Resources/assets/admin/controllers/payment-calculator_controller';

// Start Stimulus
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

// Register plugin controllers
app.register('payment-calculator', PaymentCalculatorController);

// Set debug mode
app.debug = process.env.NODE_ENV !== 'production';