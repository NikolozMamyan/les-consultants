import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'card', 'status'];

    select(event) {
        const category = event.params.category;
        this.buttonTargets.forEach(button => button.setAttribute('aria-pressed', String(button === event.currentTarget)));
        let count = 0;
        this.cardTargets.forEach(card => {
            card.hidden = category !== 'all' && card.dataset.category !== category && card.dataset.category !== 'all';
            if (!card.hidden && card.dataset.category !== 'all') count += 1;
        });
        this.statusTarget.textContent = `${count} domaine${count > 1 ? 's' : ''} d’expertise`;
    }
}
