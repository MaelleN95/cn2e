import { Controller } from '@hotwired/stimulus';
import '../vendor/leaflet/leaflet.css';
import '../vendor/leaflet/leaflet.js';

export default class extends Controller {
    static targets = ['map', 'popup', 'overlay', 'message'];

    static values = {
        endpoint: String,
    };
    
    selected = null;

    mapLocked = true;
    messageVisible = false;

    async connect() {
        this.initMap();
        await this.loadEstablishments();
        this.initInteractions();
        this.handleResize();

        window.addEventListener('resize', this.resizeHandler);
    }

    disconnect() {
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }
    }

    handleResize() {
        if (!this.map) return;

        this.map.invalidateSize();

        // recentrage propre après resize
        this.map.setView([47, 2.5], this.getInitialZoom());
    }

    getInitialZoom() {
        return this.isMobile() ? 5 : 6;
    }

    isMobile() {
        return window.matchMedia('(max-width: 768px)').matches;
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

            scrollWheelZoom: false,
            dragging: true,
            touchZoom: true,
            tap: true,
        }).setView([47, 2.5], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(this.map);
    }

    initInteractions() {
        const container = this.map.getContainer();

        if (this.isMobile()) {
            this.initMobileInteractions(container);
        } else {
            this.initDesktopInteractions(container);
        }
    }

    initMobileInteractions(container) {
        // Mobile = interaction simple
        this.mapLocked = false;

        container.addEventListener('click', () => {
            this.unlockMap();
        });
    }

    initDesktopInteractions(container) {
        // tentative de scroll -> affiche message
        container.addEventListener('wheel', (e) => {
            if (this.mapLocked) {
                this.showHint();
            }
        }, { passive: false });

        // clic -> unlock
        container.addEventListener('click', () => {
            this.unlockMap();
        });

        // sortie de souris -> lock silencieux
        container.addEventListener('mouseleave', (e) => {
            const x = e.clientX;
            const y = e.clientY;

            const el = document.elementFromPoint(x, y);

            // si on est encore dans un élément lié à la carte (popup incluse)
            if (el && this.elementBelongsToMap(el)) {
                return;
            }

            this.lockMap();
        });  
    }

    elementBelongsToMap(el) {
        if (!el) return false;

        // carte elle-même
        if (this.map.getContainer().contains(el)) return true;

        // popups custom Stimulus
        if (this.hasPopupTarget && this.popupTarget.contains(el)) return true;

        return false;
    }

    lockMap() {
        this.mapLocked = true;
        this.map.scrollWheelZoom.disable();

        this.hideHint();
    }

    unlockMap() {
        this.mapLocked = false;
        this.map.scrollWheelZoom.enable();

        this.hideHint();
    }

    showHint() {
        if (this.messageVisible) return;

        this.messageTarget.classList.remove('hidden');
        this.overlayTarget.classList.add('bg-black/20');

        this.messageVisible = true;
    }

    hideHint() {
        this.messageTarget.classList.add('hidden');
        this.overlayTarget.classList.remove('bg-black/20');

        this.messageVisible = false;
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

            

            L.marker([establishment.latitude, establishment.longitude], {
                icon: this.getIcon(establishment)
            })
            .addTo(this.map)
            .on('click', () => {
                this.selected = establishment;
                this.renderPopup();
            })
        });
    }

    getIcon(establishment) {
        const L = window.L;

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

    renderPopup() {
        if (!this.selected) return;

        this.popupTarget.innerHTML = `
            <div
                class="
                    absolute
                    bottom-3
                    left-3
                    right-3
                    z-[1001]
                    rounded
                    bg-white
                    p-4
                    shadow-2xl
                    border
                    !border-gray-200
                "
            >
                <button
                    data-action="click->map#closePopup"
                    class="
                        absolute
                        right-3
                        top-1
                        text-lg
                        font-semibold
                        text-gray-500
                        transition
                        hover:text-gray-900
                        cursor-pointer
                    "
                    type="button"
                    aria-label="Fermer"
                >
                    ✕
                </button>

                <div class="pr-8">
                    <h3 class="text-base font-semibold text-gray-900">
                        ${this.selected.name}
                    </h3>

                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                        ${
                            this.selected.address
                                ? `
                                    <p>
                                        ${this.selected.address}
                                    </p>
                                `
                                : ''
                        }

                        ${
                            this.selected.phone
                                ? `
                                    <div class="flex items-center gap-2">
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="shrink-0 text-gray-500 w-4 h-4"
                                        >
                                            <path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>
                                        </svg>

                                        <a
                                            href="tel:${this.selected.phone}"
                                            class="hover:text-gray-900 transition"
                                        >
                                            ${this.selected.phone}
                                        </a>
                                    </div>
                                `
                                : ''
                        }
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        ${
                            this.selected.website
                                ? `
                                    <a
                                        href="${this.selected.website}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="
                                            inline-flex
                                            items-center
                                            rounded-md
                                            border
                                            border-gray-300
                                            px-3
                                            py-1.5
                                            text-sm
                                            font-medium
                                            text-gray-700
                                            transition
                                            hover:bg-gray-100
                                        "
                                    >
                                        Site web
                                    </a>
                                `
                                : ''
                        }

                        <a
                            href="${this.selected.url}"
                            class="
                                inline-flex
                                items-center
                                rounded-md
                                bg-primary 
                                text-primary-foreground 
                                px-3
                                py-1.5
                                rounded 
                                hover:bg-primary/80 
                                transition
                            "
                        >
                            Voir la fiche
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    closePopup() {
        this.selected = null;
        this.popupTarget.innerHTML = '';
    }
}