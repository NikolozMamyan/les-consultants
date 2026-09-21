import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'sectionButton',
        'panel',
        'frame',
        'frameShell',
        'field',
        'device',
        'preview',
        'status',
        'zoomLabel',
        'outline',
        'outlineLabel',
        'carouselCardButton',
        'carouselCardPanel',
        'saveButton',
    ];
    static values = { saveUrl: String };

    connect() {
        const activeButton = this.sectionButtonTargets.find(button => button.classList.contains('is-active'));
        this.activeSection = activeButton?.dataset.sectionId;
        this.currentSectionSelector = activeButton?.dataset.sectionSelector;
        this.zoomLevels = [70, 85, 100, 115];
        this.zoom = 85;
        this.outlineUpdate = () => this.updateOutline();
    }

    disconnect() {
        this.detachPreviewListeners();
        this.outlineTimers?.forEach(timer => window.clearTimeout(timer));
    }

    selectSection(event) {
        const button = event.currentTarget;
        this.activateSection(button.dataset.sectionId, button.dataset.sectionSelector, button.querySelector('strong')?.textContent);
    }

    activateSection(sectionId, selector, label) {
        this.activeSection = sectionId;
        this.currentSectionSelector = selector;

        this.sectionButtonTargets.forEach(button => {
            const active = button.dataset.sectionId === sectionId;
            button.classList.toggle('is-active', active);
            button.toggleAttribute('aria-current', active);
        });

        this.panelTargets.forEach(panel => {
            panel.hidden = panel.dataset.sectionId !== sectionId;
        });

        this.outlineLabelTarget.textContent = label || 'Section sélectionnée';
        this.scrollPreviewTo(selector);

        const panel = this.panelTargets.find(item => item.dataset.sectionId === sectionId);
        if (panel?.querySelector('.admin-carousel-card-button.is-active')) {
            window.setTimeout(() => {
                const selectedCard = panel.querySelector('.admin-carousel-card-button.is-active');
                if (this.activeSection === sectionId && selectedCard) {
                    this.highlightCarouselCard(selectedCard, true);
                }
            }, 380);
        } else {
            this.clearCarouselCardHighlight();
        }
    }

    previewReady() {
        this.detachPreviewListeners();
        this.lockPreview();
        this.syncFieldsFromPreview();
        this.restoreCarouselPreviews();

        const previewWindow = this.frameTarget.contentWindow;
        previewWindow?.addEventListener('scroll', this.outlineUpdate, { passive: true });
        previewWindow?.addEventListener('resize', this.outlineUpdate);
        this.previewWindow = previewWindow;

        const activeButton = this.sectionButtonTargets.find(button => button.dataset.sectionId === this.activeSection);
        this.outlineLabelTarget.textContent = activeButton?.querySelector('strong')?.textContent || 'Section sélectionnée';
        window.setTimeout(() => this.scrollPreviewTo(this.currentSectionSelector), 350);
    }

    selectCarouselCard(event) {
        const button = event.currentTarget;
        const editor = button.closest('.admin-carousel-editor');
        if (!editor) {
            return;
        }

        editor.querySelectorAll('.admin-carousel-card-button').forEach(item => {
            const active = item.dataset.cardId === button.dataset.cardId;
            item.classList.toggle('is-active', active);
            item.toggleAttribute('aria-current', active);
        });
        editor.querySelectorAll('.admin-carousel-card-panel').forEach(panel => {
            panel.hidden = panel.dataset.cardId !== button.dataset.cardId;
        });

        this.highlightCarouselCard(button, true);
    }

    updateCarouselInterval(event) {
        const input = event.currentTarget;
        const editor = input.closest('.admin-carousel-editor');
        const output = input.closest('.admin-carousel-interval')?.querySelector('output');
        if (output) {
            output.textContent = `${(Number(input.value) / 1000).toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} s`;
        }

        this.applyCarouselSettings(editor);
        this.markDirty();
    }

    updateCarouselMode(event) {
        const editor = event.currentTarget.closest('.admin-carousel-editor');
        this.applyCarouselSettings(editor);
        this.markDirty();
        this.scheduleOutlineUpdate();
    }

    updateText(event) {
        const field = event.currentTarget;
        const element = this.previewElement(field.dataset.previewSelector);

        if (element) {
            this.replaceVisibleText(element, field.value);
        }

        this.markDirty();
        this.scheduleOutlineUpdate();
    }

    updateImage(event) {
        const field = event.currentTarget;
        const file = field.files?.[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', () => {
            const image = this.previewElement(field.dataset.previewSelector);
            if (image) {
                image.src = reader.result;
            }
            this.markDirty();
            this.scheduleOutlineUpdate();
        });
        reader.readAsDataURL(file);
    }

    viewport(event) {
        const size = event.currentTarget.dataset.size;
        this.previewTarget.dataset.size = size;
        this.deviceTargets.forEach(button => button.classList.toggle('is-active', button.dataset.size === size));
        this.scheduleOutlineUpdate();
    }

    zoomIn() {
        const index = Math.min(this.zoomLevels.length - 1, this.zoomLevels.indexOf(this.zoom) + 1);
        this.applyZoom(this.zoomLevels[index]);
    }

    zoomOut() {
        const index = Math.max(0, this.zoomLevels.indexOf(this.zoom) - 1);
        this.applyZoom(this.zoomLevels[index]);
    }

    applyZoom(zoom) {
        this.zoom = zoom;
        this.previewTarget.dataset.zoom = String(zoom);
        this.zoomLabelTarget.textContent = `${zoom} %`;
        this.scheduleOutlineUpdate();
    }

    reset(event) {
        event.preventDefault();
        this.element.querySelector('form')?.reset();
        this.frameTarget.contentWindow?.location.reload();
        this.statusTarget.innerHTML = '<i></i> Modifications annulées';
        this.statusTarget.classList.remove('is-dirty');
    }

    async save(event) {
        event.preventDefault();
        const form = this.element.querySelector('form');
        if (!form || !this.hasSaveUrlValue) {
            return;
        }

        const fields = {};
        const body = new FormData();
        this.fieldTargets.forEach(field => {
            if ('file' === field.type) {
                const file = field.files?.[0];
                if (file) {
                    body.append('imageSelectors[]', field.dataset.previewSelector);
                    body.append('images[]', file, file.name);
                }
                return;
            }
            fields[field.dataset.previewSelector] = field.value;
        });

        const carousels = {};
        this.element.querySelectorAll('.admin-carousel-editor').forEach(editor => {
            carousels[editor.dataset.carouselRootSelector] = {
                interval: Number(editor.querySelector('.admin-carousel-interval input')?.value),
                mode: editor.querySelector('.admin-carousel-mode-switch input')?.checked ? 'marquee' : 'cards',
            };
        });

        body.set('_token', form.elements.namedItem('_token')?.value || '');
        body.set('fields', JSON.stringify(fields));
        body.set('carousels', JSON.stringify(carousels));
        this.saveButtonTarget.disabled = true;
        this.statusTarget.innerHTML = '<i></i> Publication en cours…';

        try {
            const response = await fetch(this.saveUrlValue, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body,
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(result.message || 'Impossible d’enregistrer les modifications.');
            }

            this.commitFormState(result.content || {});
            this.statusTarget.innerHTML = '<i></i> Modifications publiées';
            this.statusTarget.classList.remove('is-dirty');
            this.frameTarget.contentWindow?.location.reload();
            window.dispatchEvent(new CustomEvent('admin:notice', { detail: { message: result.message } }));
        } catch (error) {
            this.statusTarget.innerHTML = '<i></i> Échec de la publication';
            this.statusTarget.classList.add('is-dirty');
            window.dispatchEvent(new CustomEvent('admin:notice', {
                detail: { message: error instanceof Error ? error.message : 'Impossible d’enregistrer les modifications.' },
            }));
        } finally {
            this.saveButtonTarget.disabled = false;
        }
    }

    markDirty() {
        this.statusTarget.innerHTML = '<i></i> Modifications non publiées';
        this.statusTarget.classList.add('is-dirty');
    }

    lockPreview() {
        const document = this.frameTarget.contentDocument;
        if (!document) {
            return;
        }

        document.documentElement.classList.add('admin-editor-preview');

        const preventInteraction = event => {
            if (event.type === 'click' || event.type === 'submit') {
                event.preventDefault();
            }
            event.stopImmediatePropagation();
        };

        document.addEventListener('click', preventInteraction, true);
        document.addEventListener('submit', preventInteraction, true);
        document.addEventListener('keydown', event => {
            if (['Enter', ' '].includes(event.key)) {
                preventInteraction(event);
            }
        }, true);

        document.querySelectorAll('a, button, input, select, textarea, summary').forEach(element => {
            element.setAttribute('tabindex', '-1');
            element.setAttribute('aria-disabled', 'true');
        });
    }

    syncFieldsFromPreview() {
        this.fieldTargets.forEach(field => {
            const element = this.previewElement(field.dataset.previewSelector);
            if (!element) {
                return;
            }

            if ('file' === field.type) {
                const filename = element.getAttribute('src')?.split('/').pop();
                const label = field.previousElementSibling?.querySelector('strong');
                if (filename && label) {
                    label.textContent = filename;
                }
                return;
            }

            field.value = element.textContent.replace(/\s+/g, ' ').trim();
        });
    }

    restoreCarouselPreviews() {
        this.element.querySelectorAll('.admin-carousel-editor').forEach(editor => {
            this.applyCarouselSettings(editor);
        });

        const activePanel = this.panelTargets.find(panel => !panel.hidden);
        const activeCard = activePanel?.querySelector('.admin-carousel-card-button.is-active');
        if (activeCard) {
            this.highlightCarouselCard(activeCard, false);
        }
    }

    applyCarouselSettings(editor) {
        if (!editor) {
            return;
        }

        const carousel = this.previewElement(editor.dataset.carouselRootSelector);
        const input = editor.querySelector('.admin-carousel-interval input');
        const modeInput = editor.querySelector('.admin-carousel-mode-switch input');
        if (!carousel || !input || !modeInput) {
            return;
        }

        const interval = Number(input.value);
        const marquee = modeInput.checked;
        carousel.dataset.carouselIntervalValue = String(interval);
        carousel.dataset.carouselModeValue = marquee ? 'marquee' : 'cards';
    }

    commitFormState(content) {
        this.fieldTargets.forEach(field => {
            if ('file' === field.type) {
                if (field.files?.[0]) {
                    const savedPath = content[field.dataset.previewSelector];
                    const label = field.previousElementSibling?.querySelector('strong');
                    if (savedPath && label) {
                        label.textContent = savedPath.split('/').pop();
                    }
                    field.value = '';
                }
                return;
            }
            field.defaultValue = field.value;
        });

        this.element.querySelectorAll('.admin-carousel-interval input').forEach(input => input.defaultValue = input.value);
        this.element.querySelectorAll('.admin-carousel-mode-switch input').forEach(input => input.defaultChecked = input.checked);
    }

    highlightCarouselCard(button, scroll) {
        const editor = button.closest('.admin-carousel-editor');
        const card = this.previewElement(button.dataset.cardSelector);
        this.clearCarouselCardHighlight();

        if (!editor || !card) {
            return;
        }

        card.dataset.adminCardSelected = 'true';
        const modeInput = editor.querySelector('.admin-carousel-mode-switch input');
        if (!scroll || modeInput?.checked) {
            return;
        }

        const carousel = this.previewElement(editor.dataset.carouselRootSelector);
        const viewport = carousel?.querySelector(editor.dataset.carouselViewportSelector);
        if (!viewport) {
            return;
        }

        const cardRect = card.getBoundingClientRect();
        const viewportRect = viewport.getBoundingClientRect();
        const left = cardRect.left - viewportRect.left + viewport.scrollLeft - (viewport.clientWidth - card.offsetWidth) / 2;
        viewport.scrollTo({ left, behavior: 'smooth' });
        this.scheduleOutlineUpdate();
    }

    clearCarouselCardHighlight() {
        this.frameTarget.contentDocument?.querySelectorAll('[data-admin-card-selected]').forEach(card => {
            card.removeAttribute('data-admin-card-selected');
        });
    }

    replaceVisibleText(element, value) {
        const walker = element.ownerDocument.createTreeWalker(element, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        let node = walker.nextNode();
        while (node) {
            if (node.textContent.trim()) {
                textNodes.push(node);
            }
            node = walker.nextNode();
        }

        if (textNodes.length > 0 && element.children.length > 0) {
            textNodes[0].textContent = `${value} `;
            textNodes.slice(1).forEach(item => item.textContent = '');
            return;
        }

        element.textContent = value;
    }

    scrollPreviewTo(selector) {
        const element = this.previewElement(selector);
        if (!element) {
            this.outlineTarget.hidden = true;
            return;
        }

        const previewWindow = this.frameTarget.contentWindow;
        const destination = element.getBoundingClientRect().top + (previewWindow?.scrollY || 0) - 70;
        previewWindow?.scrollTo({ top: Math.max(0, destination), behavior: 'smooth' });
        this.outlineTarget.hidden = false;
        this.scheduleOutlineUpdate();
    }

    scheduleOutlineUpdate() {
        this.outlineTimers?.forEach(timer => window.clearTimeout(timer));
        this.updateOutline();
        this.outlineTimers = [120, 300, 550, 850].map(delay => window.setTimeout(this.outlineUpdate, delay));
    }

    updateOutline() {
        const element = this.previewElement(this.currentSectionSelector);
        if (!element || !this.hasFrameShellTarget) {
            this.outlineTarget.hidden = true;
            return;
        }

        const elementRect = element.getBoundingClientRect();
        const iframeRect = this.frameTarget.getBoundingClientRect();
        const shellRect = this.frameShellTarget.getBoundingClientRect();
        const scale = iframeRect.width / this.frameTarget.offsetWidth;
        const left = iframeRect.left - shellRect.left + elementRect.left * scale;
        const top = iframeRect.top - shellRect.top + elementRect.top * scale;

        this.outlineTarget.style.setProperty('--outline-left', `${left}px`);
        this.outlineTarget.style.setProperty('--outline-top', `${top}px`);
        this.outlineTarget.style.setProperty('--outline-width', `${elementRect.width * scale}px`);
        this.outlineTarget.style.setProperty('--outline-height', `${elementRect.height * scale}px`);
        this.outlineTarget.hidden = false;
    }

    detachPreviewListeners() {
        this.previewWindow?.removeEventListener('scroll', this.outlineUpdate);
        this.previewWindow?.removeEventListener('resize', this.outlineUpdate);
        this.previewWindow = null;
    }

    previewElement(selector) {
        if (!selector) {
            return null;
        }

        try {
            return this.frameTarget.contentDocument?.querySelector(selector) || null;
        } catch {
            return null;
        }
    }
}
