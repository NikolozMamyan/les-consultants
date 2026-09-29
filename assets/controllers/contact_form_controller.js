import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'profile', 'subjectLabel', 'messageLabel'];
    static values = {
        profile: String,
        enterpriseSubjectLabel: { type: String, default: 'Sujet *' },
        consultantSubjectLabel: { type: String, default: 'Votre domaine d’expertise *' },
        enterpriseMessageLabel: { type: String, default: 'Votre message *' },
        consultantMessageLabel: { type: String, default: 'Votre parcours et vos disponibilités *' },
    };

    connect() {
        this.setProfile(this.profileValue === 'consultant' ? 'consultant' : 'entreprise');
    }

    select(event) { this.setProfile(event.params.profile); }

    refreshLabels() { this.setProfile(this.profileTarget.value); }

    setProfile(profile) {
        const consultant = profile === 'consultant';
        this.profileTarget.value = profile;
        this.tabTargets.forEach(tab => {
            const selected = tab.dataset.contactFormProfileParam === profile;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        this.subjectLabelTarget.textContent = consultant ? this.consultantSubjectLabelValue : this.enterpriseSubjectLabelValue;
        this.messageLabelTarget.textContent = consultant ? this.consultantMessageLabelValue : this.enterpriseMessageLabelValue;
    }
}
