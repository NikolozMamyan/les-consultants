import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'profile', 'subjectLabel', 'messageLabel'];
    static values = { profile: String };

    connect() {
        this.setProfile(this.profileValue === 'consultant' ? 'consultant' : 'entreprise');
    }

    select(event) { this.setProfile(event.params.profile); }

    setProfile(profile) {
        const consultant = profile === 'consultant';
        this.profileTarget.value = profile;
        this.tabTargets.forEach(tab => {
            const selected = tab.dataset.contactFormProfileParam === profile;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        this.subjectLabelTarget.textContent = consultant ? 'Votre domaine d’expertise' : 'Sujet';
        this.messageLabelTarget.textContent = consultant ? 'Votre parcours et vos disponibilités' : 'Votre message';
    }
}
