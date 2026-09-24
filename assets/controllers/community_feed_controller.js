import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['summary', 'content', 'moreButton', 'comments', 'commentsButton'];
    static values = { registrationUrl: String };

    toggleContent() {
        const expanded = this.contentTarget.hidden;

        this.contentTarget.hidden = !expanded;
        this.summaryTarget.hidden = expanded;
        this.moreButtonTarget.textContent = expanded ? 'Réduire' : 'Voir plus';
        this.moreButtonTarget.setAttribute('aria-expanded', String(expanded));
    }

    toggleComments() {
        const expanded = this.commentsTarget.hidden;

        this.commentsTarget.hidden = !expanded;
        this.commentsButtonTarget.setAttribute('aria-expanded', String(expanded));
        this.commentsButtonTarget.classList.toggle('is-active', expanded);
    }

    join(event) {
        event.preventDefault();
        window.location.assign(this.registrationUrlValue);
    }
}
