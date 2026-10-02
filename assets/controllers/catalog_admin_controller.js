/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';
import * as pdfjs from 'pdfjs-dist';

export default class extends Controller {
    static targets = [
        'pdfInput',
        'pdfPageCounts',
        'pdfFileCount',
        'pdfStatus',
        'pdfSubmit',
        'pdfSelection',
        'pdfPreview',
        'pageGrid',
        'pageCard',
        'reorderStatus',
        'contentsList',
        'contentsEntry',
        'contentsStatus',
    ];

    static values = {
        workerUrl: String,
        reorderUrl: String,
        reorderToken: String,
        contentsReorderUrl: String,
        contentsReorderToken: String,
    };

    connect() {
        pdfjs.GlobalWorkerOptions.workerSrc = this.workerUrlValue;
        this.pdfDocuments = new Map();
        this.inspectionId = 0;
        this.renderExistingPdfPreviews();
    }

    disconnect() {
        this.inspectionId += 1;
        this.previewObserver?.disconnect();
        for (const entry of this.pdfDocuments.values()) {
            entry.loadingTask.destroy();
        }
        this.pdfDocuments.clear();
    }

    async inspectPdf() {
        const files = Array.from(this.pdfInputTarget.files);
        const inspectionId = ++this.inspectionId;
        this.pdfPageCountsTarget.value = '';
        this.pdfFileCountTarget.value = String(files.length);
        this.pdfSelectionTarget.replaceChildren();
        this.pdfSelectionTarget.hidden = files.length === 0;

        if (files.length === 0) {
            this.pdfStatusTarget.textContent = 'Les documents seront analysés avant l’import.';
            return;
        }
        if (files.length > 50) {
            this.pdfStatusTarget.textContent = 'Sélectionnez au maximum 50 PDF à la fois.';
            this.pdfSubmitTarget.disabled = true;
            return;
        }

        this.pdfSubmitTarget.disabled = true;
        this.pdfStatusTarget.textContent = `Analyse de ${files.length} PDF…`;
        const pageCounts = Array(files.length).fill(0);
        const rows = files.map((file, index) => this.createPdfSelectionRow(file, index));
        let completed = 0;
        let cursor = 0;

        const analyzeNext = async () => {
            while (cursor < files.length) {
                const index = cursor++;
                const file = files[index];
                const row = rows[index];
                let loadingTask;

                try {
                    loadingTask = pdfjs.getDocument({ data: await file.arrayBuffer() });
                    const pdfDocument = await loadingTask.promise;
                    if (inspectionId !== this.inspectionId) {
                        return;
                    }

                    pageCounts[index] = pdfDocument.numPages;
                    await this.renderPdfPage(pdfDocument, 1, row.canvas, 150);
                    row.detail.textContent = `${pdfDocument.numPages} page${pdfDocument.numPages > 1 ? 's' : ''}`;
                    row.element.classList.add('is-ready');
                } catch {
                    row.detail.textContent = 'Analyse navigateur impossible · vérification à l’import';
                    row.element.classList.add('has-error');
                } finally {
                    if (loadingTask) {
                        try {
                            await loadingTask.destroy();
                        } catch {
                        }
                    }
                    ++completed;
                    if (inspectionId === this.inspectionId) {
                        this.pdfStatusTarget.textContent = `${completed} PDF analysé${completed > 1 ? 's' : ''} sur ${files.length}…`;
                    }
                }
            }
        };

        await Promise.all(Array.from({ length: Math.min(3, files.length) }, () => analyzeNext()));
        if (inspectionId !== this.inspectionId) {
            return;
        }

        const totalPages = pageCounts.reduce((total, count) => total + count, 0);
        this.pdfPageCountsTarget.value = JSON.stringify(pageCounts);
        this.pdfStatusTarget.textContent = `${files.length} PDF sélectionné${files.length > 1 ? 's' : ''} · ${totalPages || 'nombre de'} page${totalPages > 1 ? 's' : ''} détectée${totalPages > 1 ? 's' : ''}.`;
        this.pdfSubmitTarget.disabled = false;
    }

