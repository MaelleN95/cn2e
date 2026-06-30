/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['header'];

  connect() {
    this.lastScroll = 0;
    window.addEventListener('scroll', this.onScroll.bind(this));
  }

  onScroll() {
    const current = window.scrollY;

    if (current < 50) {
      this.headerTarget.classList.remove('-translate-y-full');
    } else if (current > this.lastScroll) {
      this.headerTarget.classList.add('-translate-y-full');
    } else {
      this.headerTarget.classList.remove('-translate-y-full');
    }

    this.lastScroll = current;
  }
}