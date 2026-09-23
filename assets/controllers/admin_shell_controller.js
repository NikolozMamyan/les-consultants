import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toast'];

    disconnect() {
        window.clearTimeout(this.toastTimer);
    }

    demo(event) {
        event.preventDefault();
        this.show(event.params.message || 'Cette action sera disponible dans la prochaine étape.');
    }

    notice(event) {
        this.show(event.detail?.message || 'Action effectuée dans la maquette.');
    }

    confirm(event) {
        if (!window.confirm(event.params.message || 'Confirmer cette action ?')) {
            event.preventDefault();
        }
    }

    show(message) {
        window.clearTimeout(this.toastTimer);
        this.toastTarget.textContent = message;
        this.toastTarget.hidden = false;
        requestAnimationFrame(() => this.toastTarget.classList.add('is-visible'));

        this.toastTimer = window.setTimeout(() => {
            this.toastTarget.classList.remove('is-visible');
            window.setTimeout(() => {
                this.toastTarget.hidden = true;
            }, 250);
        }, 3200);
    }
}
