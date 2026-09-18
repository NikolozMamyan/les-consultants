import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog', 'flow', 'flowOption', 'eyebrow', 'title', 'intro'];
    static values = { open: Boolean, flow: String };

    connect() {
        this.currentStep = 0;

        if (this.openValue) {
            requestAnimationFrame(() => this.show(this.flowValue, true));
        }
    }

    open(event) {
        this.show(event.params.flow, false);
    }

    switchFlow(event) {
        const flow = event.params.flow === 'consultant' ? 'consultant' : 'mission';

        if (this.activeFlow === flow) {
            return;
        }

        this.show(flow, false);
    }

    show(flow, preserveErrors) {
        const activeFlow = flow === 'consultant' ? 'consultant' : 'mission';

        this.flowTargets.forEach(element => {
            element.hidden = element.dataset.flow !== activeFlow;
        });

        this.flowOptionTargets.forEach(element => {
            const selected = element.dataset.flow === activeFlow;
            element.classList.toggle('is-active', selected);
            element.setAttribute('aria-selected', selected ? 'true' : 'false');
        });

        this.updateHeading(activeFlow);
        const form = this.activeForm;
        this.currentStep = preserveErrors ? this.errorStep(form) : 0;
        this.update(form);

        if (!this.dialogTarget.open) {
            this.dialogTarget.showModal();
        }

        document.body.classList.add('modal-open');
        this.dialogTarget.scrollTop = 0;
        requestAnimationFrame(() => this.focusStep(form));
    }

    close() {
        this.dialogTarget.close();
    }

    closed() {
        document.body.classList.remove('modal-open');
    }

    backdrop(event) {
        if (event.target === this.dialogTarget) {
            this.close();
        }
    }

    next(event) {
        const form = event.currentTarget.closest('form');

        if (!this.validateStep(form)) {
            return;
        }

        this.currentStep += 1;
        this.update(form);
        this.focusStep(form);
    }

    previous(event) {
        const form = event.currentTarget.closest('form');
        this.currentStep = Math.max(0, this.currentStep - 1);
        this.update(form);
        this.focusStep(form);
    }

    validateSubmit(event) {
        const form = event.currentTarget;

        if (form.checkValidity()) {
            return;
        }

        event.preventDefault();
        const invalidField = form.querySelector(':invalid');
        const invalidStep = invalidField?.closest('.submission-step');

        if (invalidStep) {
            this.currentStep = Number(invalidStep.dataset.step);
            this.update(form);
            requestAnimationFrame(() => invalidField.reportValidity());
        }
    }

    validateStep(form) {
        const step = this.steps(form)[this.currentStep];
        const invalidField = step.querySelector(':invalid');

        if (!invalidField) {
            return true;
        }

        invalidField.reportValidity();
        return false;
    }

    update(form) {
        const steps = this.steps(form);

        steps.forEach((step, index) => {
            step.hidden = index !== this.currentStep;
        });

        form.querySelectorAll('.submission-progress > span').forEach((item, index) => {
            item.classList.toggle('is-active', index === this.currentStep);
            item.classList.toggle('is-complete', index < this.currentStep);
            item.toggleAttribute('aria-current', index === this.currentStep);
        });

        form.querySelector('[data-role="previous"]').hidden = this.currentStep === 0;
        form.querySelector('[data-role="next"]').hidden = this.currentStep === steps.length - 1;
        form.querySelector('[data-role="submit"]').hidden = this.currentStep !== steps.length - 1;
        form.querySelector('.submission-step-count').textContent = `Étape ${this.currentStep + 1} sur ${steps.length}`;
        this.dialogTarget.querySelector('.submission-modal-body').scrollTop = 0;
    }

    updateHeading(flow) {
        const consultant = flow === 'consultant';
        this.eyebrowTarget.textContent = consultant ? 'Votre profil' : 'Votre besoin';
        this.titleTarget.textContent = consultant ? 'Présentez votre profil' : 'Déposez votre mission';
        this.introTarget.textContent = consultant
            ? 'Rejoignez notre réseau pour recevoir des missions ciblées selon votre expertise.'
            : 'Quelques informations suffisent pour lancer la recherche du bon expert.';
    }

    errorStep(form) {
        const error = form.querySelector('.field > ul, .consent > ul');
        const step = error?.closest('.submission-step');

        return step ? Number(step.dataset.step) : 0;
    }

    focusStep(form) {
        const step = this.steps(form)[this.currentStep];
        step?.querySelector('input:not([type="hidden"]), select, textarea')?.focus({ preventScroll: true });
    }

    steps(form) {
        return [...form.querySelectorAll('.submission-step')];
    }

    get activeForm() {
        return this.flowTargets.find(element => !element.hidden).querySelector('form');
    }

    get activeFlow() {
        return this.flowTargets.find(element => !element.hidden)?.dataset.flow;
    }
}
