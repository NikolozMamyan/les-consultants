import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'card'];

    select(event) {
        const category = event.params.category;
        this.buttonTargets.forEach(button => button.setAttribute('aria-pressed', String(button === event.currentTarget)));
        this.cardTargets.forEach(card => {
            card.hidden = category !== 'all' && card.dataset.category !== category && card.dataset.category !== 'all';
        });
    }
}
