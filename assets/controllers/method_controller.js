import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['step', 'visual', 'count'];

    connect() {
        const selectedIndex = this.stepTargets.findIndex(step => step.getAttribute('aria-selected') === 'true');
        this.show(selectedIndex < 0 ? 0 : selectedIndex);
    }

    select(event) {
        this.show(Number(event.params.index));
    }

    show(index) {
        const activeIndex = Math.max(0, Math.min(this.stepTargets.length - 1, index));

        this.stepTargets.forEach((step, stepIndex) => {
            const selected = stepIndex === activeIndex;
            step.setAttribute('aria-selected', String(selected));
            step.tabIndex = selected ? 0 : -1;
        });

        this.visualTargets.forEach((visual, visualIndex) => {
            const selected = visualIndex === activeIndex;
            visual.hidden = !selected;
            visual.classList.toggle('is-active', selected);
        });

        this.countTarget.textContent = `0${activeIndex + 1} / 03`;
    }

    keyboard(event) {
        const index = this.stepTargets.indexOf(document.activeElement);
        const supportedKeys = ['ArrowLeft', 'ArrowRight', 'ArrowDown', 'ArrowUp', 'Home', 'End'];

        if (index < 0 || !supportedKeys.includes(event.key)) return;

        event.preventDefault();

        const direction = ['ArrowRight', 'ArrowDown'].includes(event.key) ? 1 : -1;
        const nextIndex = event.key === 'Home'
            ? 0
            : event.key === 'End'
                ? this.stepTargets.length - 1
                : (index + direction + this.stepTargets.length) % this.stepTargets.length;

        this.stepTargets[nextIndex].focus();
        this.show(nextIndex);
    }
}
