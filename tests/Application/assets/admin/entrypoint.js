/*
 * Application Admin Entrypoint
 */

import { startStimulusApp } from '@symfony/stimulus-bridge';
import PaymentCalculatorController from '../../../../src/Resources/assets/admin/controllers/payment-calculator_controller';

// Start Stimulus
export const app = startStimulusApp();

// Register plugin controllers
app.register('payment-calculator', PaymentCalculatorController);

// Set debug mode
app.debug = process.env.NODE_ENV !== 'production';