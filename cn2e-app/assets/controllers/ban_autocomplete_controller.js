/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        "input",
        "dropdown"
    ];

    connect() {
        this.timeout = null;
        this.results = [];
        this.createDropdown();
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className =
            `
            absolute z-50 mt-1 hidden overflow-hidden
            rounded border !border-gray-900
            bg-[#0f1115]

            md:w-[calc(50vw-2rem)]
            w-[calc(90vw-2rem)]
            overflow-y-auto
            max-h-80
            `;
        this.inputTarget.parentNode.appendChild(this.dropdown);
    }

    search(event) {
        clearTimeout(this.timeout);

        const query = event.target.value;

        if (query.length < 3) {
            this.hideDropdown();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchAddresses(query);
        }, 250);
    }

    async fetchAddresses(query) {
        const url = `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`;

        const res = await fetch(url);
        const data = await res.json();

        this.results = data.features || [];

        this.renderResults();
    }

    renderResults() {
        if (!this.results.length) {
            this.hideDropdown();
            return;
        }

        this.dropdown.innerHTML = "";

        this.results.forEach((item) => {
            const div = document.createElement('div');
            div.className =
                `
                px-4 py-3 text-sm cursor-pointer transition-colors
                text-gray-200
                hover:bg-blue-800
                hover:text-white
                !border-b !border-gray-800 last:!border-b-0
                `;

            div.textContent = item.properties.label;

            div.addEventListener('click', () => {
                this.selectAddress(item);
            });

            this.dropdown.appendChild(div);
        });

        this.dropdown.classList.remove('hidden');
    }

    selectAddress(item) {
        const props = item.properties;

        this.inputTarget.value = props.label;

        this.hideDropdown();
    }

    hideDropdown() {
        this.dropdown.classList.add('hidden');
    }
}