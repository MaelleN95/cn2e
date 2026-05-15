import { Controller } from '@hotwired/stimulus';
import '../vendor/leaflet/leaflet.css';
import '../vendor/leaflet/leaflet.js';

export default class extends Controller {
    static targets = ["map"];

    connect() {
        this.initMap();
    }

    initMap() {
        const L = window.L;

        this.map = L.map(this.mapTarget).setView([46.6, 2.5], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(this.map);
    }
}