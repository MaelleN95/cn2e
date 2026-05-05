import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        delay: Number,
        minLength: Number,
    };
    static targets = ["query"];

    connect() {
        this.timeout = null;
    }

    submit(event) {
        const query = this.queryTarget.value.trim();

        if (query.length > 0 && query.length < this.minLengthValue) {
            return;
        }

        clearTimeout(this.timeout);

        const delay =
            event?.type === "keydown" && event.key === "Enter"
                ? 0
                : this.delayValue || 500;

        this.timeout = setTimeout(() => {
            this.element.requestSubmit();
        }, delay);
    }
}
