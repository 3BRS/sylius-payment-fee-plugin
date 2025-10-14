/*
 * Payment Fee Calculator Controller
 * Handles dynamic prototype forms for payment method calculator configuration
 */

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'container'];
    static values = {
        prototypePrefix: String
    };

    connect() {
        // Initialize on load if there's already a selected value
        if (this.selectTarget.value) {
            this.updatePrototype();
        }
    }

    /**
     * Updates the container with the appropriate prototype form when calculator changes
     */
    updatePrototype() {
        const selectedValue = this.selectTarget.value;

        if (!selectedValue) {
            this.containerTarget.innerHTML = '';
            return;
        }

        const prototypeId = `${this.selectTarget.id}_${this.prototypePrefixValue}_${selectedValue}`;
        const prototypeElement = document.getElementById(prototypeId);

        if (!prototypeElement) {
            console.warn('Prototype not found for:', prototypeId);
            this.containerTarget.innerHTML = '';
            return;
        }

        const prototypeHtml = prototypeElement.dataset.prototype;

        if (prototypeHtml) {
            this.containerTarget.innerHTML = prototypeHtml;
        } else {
            this.containerTarget.innerHTML = '';
        }
    }
}