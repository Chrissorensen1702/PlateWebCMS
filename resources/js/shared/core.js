import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('sitePageEditor', (config = {}) => ({
        activeArea: String(config.activeArea ?? ''),
        areaIds: Array.isArray(config.areaIds) ? config.areaIds.map((id) => String(id)) : [],
        panelModal: config.initialPanelModal ? String(config.initialPanelModal) : null,
        activeLibraryCategory: String(config.initialLibraryCategory ?? ''),
        autosaveEnabled: Boolean(config.autosaveEnabled),
        autosaveUrl: String(config.autosaveUrl ?? ''),
        previewUrl: String(config.previewUrl ?? ''),
        previewPageMap: Array.isArray(config.previewPageMap) ? config.previewPageMap : [],
        autosaveDelay: Number(config.autosaveDelay ?? 700),
        autosaveState: Boolean(config.autosaveEnabled) ? 'saved' : 'idle',
        autosaveMessage: Boolean(config.autosaveEnabled) ? 'Alle ændringer er gemt' : '',
        autosaveTimer: null,
        autosaveQueued: false,
        autosaveInFlight: false,
        previewScrollPosition: { x: 0, y: 0 },
        draggingArea: null,
        dropTargetArea: null,
        dragPreviewElement: null,
        canReorder: Boolean(config.canReorder),

        init() {
            const hashArea = window.location.hash.replace('#area-', '');

            if (this.areaIds.includes(hashArea)) {
                this.activeArea = hashArea;
            }

            if (! this.areaIds.includes(this.activeArea)) {
                this.activeArea = this.areaIds[0] ?? '';
            }

            this.$watch('activeArea', (value) => {
                if (value) {
                    window.history.replaceState(null, '', '#area-' + value);
                }
            });

            this.$watch('panelModal', (value) => {
                document.documentElement.classList.toggle('site-editor-modal-open', Boolean(value));
            });

            if (this.panelModal) {
                document.documentElement.classList.add('site-editor-modal-open');
            }
        },

        openPanelModal(panel) {
            this.panelModal = String(panel);

            if (this.panelModal === 'library' && ! this.activeLibraryCategory) {
                this.activeLibraryCategory = String(config.initialLibraryCategory ?? '');
            }

            document.documentElement.classList.add('site-editor-modal-open');
        },

        closePanelModal() {
            this.panelModal = null;
            document.documentElement.classList.remove('site-editor-modal-open');
        },

        scheduleAutosave(event) {
            if (! this.autosaveEnabled) {
                return;
            }

            const target = event?.target;

            if (target instanceof HTMLInputElement && target.type === 'file') {
                this.autosaveState = 'saved';
                this.autosaveMessage = 'Billeder gemmes, når du klikker på gem';
                return;
            }

            window.clearTimeout(this.autosaveTimer);
            this.autosaveState = 'pending';
            this.autosaveMessage = 'Gemmer snart...';
            this.autosaveTimer = window.setTimeout(() => this.performAutosave(), this.autosaveDelay);
        },

        prepareManualSubmit() {
            window.clearTimeout(this.autosaveTimer);
            this.autosaveQueued = false;
            this.autosaveState = 'saving';
            this.autosaveMessage = 'Gemmer...';
        },

        async performAutosave() {
            if (! this.autosaveEnabled || ! this.$refs.draftForm) {
                return;
            }

            if (this.autosaveInFlight) {
                this.autosaveQueued = true;
                return;
            }

            this.autosaveInFlight = true;
            this.autosaveQueued = false;
            this.autosaveState = 'saving';
            this.autosaveMessage = 'Gemmer...';

            try {
                const formData = new FormData(this.$refs.draftForm.closest('form'));
                formData.set('return_to', 'design');

                const response = await fetch(this.autosaveUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    if (response.status === 422) {
                        this.autosaveState = 'error';
                        this.autosaveMessage = 'Kunne ikke gemme et felt endnu';
                        return;
                    }

                    throw new Error(`Autosave failed with status ${response.status}`);
                }

                const payload = await response.json();
                this.autosaveState = 'saved';
                this.autosaveMessage = payload.message ?? 'Alle ændringer er gemt';
                this.refreshPreview();
            } catch (error) {
                this.autosaveState = 'error';
                this.autosaveMessage = 'Autosave fejlede - brug Gem knappen';
            } finally {
                this.autosaveInFlight = false;

                if (this.autosaveQueued) {
                    this.performAutosave();
                }
            }
        },

        refreshPreview(force = false) {
            if (! this.$refs.previewFrame) {
                return;
            }

            this.capturePreviewScroll();

            const targetUrl = this.previewUrl || this.$refs.previewFrame.getAttribute('src') || '';

            if (! targetUrl) {
                return;
            }

            const previewUrl = new URL(targetUrl, window.location.origin);
            previewUrl.searchParams.set('_preview', String(Date.now()));
            this.$refs.previewFrame.src = previewUrl.toString();

            if (force) {
                this.autosaveMessage = 'Preview opdateret';
            }
        },

        capturePreviewScroll() {
            const previewWindow = this.$refs.previewFrame?.contentWindow;

            if (! previewWindow) {
                return;
            }

            this.previewScrollPosition = {
                x: previewWindow.scrollX ?? 0,
                y: previewWindow.scrollY ?? previewWindow.pageYOffset ?? 0,
            };
        },

        handlePreviewLoad() {
            this.bindPreviewInteractions();
            this.restorePreviewScroll();
        },

        restorePreviewScroll(attempt = 0) {
            const previewWindow = this.$refs.previewFrame?.contentWindow;

            if (! previewWindow) {
                return;
            }

            const { x, y } = this.previewScrollPosition ?? { x: 0, y: 0 };

            if (! x && ! y) {
                return;
            }

            previewWindow.scrollTo(x, y);

            if (attempt >= 4) {
                return;
            }

            window.requestAnimationFrame(() => {
                const currentY = previewWindow.scrollY ?? previewWindow.pageYOffset ?? 0;

                if (Math.abs(currentY - y) > 2) {
                    this.restorePreviewScroll(attempt + 1);
                }
            });
        },

        async deleteSection(sectionId, actionUrl, label = 'afsnit') {
            if (! window.confirm(`Vil du slette '${label}'?`)) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                    },
                    body: new URLSearchParams({
                        _method: 'DELETE',
                    }).toString(),
                });

                if (! response.ok) {
                    throw new Error(`Delete failed with status ${response.status}`);
                }

                const payload = await response.json();
                const targetId = String(sectionId);
                const nextAreaIds = this.areaIds.filter((id) => id !== targetId);

                this.areaIds = nextAreaIds;
                this.dropTargetArea = this.dropTargetArea === targetId ? null : this.dropTargetArea;

                if (this.activeArea === targetId) {
                    this.activeArea = payload.focus_section_id
                        ? String(payload.focus_section_id)
                        : (nextAreaIds[0] ?? '');
                }

                this.autosaveState = 'saved';
                this.autosaveMessage = payload.message ?? 'Afsnittet er fjernet';
                this.refreshPreview();
            } catch (error) {
                this.autosaveState = 'error';
                this.autosaveMessage = 'Kunne ikke slette afsnittet';
            }
        },

        bindPreviewInteractions() {
            if (! this.$refs.previewFrame?.contentDocument || ! Array.isArray(this.previewPageMap)) {
                return;
            }

            const previewDocument = this.$refs.previewFrame.contentDocument;
            const previewWindow = this.$refs.previewFrame.contentWindow;

            if (! previewWindow) {
                return;
            }

            previewDocument.querySelectorAll('a[href]').forEach((anchor) => {
                if (anchor.dataset.cmsPreviewBound === '1') {
                    return;
                }

                anchor.dataset.cmsPreviewBound = '1';

                anchor.addEventListener('click', (event) => {
                    const href = anchor.getAttribute('href');

                    if (! href) {
                        return;
                    }

                    let targetUrl;

                    try {
                        targetUrl = new URL(href, previewWindow.location.href);
                    } catch (error) {
                        return;
                    }

                    const editorUrl = this.editorUrlForPreviewTarget(targetUrl);

                    if (! editorUrl) {
                        return;
                    }

                    event.preventDefault();
                    window.location.assign(editorUrl);
                });
            });
        },

        editorUrlForPreviewTarget(targetUrl) {
            const targetPath = this.normalizePreviewPath(targetUrl.pathname);

            const match = this.previewPageMap.find((entry) => {
                try {
                    return this.normalizePreviewPath(new URL(entry.public_url, window.location.origin).pathname) === targetPath;
                } catch (error) {
                    return false;
                }
            });

            return match?.editor_url ?? null;
        },

        normalizePreviewPath(pathname) {
            if (! pathname || pathname === '/') {
                return '/';
            }

            return pathname.replace(/\/+$/, '') || '/';
        },

        startDragging(event, areaId) {
            if (! this.canReorder) {
                return;
            }

            this.draggingArea = String(areaId);
            this.dropTargetArea = String(areaId);
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(areaId));

            document.documentElement.classList.add('site-editor-is-dragging');

            const sourceElement = event.currentTarget;

            if (sourceElement instanceof HTMLElement) {
                const preview = sourceElement.cloneNode(true);
                preview.classList.add('site-editor-panel-nav__drag-preview');
                preview.style.width = `${sourceElement.offsetWidth}px`;
                preview.style.position = 'fixed';
                preview.style.top = '-9999px';
                preview.style.left = '-9999px';
                preview.style.pointerEvents = 'none';
                document.body.appendChild(preview);
                event.dataTransfer.setDragImage(preview, preview.offsetWidth / 2, 28);
                this.dragPreviewElement = preview;
            }
        },

        handleDragOver(event) {
            if (! this.draggingArea || ! this.$refs.sectionList) {
                return;
            }

            const draggedElement = this.$refs.sectionList.querySelector(`[data-section-id="${this.draggingArea}"]`);
            const targetElement = event.currentTarget;

            if (! draggedElement || ! targetElement || draggedElement === targetElement) {
                return;
            }

            this.dropTargetArea = targetElement.dataset.sectionId ?? null;

            const targetRect = targetElement.getBoundingClientRect();
            const axis = this.$refs.sectionList.dataset.reorderAxis ?? 'vertical';
            const insertAfter = axis === 'horizontal'
                ? event.clientX > targetRect.left + (targetRect.width / 2)
                : event.clientY > targetRect.top + (targetRect.height / 2);
            const nextElement = insertAfter ? targetElement.nextElementSibling : targetElement;

            if (nextElement !== draggedElement) {
                this.$refs.sectionList.insertBefore(draggedElement, nextElement);
            }
        },

        finishDragging() {
            if (! this.draggingArea || ! this.$refs.sectionList) {
                this.resetDraggingState();
                return;
            }

            const nextOrder = Array.from(this.$refs.sectionList.querySelectorAll('[data-section-id]'))
                .map((element) => element.dataset.sectionId);
            const hasChanged = JSON.stringify(nextOrder) !== JSON.stringify(this.areaIds);
            const focusArea = this.draggingArea;

            this.resetDraggingState();

            if (! hasChanged) {
                return;
            }

            this.areaIds = nextOrder;
            this.submitSectionOrder(nextOrder, focusArea);
        },

        submitSectionOrder(nextOrder, focusArea) {
            if (! this.$refs.sectionReorderForm || ! this.$refs.sectionReorderInputs) {
                return;
            }

            this.$refs.sectionReorderInputs.innerHTML = '';

            nextOrder.forEach((sectionId) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'section_ids[]';
                input.value = sectionId;
                this.$refs.sectionReorderInputs.appendChild(input);
            });

            this.$refs.sectionReorderFocus.value = focusArea;
            this.$refs.sectionReorderForm.submit();
        },

        resetDraggingState() {
            this.draggingArea = null;
            this.dropTargetArea = null;
            document.documentElement.classList.remove('site-editor-is-dragging');

            if (this.dragPreviewElement instanceof HTMLElement) {
                this.dragPreviewElement.remove();
            }

            this.dragPreviewElement = null;
        },
    }));
});

export function startAlpine() {
    Alpine.start();
}
