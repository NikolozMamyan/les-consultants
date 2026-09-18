import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'panel'];

    select(event) {
        const panelId = event.params.panel;
        this.tabTargets.forEach(tab => {
            const selected = tab === event.currentTarget;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        this.panelTargets.forEach(panel => panel.hidden = panel.id !== panelId);
    }

    keyboard(event) {
        const index = this.tabTargets.indexOf(document.activeElement);
        if (index < 0 || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? this.tabTargets.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + this.tabTargets.length) % this.tabTargets.length;
        this.tabTargets[nextIndex].focus();
        this.tabTargets[nextIndex].click();
    }
}
