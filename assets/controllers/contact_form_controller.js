import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'profile', 'subjectLabel', 'messageLabel'];
    static values = {
        profile: String,
        enterpriseSubjectLabel: { type: String, default: 'Subject *' },
        consultantSubjectLabel: { type: String, default: 'Your area of expertise *' },
        enterpriseMessageLabel: { type: String, default: 'Your message *' },
        consultantMessageLabel: { type: String, default: 'Your background and availability *' },
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
