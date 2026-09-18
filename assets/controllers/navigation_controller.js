import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'button'];

    toggle(event) {
        event.stopPropagation();
        this.setOpen(!this.menuTarget.classList.contains('open'));
    }

    close() {
        this.setOpen(false);
    }

    outside(event) {
        if (!event.target.closest('.site-header')) this.close();
    }

    resize() {
        if (window.innerWidth > 900) this.close();
    }

    setOpen(open) {
        this.menuTarget.classList.toggle('open', open);
        this.buttonTarget.setAttribute('aria-expanded', String(open));
        this.buttonTarget.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    }
}
