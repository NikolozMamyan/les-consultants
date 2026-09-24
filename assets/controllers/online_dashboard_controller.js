import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'count', 'countLabel', 'activeLast15', 'sessionsToday', 'averageSession', 'updatedAt',
        'timeline', 'devices', 'deviceRing', 'pages', 'sources', 'visitors', 'status',
    ];

    static values = {
        url: String,
        snapshot: Object,
    };

    connect() {
        this.refresh = this.refresh.bind(this);
        this.render(this.snapshotValue);
        this.timer = window.setInterval(this.refresh, 15000);
    }

    disconnect() {
        window.clearInterval(this.timer);
    }

    async refresh() {
        if (!this.hasUrlValue || document.hidden) {
            return;
        }

        this.statusTarget.classList.add('is-loading');
        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                throw new Error('Audience indisponible');
            }
            this.render(await response.json());
        } catch (_) {
            this.statusTarget.textContent = 'Reconnexion…';
            this.statusTarget.classList.add('is-offline');
        } finally {
            this.statusTarget.classList.remove('is-loading');
        }
    }

    render(data) {
        this.countTargets.forEach((target) => { target.textContent = data.onlineCount; });
        this.countLabelTarget.textContent = `${data.onlineCount === 1 ? 'visiteur actif' : 'visiteurs actifs'}`;
        this.activeLast15Target.textContent = data.activeLast15;
        this.sessionsTodayTarget.textContent = data.sessionsToday;
        this.averageSessionTarget.textContent = data.averageSession;
        this.updatedAtTarget.textContent = `Actualisé à ${this.time(data.generatedAt)}`;
        this.statusTarget.textContent = 'Données en direct';
        this.statusTarget.classList.remove('is-offline');
        this.renderTimeline(data.timeline || []);
        this.renderDevices(data.devices || []);
        this.renderPages(data.pages || []);
        this.renderSources(data.sources || []);
        this.renderVisitors(data.visitors || []);
    }

    renderTimeline(points) {
        this.timelineTarget.replaceChildren(...points.map((point) => {
            const column = this.node('div', 'admin-live-chart-column');
            column.style.setProperty('--live-height', `${point.share}%`);
            const value = this.node('span', '', point.count);
            const bar = this.node('i');
            const label = this.node('small', '', point.label);
            column.append(value, bar, label);
            return column;
        }));
    }

    renderDevices(devices) {
        const values = Object.fromEntries(devices.map((device) => [device.key, device.share]));
        const mobileEnd = values.mobile || 0;
        const tabletEnd = mobileEnd + (values.tablet || 0);
        this.deviceRingTarget.style.setProperty('--mobile-end', `${mobileEnd * 3.6}deg`);
        this.deviceRingTarget.style.setProperty('--tablet-end', `${tabletEnd * 3.6}deg`);

        this.devicesTarget.replaceChildren(...devices.map((device) => {
            const row = this.node('div', `admin-device-row admin-device-${device.key}`);
            const label = this.node('span');
            label.append(this.node('i'), document.createTextNode(device.label));
            row.append(label, this.node('strong', '', device.count), this.node('small', '', `${device.share} %`));
            return row;
        }));
    }

    renderPages(pages) {
        if (pages.length === 0) {
            this.pagesTarget.replaceChildren(this.empty('Les pages actives apparaîtront ici.'));
            return;
        }

        this.pagesTarget.replaceChildren(...pages.map((page) => {
            const row = this.node('article');
            const copy = this.node('span');
            copy.append(this.node('strong', '', page.title), this.node('small', '', page.path));
            const progress = this.node('i');
            const bar = this.node('b');
            bar.style.setProperty('--page-share', `${page.share}%`);
            progress.append(bar);
            row.append(copy, progress, this.node('em', '', page.count));
            return row;
        }));
    }

    renderSources(sources) {
        if (sources.length === 0) {
            this.sourcesTarget.replaceChildren(this.empty('Aucune source active.'));
            return;
        }

        this.sourcesTarget.replaceChildren(...sources.map((source, index) => {
            const row = this.node('article');
            row.append(this.node('span', '', String(index + 1).padStart(2, '0')), this.node('strong', '', source.label), this.node('b', '', source.count));
            return row;
        }));
    }

    renderVisitors(visitors) {
        if (visitors.length === 0) {
            const empty = this.empty('Aucun visiteur actif dans les deux dernières minutes.');
            empty.classList.add('admin-live-empty-large');
            this.visitorsTarget.replaceChildren(empty);
            return;
        }

        this.visitorsTarget.replaceChildren(...visitors.map((visitor) => {
            const row = this.node('article', 'admin-live-visitor');
            const identity = this.node('span', 'admin-live-identity');
            identity.append(this.node('i', `admin-live-device admin-live-device-${visitor.device}`), this.node('span'));
            identity.lastElementChild.append(this.node('strong', '', `Visiteur ${visitor.id}`), this.node('small', '', `${visitor.browser} · ${this.deviceLabel(visitor.device)}`));

            const page = this.node('span', 'admin-live-page');
            page.append(this.node('strong', '', visitor.pageTitle), this.node('small', '', visitor.pagePath));

            const session = this.node('span', 'admin-live-session');
            session.append(this.node('strong', '', this.duration(visitor.firstSeenAt)), this.node('small', '', `${visitor.pageViews} page${visitor.pageViews > 1 ? 's' : ''}`));

            const source = this.node('span', 'admin-live-source');
            source.append(this.node('strong', '', visitor.source), this.node('small', '', this.ago(visitor.lastSeenAt)));
            row.append(identity, page, session, source);
            return row;
        }));
    }

    node(tag, className = '', text = null) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== null) element.textContent = text;
        return element;
    }

    empty(message) {
        return this.node('p', 'admin-live-empty', message);
    }

    time(value) {
        return new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(new Date(value));
    }

    ago(value) {
        const seconds = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 1000));
        return seconds < 10 ? 'À l’instant' : `Il y a ${seconds} s`;
    }

    duration(value) {
        const minutes = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 60000));
        return minutes < 1 ? 'Moins d’1 min' : `${minutes} min`;
    }

    deviceLabel(device) {
        return { desktop: 'Ordinateur', mobile: 'Mobile', tablet: 'Tablette' }[device] || device;
    }
}
