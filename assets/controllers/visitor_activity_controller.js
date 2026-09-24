import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String };

    connect() {
        this.ping = this.ping.bind(this);
        this.visibilityChanged = this.visibilityChanged.bind(this);
        document.addEventListener('visibilitychange', this.visibilityChanged);
        this.ping();
        this.timer = window.setInterval(this.ping, 30000);
    }

    disconnect() {
        document.removeEventListener('visibilitychange', this.visibilityChanged);
        window.clearInterval(this.timer);
    }

    visibilityChanged() {
        if (!document.hidden) {
            this.ping();
        }
    }

    ping() {
        if (document.hidden || !this.hasUrlValue) {
            return;
        }

        fetch(this.urlValue, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                path: `${window.location.pathname}${window.location.search}`,
                title: document.title,
                referrer: document.referrer,
            }),
        }).catch(() => {});
    }
}
