import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['bar', 'progress'];

    connect() {
        this.barTargets.forEach((bar) => {
            bar.closest('.admin-bar-column')?.style.setProperty('--bar-height', `${bar.dataset.height}%`);
        });
        this.progressTargets.forEach((progress) => {
            progress.style.setProperty('--progress-width', `${progress.dataset.width}%`);
        });
    }
}
