import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'label', 'icon'];

    connect() {
        this.inView = true;
        this.observer = new IntersectionObserver(([entry]) => {
            this.inView = entry.isIntersecting;
            this.updateIdleState();
        }, { threshold: 0.05 });
        this.observer.observe(this.element);
        this.visibilityHandler = () => this.updateIdleState();
        document.addEventListener('visibilitychange', this.visibilityHandler);
    }

    disconnect() {
        this.observer?.disconnect();
        document.removeEventListener('visibilitychange', this.visibilityHandler);
        document.body.classList.remove('radar-idle', 'motion-paused');
    }

    toggle() {
        const paused = document.body.classList.toggle('motion-paused');
        this.toggleTarget.setAttribute('aria-pressed', String(paused));
        this.toggleTarget.setAttribute('aria-label', paused ? 'Reprendre l’animation du radar' : 'Mettre l’animation du radar en pause');
        this.labelTarget.textContent = paused ? 'Animer' : 'Pause';
        this.iconTarget.setAttribute('d', paused ? 'm8 4 12 8-12 8V4Z' : 'M8 5v14M16 5v14');
    }

    updateIdleState() {
        document.body.classList.toggle('radar-idle', document.hidden || !this.inView);
    }
}
