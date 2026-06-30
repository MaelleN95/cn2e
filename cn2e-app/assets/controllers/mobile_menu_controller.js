/* stimulusFetch: 'lazy' */
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
  "menu",
  "button",
  "line1",
  "line2",
  "line3"
];

  connect() {
    this.isOpen = false;
  }

  toggle() {
    this.isOpen ? this.close() : this.open();
  }

  open() {
    this.isOpen = true;

    this.menuTarget.classList.remove("hidden");

    this.line1Target.classList.add("rotate-45", "translate-y-2.5");
    this.line2Target.classList.add("opacity-0");
    this.line3Target.classList.add("-rotate-45", "-translate-y-2.5");

    document.body.classList.add("overflow-hidden");

    this.buttonTarget.setAttribute("aria-expanded", "true");
  }

  close() {
    this.isOpen = false;

    this.menuTarget.classList.add("hidden");

    this.line1Target.classList.remove("rotate-45", "translate-y-2.5");
    this.line2Target.classList.remove("opacity-0");
    this.line3Target.classList.remove("-rotate-45", "-translate-y-2.5");

    document.body.classList.remove("overflow-hidden");

    this.buttonTarget.setAttribute("aria-expanded", "false");
  }
}