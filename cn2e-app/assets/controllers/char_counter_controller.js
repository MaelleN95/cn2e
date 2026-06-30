/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'count'];
    static values = {
        max: Number,
    };

    connect() {
        this.update();
    }

    update() {
        const length = this.inputTarget.value.length;
        this.countTarget.textContent = length;

        if (length > this.maxValue) {
            this.countTarget.classList.add('text-red-500');
            this.inputTarget.classList.add('border-red-500');
        } else {
            this.countTarget.classList.remove('text-red-500');
            this.inputTarget.classList.remove('border-red-500');
        }
    }

    input() {
        this.update();
    }
}