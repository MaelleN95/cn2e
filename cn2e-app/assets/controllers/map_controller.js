import { Controller } from '@hotwired/stimulus';
import '../vendor/leaflet/leaflet.css';
import '../vendor/leaflet/leaflet.js';

export default class extends Controller {
    static targets = ["map"];

    static values = {
        endpoint: String,
    };

    async connect() {
        this.initMap();

        await this.loadEstablishments();
    }

    initMap() {
        const L = window.L;

        // Limites géographiques approximatives de l'Europe
        const europeBounds = L.latLngBounds(
            [34, -25], // Sud-Ouest
            [72, 45]  // Nord-Est
        );

        this.map = L.map(this.mapTarget, {
            maxBounds: europeBounds,
            maxBoundsViscosity: 1.0,
            minZoom: 4,
        }).setView([47, 2.5], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(this.map);
    }

    async loadEstablishments() {
        const response = await fetch(this.endpointValue);

        if (!response.ok) {
            return;
        }

        const establishments = await response.json();

        this.addMarkers(establishments);
    }

    addMarkers(establishments) {
        const L = window.L;

        establishments.forEach((establishment) => {
            if (
                establishment.latitude === null ||
                establishment.longitude === null
            ) {
                return;
            }

            const popupContent = `
                <div class="space-y-2">
                    <div>
                        <strong>${establishment.name}</strong>
                    </div>

                    <div>
                        ${establishment.city}
                        ${establishment.region
                            ? `(${establishment.region})`
                            : ''}
                    </div>

                    <a
                        href="${establishment.url}"
                        class="text-blue-600 underline"
                    >
                        Voir la fiche
                    </a>
                </div>
            `;

            L.marker([establishment.latitude, establishment.longitude], {
                icon: this.getIcon(establishment)
            })
            .addTo(this.map)
            .bindPopup(popupContent);
        });
    }

    getIcon(establishment) {
        return L.divIcon({
            className: '',
            html: `
                <svg viewBox="0 0 24 24" fill="none" stroke="#003399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" fill="white"/>
                    <circle cx="12" cy="10" r="3" fill="#003399"/>
                </svg>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }
}