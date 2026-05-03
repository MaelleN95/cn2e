import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['header'];

  connect() {
    this.lastScroll = 0;
    this.onScroll = this.handleScroll.bind(this);
    window.addEventListener('scroll', this.onScroll)
  }

  disconnect() {
    window.removeEventListener('scroll', this.onScroll);
  }

  handleScroll() {
    const currentScroll = window.scrollY;

    if (currentScroll <= 0) {
      this.headerTarget.classList.remove('slide-up');
      this.lastScroll = 0;
      return;
    }

    if (currentScroll > this.lastScroll) {
      this.headerTarget.classList.add('slide-up');
    } else {
      this.headerTarget.classList.remove('slide-up');
    }

    this.lastScroll = currentScroll;
  }
}
