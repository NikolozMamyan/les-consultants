import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['value'];
    static values = { url: String };

    connect() {
        this.refresh = this.refresh.bind(this);
        this.timer = window.setInterval(this.refresh, 15000);
    }

    disconnect() {
        window.clearInterval(this.timer);
    }

    async refresh() {
        if (!this.hasUrlValue || document.hidden) return;

        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) {
                const data = await response.json();
                this.valueTarget.textContent = data.onlineCount;
            }
        } catch (_) {}
    }
}
