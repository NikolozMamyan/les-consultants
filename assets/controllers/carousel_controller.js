import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['viewport', 'card', 'dot', 'previous', 'next', 'status'];
    static values = {
        interval: { type: Number, default: 4800 },
        mode: { type: String, default: 'cards' },
    };

    connect() {
        this.activeIndex = Math.min(1, this.cardTargets.length - 1);
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.framePending = false;
        this.pointer = null;
        this.dragged = false;
        this.hovered = false;
        this.focusedWithin = false;
        this.visible = false;
        this.visibilityListener = () => this.syncAutoplay();
        this.motionListener = () => this.syncAutoplay();
        this.resizeObserver = new ResizeObserver(() => this.goTo(this.activeIndex, true));
        this.resizeObserver.observe(this.viewportTarget);
        this.intersectionObserver = new IntersectionObserver(entries => {
            this.visible = entries[0]?.isIntersecting ?? false;
            this.syncAutoplay();
        }, { threshold: 0.35 });
        this.intersectionObserver.observe(this.element);
        document.addEventListener('visibilitychange', this.visibilityListener);
        this.reducedMotion.addEventListener('change', this.motionListener);
        this.applyPresentationMode();
        requestAnimationFrame(() => this.goTo(this.activeIndex, true));
    }

    disconnect() {
        this.stopAutoplay();
        this.resizeObserver?.disconnect();
        this.intersectionObserver?.disconnect();
        document.removeEventListener('visibilitychange', this.visibilityListener);
        this.reducedMotion.removeEventListener('change', this.motionListener);
        this.removeMarqueeClones();
    }

    intervalValueChanged() {
        if (!this.reducedMotion) return;
        this.applyPresentationMode();
        this.syncAutoplay();
    }

    modeValueChanged() {
        if (!this.reducedMotion) return;
        this.applyPresentationMode();

        if ('marquee' === this.modeValue) {
            this.stopAutoplay();
            this.viewportTarget.scrollTo({ left: 0, behavior: 'auto' });
            return;
        }

        requestAnimationFrame(() => this.goTo(this.activeIndex, true));
        this.syncAutoplay();
    }

    applyPresentationMode() {
        const marquee = 'marquee' === this.modeValue;
        this.removeMarqueeClones();
        this.element.classList.toggle('is-compact-marquee', marquee);
        this.element.style.setProperty('--carousel-marquee-duration', `${Math.max(14, Math.min(48, this.intervalValue / 220))}s`);

        if (!marquee) return;
        const track = this.cardTargets[0]?.parentElement;
        this.cardTargets.forEach(card => {
            const clone = card.cloneNode(true);
            clone.dataset.carouselClone = 'true';
            clone.removeAttribute('data-carousel-target');
            clone.removeAttribute('data-admin-card-selected');
            clone.setAttribute('aria-hidden', 'true');
            track?.append(clone);
        });
    }

    removeMarqueeClones() {
        this.element.querySelectorAll('[data-carousel-clone]').forEach(clone => clone.remove());
    }

    previous() {
        this.goTo(this.activeIndex - 1);
        this.syncAutoplay();
    }

    next() {
        this.goTo(this.activeIndex + 1);
        this.syncAutoplay();
    }

    select(event) {
        this.goTo(event.params.index);
        this.syncAutoplay();
    }

    mouseEnter() {
        this.hovered = true;
        this.stopAutoplay();
    }

    mouseLeave() {
        this.hovered = false;
        this.syncAutoplay();
    }

    focusIn() {
        this.focusedWithin = true;
        this.stopAutoplay();
    }

    focusOut(event) {
        if (this.element.contains(event.relatedTarget)) return;
        this.focusedWithin = false;
        this.syncAutoplay();
    }

    keyboard(event) {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const index = event.key === 'Home' ? 0 : event.key === 'End' ? this.cardTargets.length - 1 : this.activeIndex + (event.key === 'ArrowRight' ? 1 : -1);
        this.goTo(index);
        this.syncAutoplay();
    }

    scrolled() {
        if (this.framePending) return;
        this.framePending = true;
        requestAnimationFrame(() => {
            this.setActive(this.nearestIndex());
            this.framePending = false;
        });
    }

    pointerDown(event) {
        if (event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('a, button')) return;
        this.stopAutoplay();
        this.pointer = { id: event.pointerId, x: event.clientX, scroll: this.viewportTarget.scrollLeft };
        this.dragged = false;
    }

    pointerMove(event) {
        if (!this.pointer || this.pointer.id !== event.pointerId) return;
        const delta = event.clientX - this.pointer.x;
        if (!this.dragged && Math.abs(delta) < 5) return;
        if (!this.dragged) {
            this.dragged = true;
            this.viewportTarget.setPointerCapture(event.pointerId);
            this.viewportTarget.classList.add('is-dragging');
        }
        event.preventDefault();
        this.viewportTarget.scrollLeft = this.pointer.scroll - delta;
    }

    pointerUp(event) {
        if (!this.pointer || event.pointerId !== this.pointer.id) return;
        if (this.viewportTarget.hasPointerCapture(this.pointer.id)) this.viewportTarget.releasePointerCapture(this.pointer.id);
        this.pointer = null;
        this.viewportTarget.classList.remove('is-dragging');
        if (this.dragged) this.goTo(this.nearestIndex());
        this.syncAutoplay();
    }

    protectClick(event) {
        if (!this.dragged) return;
        event.preventDefault();
        this.dragged = false;
    }

    cardCenter(card) {
        return card.getBoundingClientRect().left - this.viewportTarget.getBoundingClientRect().left + this.viewportTarget.scrollLeft - (this.viewportTarget.clientWidth - card.offsetWidth) / 2;
    }

    nearestIndex() {
        return this.cardTargets.reduce((best, card, index) => Math.abs(this.cardCenter(card) - this.viewportTarget.scrollLeft) < Math.abs(this.cardCenter(this.cardTargets[best]) - this.viewportTarget.scrollLeft) ? index : best, 0);
    }

    goTo(index, instant = false) {
        if ('marquee' === this.modeValue) {
            return;
        }

        const nextIndex = Math.max(0, Math.min(this.cardTargets.length - 1, Number(index)));
        this.viewportTarget.scrollTo({ left: this.cardCenter(this.cardTargets[nextIndex]), behavior: instant || this.reducedMotion.matches ? 'auto' : 'smooth' });
        this.setActive(nextIndex);
    }

    setActive(index) {
        this.activeIndex = index;
        this.cardTargets.forEach((card, cardIndex) => card.classList.toggle('is-active', cardIndex === index));
        this.dotTargets.forEach((dot, dotIndex) => dot.toggleAttribute('aria-current', dotIndex === index));
        this.previousTarget.disabled = index === 0;
        this.nextTarget.disabled = index === this.cardTargets.length - 1;
        this.statusTarget.textContent = `${this.cardTargets[index].querySelector('h3').textContent}, ${index + 1} sur ${this.cardTargets.length}`;
    }

    syncAutoplay() {
        this.stopAutoplay();

        if ('marquee' === this.modeValue || !this.visible || this.hovered || this.focusedWithin || document.hidden || this.reducedMotion.matches || this.cardTargets.length < 2) {
            return;
        }

        this.autoplayTimer = window.setTimeout(() => {
            this.goTo((this.activeIndex + 1) % this.cardTargets.length);
            this.syncAutoplay();
        }, this.intervalValue);
    }

    stopAutoplay() {
        window.clearTimeout(this.autoplayTimer);
        this.autoplayTimer = null;
    }
}
