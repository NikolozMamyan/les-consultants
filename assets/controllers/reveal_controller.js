import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item'];

    connect() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
            this.itemTargets.forEach(item => item.classList.add('is-revealed'));
            return;
        }
        this.observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                this.observer.unobserve(entry.target);
            });
        }, { threshold: 0.07, rootMargin: '0px 0px -25px' });
        this.itemTargets.forEach((item, index) => {
            if (item.getBoundingClientRect().top < window.innerHeight) return;
            item.classList.add('will-reveal');
            item.style.setProperty('--reveal-delay', `${(index % 3) * 70}ms`);
            this.observer.observe(item);
        });
    }

    disconnect() { this.observer?.disconnect(); }
}
