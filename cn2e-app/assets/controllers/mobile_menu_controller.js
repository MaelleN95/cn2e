import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["burger", "overlay", "headerNavLinks"];

    connect() {
        this.updateScroll();
        this.headerNavLinksTarget.querySelectorAll("a").forEach(link =>
            link.addEventListener("click", () => this.closeMenu())
        );
    }

    toggleMenu() {
        this.updateScroll();
    }

    closeMenu() {
        this.burgerTarget.checked = false;
        this.updateScroll();
    }

    updateScroll() {
        if (this.burgerTarget.checked) {
            document.body.classList.add("no-scroll");
        } else {
            document.body.classList.remove("no-scroll");
        }
    }
}
