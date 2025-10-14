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
        // Only initialize if container is empty
        if (this.containerTarget.innerHTML.trim() === '') {
            // If there's a selected value OR the select has options (browser will default to first)
            const hasValue = this.selectTarget.value || this.selectTarget.options.length > 0;
            if (hasValue) {
                this.updatePrototype();
            }
        }
        // If container has content (edit mode with existing values), don't overwrite it
    }

    /**
     * Updates the container with the appropriate prototype form when calculator changes
     */
    updatePrototype() {
        // Get selected value, or use first option's value if none is explicitly selected
        let selectedValue = this.selectTarget.value;

        if (!selectedValue && this.selectTarget.options.length > 0) {
            selectedValue = this.selectTarget.options[0].value;
        }

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