import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item'];

    toggle(event) {
        if (!event.currentTarget.open) return;
        this.itemTargets.forEach(item => {
            if (item !== event.currentTarget) item.open = false;
        });
    }
}
