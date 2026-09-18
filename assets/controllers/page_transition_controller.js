import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['panel'];

    connect() {
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.destinationUrl = this.element.dataset.destinationUrl || null;
        this.visitAction = this.element.dataset.visitAction || null;
        this.navigationType = this.element.dataset.navigationType || null;
        this.beforeVisitListener = event => this.beforeVisit(event);
        this.beforeRenderListener = event => this.beforeRender(event);
        this.submitStartListener = () => this.beforeSubmit();
        this.loadListener = () => this.completeNavigation();
        this.errorListener = () => this.hide();

        document.addEventListener('turbo:before-visit', this.beforeVisitListener);
        document.addEventListener('turbo:before-render', this.beforeRenderListener);
        document.addEventListener('turbo:submit-start', this.submitStartListener);
        document.addEventListener('turbo:load', this.loadListener);
        document.addEventListener('turbo:fetch-request-error', this.errorListener);

        if (!this.element.classList.contains('is-visible')) {
            this.element.hidden = true;
        } else {
            document.documentElement.classList.add('is-turbo-navigating');
        }
    }

    disconnect() {
        window.clearTimeout(this.hideTimer);
        window.clearTimeout(this.finishTimer);
        document.removeEventListener('turbo:before-visit', this.beforeVisitListener);
        document.removeEventListener('turbo:before-render', this.beforeRenderListener);
        document.removeEventListener('turbo:submit-start', this.submitStartListener);
        document.removeEventListener('turbo:load', this.loadListener);
        document.removeEventListener('turbo:fetch-request-error', this.errorListener);
        if (this.element.hidden || !this.element.classList.contains('is-visible')) {
            document.documentElement.classList.remove('is-turbo-navigating');
        }
    }

    beforeVisit(event) {
        this.destinationUrl = event.detail.url;
        this.visitAction = event.detail.action ?? 'advance';
        this.navigationType = 'visit';
        this.persistNavigationState();
        document.documentElement.classList.add('is-turbo-navigating');
        this.show(this.pageFromUrl(this.destinationUrl));
    }

    beforeSubmit() {
        this.destinationUrl = window.location.href;
        this.navigationType = 'submit';
        this.persistNavigationState();
        document.documentElement.classList.add('is-turbo-navigating');
        this.show(this.pageFromUrl(this.destinationUrl));
    }

    beforeRender(event) {
        if (this.reducedMotion.matches || !document.startViewTransition) {
            return;
        }

        const render = event.detail.render;
        event.detail.render = (currentBody, newBody) => {
            const transition = document.startViewTransition(() => render(currentBody, newBody));

            return transition.updateCallbackDone;
        };
    }

    show(page) {
        window.clearTimeout(this.hideTimer);
        window.clearTimeout(this.finishTimer);
        this.panelTargets.forEach(panel => {
            panel.hidden = panel.dataset.page !== page;
        });

        this.element.hidden = false;
        this.element.classList.remove('is-leaving');
        this.element.classList.add('is-covering', 'is-visible');
        this.shownAt = performance.now();
        requestAnimationFrame(() => this.element.classList.remove('is-covering'));
    }

    completeNavigation() {
        if (this.element.hidden || !this.element.classList.contains('is-visible')) {
            return;
        }

        requestAnimationFrame(() => {
            this.positionDestination();
            requestAnimationFrame(() => this.hide());
        });
    }

    positionDestination() {
        const destinationUrl = this.destinationUrl ?? this.element.dataset.destinationUrl ?? window.location.href;
        const navigationType = this.navigationType ?? this.element.dataset.navigationType;
        const visitAction = this.visitAction ?? this.element.dataset.visitAction;
        const destination = new URL(destinationUrl, window.location.origin);

        if (destination.hash) {
            const id = decodeURIComponent(destination.hash.slice(1));
            const target = document.getElementById(id);

            if (target) {
                const scrollPadding = Number.parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
                const top = target.getBoundingClientRect().top + window.scrollY - scrollPadding;
                this.jumpTo(Math.max(0, top));
                return;
            }
        }

        if (navigationType === 'visit' && visitAction !== 'restore') {
            this.jumpTo(0);
        }
    }

    jumpTo(top) {
        const previousBehavior = document.documentElement.style.scrollBehavior;
        document.documentElement.style.scrollBehavior = 'auto';
        window.scrollTo(0, top);
        document.documentElement.style.scrollBehavior = previousBehavior;
    }

    persistNavigationState() {
        this.element.dataset.destinationUrl = this.destinationUrl;
        this.element.dataset.navigationType = this.navigationType;

        if (this.visitAction) {
            this.element.dataset.visitAction = this.visitAction;
        } else {
            delete this.element.dataset.visitAction;
        }
    }

    hide() {
        if (this.element.hidden || !this.element.classList.contains('is-visible')) {
            return;
        }

        const visibleFor = performance.now() - (this.shownAt ?? 0);
        const delay = Math.max(0, 280 - visibleFor);

        window.clearTimeout(this.hideTimer);
        this.hideTimer = window.setTimeout(() => {
            this.element.classList.add('is-leaving');
            this.element.classList.remove('is-visible');
            this.finishTimer = window.setTimeout(() => {
                this.element.hidden = true;
                this.element.classList.remove('is-leaving', 'is-covering');
                document.documentElement.classList.remove('is-turbo-navigating');
                this.destinationUrl = null;
                this.visitAction = null;
                this.navigationType = null;
                delete this.element.dataset.destinationUrl;
                delete this.element.dataset.visitAction;
                delete this.element.dataset.navigationType;
            }, this.reducedMotion.matches ? 0 : 260);
        }, delay);
    }

    pageFromUrl(value) {
        const path = new URL(value, window.location.origin).pathname.replace(/\/$/, '') || '/';

        return {
            '/': 'home',
            '/expertises': 'services',
            '/a-propos': 'cabinet',
            '/contact': 'contact',
            '/deposer': 'deposit',
        }[path] ?? 'home';
    }
}