    createPdfSelectionRow(file, index) {
        const element = document.createElement('article');
        const canvas = document.createElement('canvas');
        const copy = document.createElement('span');
        const name = document.createElement('strong');
        const detail = document.createElement('small');
        const order = document.createElement('i');

        element.className = 'admin-catalog-pdf-item';
        name.textContent = file.name;
        detail.textContent = 'Analyse…';
        order.textContent = String(index + 1).padStart(2, '0');
        copy.append(name, detail);
        element.append(canvas, copy, order);
        this.pdfSelectionTarget.append(element);

        return { element, canvas, detail };
    }

    renderExistingPdfPreviews() {
        if (!this.hasPdfPreviewTarget) {
            return;
        }

        if ('IntersectionObserver' in window) {
            this.previewObserver = new IntersectionObserver((entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) {
                        continue;
                    }
                    this.previewObserver.unobserve(entry.target);
                    this.renderExistingPdfPreview(entry.target);
                }
            }, { rootMargin: '320px 0px' });
            this.pdfPreviewTargets.forEach((canvas) => this.previewObserver.observe(canvas));
            return;
        }

        this.pdfPreviewTargets.forEach((canvas) => this.renderExistingPdfPreview(canvas));
    }

    async renderExistingPdfPreview(canvas) {
        try {
            const source = canvas.dataset.pdfSource;
            const pdfDocument = await this.loadPdfDocument(source);
            await this.renderPdfPage(pdfDocument, Number(canvas.dataset.pdfPage) || 1, canvas, 620);
            canvas.classList.add('is-ready');
        } catch {
            canvas.classList.add('has-error');
        }
    }

    loadPdfDocument(source) {
        if (!this.pdfDocuments.has(source)) {
            const loadingTask = pdfjs.getDocument({ url: source });
            this.pdfDocuments.set(source, { loadingTask, promise: loadingTask.promise });
        }

        return this.pdfDocuments.get(source).promise;
    }

    async renderPdfPage(pdfDocument, pageNumber, canvas, maximumCssWidth) {
        const page = await pdfDocument.getPage(pageNumber);
        const initialViewport = page.getViewport({ scale: 1 });
        const availableWidth = canvas.parentElement?.clientWidth || maximumCssWidth;
        const cssWidth = Math.max(80, Math.min(maximumCssWidth, availableWidth));
        const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
        const viewport = page.getViewport({ scale: (cssWidth / initialViewport.width) * pixelRatio });
        const context = canvas.getContext('2d', { alpha: false });

        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);
        canvas.style.aspectRatio = `${initialViewport.width} / ${initialViewport.height}`;
        await page.render({ canvasContext: context, viewport }).promise;
        page.cleanup();
    }

    startContentsDrag(event) {
        if (this.isSavingContentsOrder) {
            event.preventDefault();
            return;
        }

        this.draggedContentsEntry = event.currentTarget.closest('[data-contents-key]');
        if (!this.draggedContentsEntry) {
            event.preventDefault();
            return;
        }

        this.originalContentsOrder = this.currentContentsKeys();
        this.draggedContentsEntry.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', this.draggedContentsEntry.dataset.contentsKey);
    }

    overContents(event) {
        if (!this.draggedContentsEntry) {
            return;
        }

        event.preventDefault();
        const target = event.currentTarget;
        if (target === this.draggedContentsEntry) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const after = event.clientY > rect.top + (rect.height / 2)
            || (Math.abs(event.clientY - (rect.top + rect.height / 2)) < rect.height / 3 && event.clientX > rect.left + (rect.width / 2));
        this.contentsListTarget.insertBefore(this.draggedContentsEntry, after ? target.nextSibling : target);
        this.updateContentsPositions();
    }

    dropContents(event) {
        event.preventDefault();
    }

    async finishContentsDrag() {
        if (!this.draggedContentsEntry) {
            return;
        }

        this.draggedContentsEntry.classList.remove('is-dragging');
        this.draggedContentsEntry = null;
        if (this.originalContentsOrder.join(',') === this.currentContentsKeys().join(',')) {
            return;
        }

        this.isSavingContentsOrder = true;
        this.contentsStatusTarget.textContent = 'Enregistrement du nouvel ordre…';
        const body = new URLSearchParams();
        body.set('_token', this.contentsReorderTokenValue);
        this.currentContentsKeys().forEach((key) => body.append('contentsKeys[]', key));

        try {
            const response = await fetch(this.contentsReorderUrlValue, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
            });
            if (!response.ok) {
                throw new Error('Unable to save contents order');
            }
            this.contentsStatusTarget.textContent = 'Ordre du sommaire enregistré.';
        } catch {
            const entriesByKey = new Map(this.contentsEntryTargets.map((entry) => [entry.dataset.contentsKey, entry]));
            this.originalContentsOrder.forEach((key) => this.contentsListTarget.append(entriesByKey.get(key)));
            this.updateContentsPositions();
            this.contentsStatusTarget.textContent = 'Ordre non enregistré · réessayez.';
        } finally {
            this.isSavingContentsOrder = false;
        }
    }

    currentContentsKeys() {
        return Array.from(this.contentsListTarget.querySelectorAll('[data-contents-key]'), (entry) => entry.dataset.contentsKey);
    }

    updateContentsPositions() {
        this.contentsListTarget.querySelectorAll('[data-contents-key]').forEach((entry, index) => {
            const badge = entry.querySelector(':scope > i');
            if (badge) {
                badge.textContent = String(index + 1).padStart(2, '0');
            }
        });
    }

    startPageDrag(event) {
        if (this.isSavingOrder) {
            event.preventDefault();
            return;
        }

        this.draggedCard = event.currentTarget.closest('[data-page-id]');
        if (!this.draggedCard) {
            event.preventDefault();
            return;
        }

        this.originalOrder = this.currentPageIds();
        this.draggedCard.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', this.draggedCard.dataset.pageId);
    }

    overPage(event) {
        if (!this.draggedCard) {
            return;
        }

        event.preventDefault();
        const target = event.currentTarget;
        if (target === this.draggedCard) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const after = event.clientY > rect.top + (rect.height / 2)
            || (Math.abs(event.clientY - (rect.top + rect.height / 2)) < rect.height / 3 && event.clientX > rect.left + (rect.width / 2));
        this.pageGridTarget.insertBefore(this.draggedCard, after ? target.nextSibling : target);
        this.updateDisplayedPositions();
    }

    dropPage(event) {
        event.preventDefault();
    }

    async finishPageDrag() {
        if (!this.draggedCard) {
            return;
        }

        this.draggedCard.classList.remove('is-dragging');
        this.draggedCard = null;
        if (this.originalOrder.join(',') === this.currentPageIds().join(',')) {
            return;
        }

        this.isSavingOrder = true;
        this.reorderStatusTarget.textContent = 'Enregistrement du nouvel ordre…';
        const body = new URLSearchParams();
        body.set('_token', this.reorderTokenValue);
        this.currentPageIds().forEach((id) => body.append('pageIds[]', id));

        try {
            const response = await fetch(this.reorderUrlValue, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
            });
            if (!response.ok) {
                throw new Error('Unable to save page order');
            }
            this.reorderStatusTarget.textContent = 'Ordre enregistré';
        } catch {
            const cardsById = new Map(this.pageCardTargets.map((card) => [card.dataset.pageId, card]));
            this.originalOrder.forEach((id) => this.pageGridTarget.append(cardsById.get(id)));
            this.updateDisplayedPositions();
            this.reorderStatusTarget.textContent = 'Ordre non enregistré · réessayez';
        } finally {
            this.isSavingOrder = false;
        }
    }

    currentPageIds() {
        return Array.from(this.pageGridTarget.querySelectorAll('[data-page-id]'), (card) => card.dataset.pageId);
    }

    updateDisplayedPositions() {
        const cards = Array.from(this.pageGridTarget.querySelectorAll('[data-page-id]'));
        cards.forEach((card, index) => {
            const badge = card.querySelector('.admin-catalog-page-preview > i');
            if (badge) {
                badge.textContent = String(index + 1).padStart(2, '0');
            }
            const movementButtons = card.querySelectorAll('footer > div form button');
            if (movementButtons[0]) {
                movementButtons[0].disabled = index === 0;
            }
            if (movementButtons[1]) {
                movementButtons[1].disabled = index === cards.length - 1;
            }
        });
    }
}
