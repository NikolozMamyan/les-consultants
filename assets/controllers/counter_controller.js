import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        end: Number,
        prefix: { type: String, default: '' },
        suffix: { type: String, default: '' },
    };

    connect() {
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.observer = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;
            this.observer.disconnect();
            this.reducedMotion ? this.render(this.endValue) : this.animate();
        }, { threshold: 0.6 });
        this.observer.observe(this.element);
    }

    disconnect() {
        this.observer?.disconnect();
        cancelAnimationFrame(this.frame);
    }

    animate() {
        const startedAt = performance.now();
        const duration = 1100;
        const tick = now => {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            this.render(Math.round(this.endValue * eased));
            if (progress < 1) this.frame = requestAnimationFrame(tick);
        };
        this.frame = requestAnimationFrame(tick);
    }

    render(value) {
        this.element.textContent = `${this.prefixValue}${value}${this.suffixValue}`;
    }
}
