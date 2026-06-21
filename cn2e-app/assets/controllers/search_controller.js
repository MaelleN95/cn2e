import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        delay: Number,
        minLength: Number,
    };
    static targets = ["query", "results"];

    connect() {
        this.timeout = null;
        this.abortController = null;
    }

    submit(event) {
        const query = this.queryTarget.value.trim();

        // Evite que la recherche soit déclenchée si l'utilisateur appuie sur "Entrée" dans le champ de recherche
        if (event?.type === "keydown" && event.key === "Enter") {
            event.preventDefault();
        }

        if (query.length > 0 && query.length < this.minLengthValue) {
            return;
        }

        clearTimeout(this.timeout);

        const delay =
            event?.type === "keydown" && event.key === "Enter"
                ? 0
                : this.delayValue || 500;

        this.timeout = setTimeout(() => {
            this.search();
        }, delay);
    }

    async search(event) {
        event?.preventDefault();

        const query = this.queryTarget.value.trim();

        if (query.length > 0 && query.length < this.minLengthValue) {
            return;
        }

        // Crée une nouvelle URL basée sur l'action du formulaire ou l'URL courante
        const url = new URL(this.element.action || window.location.href, window.location.origin);

        if (query) {
            url.searchParams.set("q", query);
        } else {
            url.searchParams.delete("q");
        }

        // Annule toute requête précédente en cours
        this.abortController?.abort();
        this.abortController = new AbortController();

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                throw new Error(`Search request failed with status ${response.status}`);
            }

            // Met à jour le contenu des résultats avec la réponse du serveur
            this.resultsTarget.innerHTML = await response.text();
            window.history.replaceState({}, "", url);
        } catch (error) {
            // Ignore les erreurs d'abandon de requête, mais relance les autres erreurs
            if (error.name !== "AbortError") {
                throw error;
            }
        }
    }
}
