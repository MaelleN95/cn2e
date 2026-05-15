import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        "input",
        "city",
        "department",
        "region",
        "lat",
        "lng",
        "dropdown"
    ];

    connect() {
        this.timeout = null;
        this.results = [];
        this.createDropdown();
        console.log('BAN controller loaded');
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className =
            `
            absolute z-50 mt-1 hidden overflow-hidden
            rounded border border-gray-900
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
                border-b border-gray-800 last:border-b-0
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
        const coords = item.geometry.coordinates;

        this.inputTarget.value = props.label;

        // city
        if (this.hasCityTarget) {
            this.cityTarget.value = props.city || '';
        }

        // department (code + name dans context)
        if (this.hasDepartmentTarget) {
            this.departmentTarget.value = props.context || '';
        }

        // region (non fourni directement → simplification FR)
        if (this.hasRegionTarget) {
            this.regionTarget.value = this.extractRegion(props.context);
        }

        // coordonnées (long/lat)
        if (this.hasLatTarget) {
            this.latTarget.value = coords[1];
        }

        if (this.hasLngTarget) {
            this.lngTarget.value = coords[0];
        }

        this.hideDropdown();
    }

    extractRegion(context) {
        if (!context) return '';

        // format BAN: "92, Hauts-de-Seine, Île-de-France"
        const parts = context.split(',');
        return parts.length >= 3 ? parts[2].trim() : '';
    }

    hideDropdown() {
        this.dropdown.classList.add('hidden');
    }
}