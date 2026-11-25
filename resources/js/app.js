import './bootstrap';

const filledFieldSelector = 'input:not([type="hidden"]):not([type="submit"]):not([type="reset"]):not([type="button"]):not([type="image"]):not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]), select, textarea';

const syncFilledFieldState = (field) => {
    if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
        return;
    }

    if (!field.matches(filledFieldSelector)) {
        return;
    }

    const hasValue = field instanceof HTMLInputElement && field.type === 'file'
        ? field.files.length > 0
        : field.value.trim().length > 0;

    if (hasValue) {
        field.dataset.uiFilled = 'true';
    } else {
        delete field.dataset.uiFilled;
    }
};

document.querySelectorAll(filledFieldSelector).forEach(syncFilledFieldState);
document.addEventListener('input', (event) => syncFilledFieldState(event.target));
document.addEventListener('change', (event) => syncFilledFieldState(event.target));
document.addEventListener('reset', (event) => {
    const form = event.target;

    if (form instanceof HTMLFormElement) {
        queueMicrotask(() => form.querySelectorAll(filledFieldSelector).forEach(syncFilledFieldState));
    }
});

document.addEventListener('click', async (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }

    const confirmControl = target.closest('[data-confirm]:not(form)');
    if (confirmControl instanceof HTMLElement && confirmControl.dataset.confirm) {
        if (!window.confirm(confirmControl.dataset.confirm)) {
            event.preventDefault();
            return;
        }
    }

    const printButton = target.closest('[data-print-trigger]');
    if (printButton) {
        window.print();
        return;
    }

    const demoButton = target.closest('.auth-demo-actions [data-email]');
    if (demoButton instanceof HTMLElement && demoButton.dataset.email) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        if (emailInput instanceof HTMLInputElement) {
            emailInput.value = demoButton.dataset.email;
            syncFilledFieldState(emailInput);
        }
        if (passwordInput instanceof HTMLInputElement) {
            passwordInput.value = 'password';
            syncFilledFieldState(passwordInput);
        }
        emailInput?.focus();
        return;
    }

    let copyTimeout = null;
    const copyButton = target.closest('#copyBankAccount[data-copy-account]');
    if (copyButton instanceof HTMLElement) {
        const status = document.getElementById('copyBankAccountStatus');
        const btnText = document.getElementById('copyButtonText') || copyButton;
        const icon = document.getElementById('copyIcon');
        const account = copyButton.dataset.copyAccount ?? '';
        try {
            await navigator.clipboard.writeText(account);
            if (status) {
                status.textContent = 'Nomor rekening berhasil disalin.';
            }
            copyButton.classList.add('copied');
            btnText.textContent = 'Tersalin ✓';
            if (icon) icon.className = 'bi bi-check2 me-1';

            if (copyTimeout) clearTimeout(copyTimeout);
            copyTimeout = setTimeout(() => {
                copyButton.classList.remove('copied');
                btnText.textContent = 'Salin';
                if (icon) icon.className = 'bi bi-copy me-1';
            }, 2000);
        } catch {
            if (status) {
                status.textContent = 'Nomor rekening tidak dapat disalin otomatis. Silakan salin secara manual.';
            }
        }
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }

    if (form.dataset.uiSubmitting === 'true') {
        event.preventDefault();
        return;
    }

    const activeElement = document.activeElement;
    const candidate = event.submitter instanceof HTMLElement
        ? event.submitter
        : (activeElement instanceof HTMLElement && form.contains(activeElement) ? activeElement : null);
    const submitter = candidate?.matches('button:not([type]), button[type="submit"], input[type="submit"], input[type="image"]')
        ? candidate
        : null;

    form.dataset.uiSubmitting = 'true';
    form.setAttribute('aria-busy', 'true');
    form.classList.add('ui-form-submitting');

    if (submitter) {
        submitter.classList.add('ui-submit-is-loading');
        submitter.setAttribute('aria-disabled', 'true');
    }

    if (!form.querySelector('.ui-submit-status')) {
        const status = document.createElement('span');
        status.className = 'visually-hidden ui-submit-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.textContent = 'Permintaan sedang diproses. Mohon tunggu.';
        form.append(status);
    }
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form.ui-form-submitting').forEach((form) => {
        delete form.dataset.uiSubmitting;
        form.removeAttribute('aria-busy');
        form.classList.remove('ui-form-submitting');
        form.querySelectorAll('.ui-submit-is-loading').forEach((submitter) => {
            submitter.classList.remove('ui-submit-is-loading');
            submitter.removeAttribute('aria-disabled');
        });
        form.querySelector('.ui-submit-status')?.remove();
    });
});

const initProductDetailPricing = () => {
    const quantityInput = document.getElementById('qtyInput');
    const displayPrice = document.getElementById('displayPrice');
    if (!quantityInput && !displayPrice) {
        return;
    }

    const minus = document.getElementById('btnMinus');
    const plus = document.getElementById('btnPlus');
    const displayUnit = document.getElementById('displayUnit');
    const displayTotal = document.getElementById('displayTotal');
    const sizeOptions = document.querySelectorAll('input[name="size_ml"][type="radio"]');
    const fallbackSize = document.querySelector('input[name="size_ml"][type="hidden"]');
    const formatter = new Intl.NumberFormat('id-ID');

    const updatePricing = () => {
        const selected = document.querySelector('input[name="size_ml"]:checked') || fallbackSize;
        const unitPrice = selected instanceof HTMLElement ? Number(selected.dataset.price) || 0 : 0;
        const rawQuantity = quantityInput instanceof HTMLInputElement ? Number.parseInt(quantityInput.value, 10) : 1;
        const quantity = Number.isFinite(rawQuantity) ? Math.max(1, Math.min(50, rawQuantity)) : 0;

        if (displayPrice) {
            displayPrice.textContent = 'Rp ' + formatter.format(unitPrice);
        }
        if (displayUnit && selected instanceof HTMLInputElement) {
            displayUnit.textContent = '/ ' + selected.value + ' ml';
        }
        if (displayTotal) {
            displayTotal.textContent = quantity ? 'Rp ' + formatter.format(unitPrice * quantity) : '—';
        }
    };

    if (quantityInput instanceof HTMLInputElement) {
        quantityInput.addEventListener('input', updatePricing);
        quantityInput.addEventListener('change', () => {
            const parsed = Number.parseInt(quantityInput.value, 10);
            quantityInput.value = String(Number.isFinite(parsed) ? Math.max(1, Math.min(50, parsed)) : 1);
            syncFilledFieldState(quantityInput);
            updatePricing();
        });
    }

    if (minus && quantityInput instanceof HTMLInputElement) {
        minus.addEventListener('click', () => {
            const current = Number.parseInt(quantityInput.value, 10) || 1;
            quantityInput.value = String(Math.max(1, current - 1));
            syncFilledFieldState(quantityInput);
            updatePricing();
        });
    }

    if (plus && quantityInput instanceof HTMLInputElement) {
        plus.addEventListener('click', () => {
            const current = Number.parseInt(quantityInput.value, 10) || 1;
            quantityInput.value = String(Math.min(50, current + 1));
            syncFilledFieldState(quantityInput);
            updatePricing();
        });
    }

    sizeOptions.forEach((option) => option.addEventListener('change', updatePricing));
};

const initPaymentProofPreview = () => {
    const paymentFileInput = document.getElementById('proof_file');
    const dropzone = document.getElementById('paymentDropzone');
    if (!(paymentFileInput instanceof HTMLInputElement)) {
        return;
    }

    let previewObjectUrl = null;

    const formatBytes = (bytes) => {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    };

    const handleFileChange = () => {
        const file = paymentFileInput.files?.[0];
        const preview = document.getElementById('customerPaymentPreview');
        const image = document.getElementById('customerPaymentPreviewImage');
        const pdfIcon = document.getElementById('customerPaymentPreviewPdf');
        const name = document.getElementById('customerPaymentPreviewName');
        const sizeEl = document.getElementById('customerPaymentPreviewSize');

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        if (!file || !preview) {
            if (preview) {
                preview.hidden = true;
            }
            if (image instanceof HTMLImageElement) {
                image.removeAttribute('src');
                image.hidden = true;
            }
            if (pdfIcon) {
                pdfIcon.hidden = true;
            }
            return;
        }

        preview.hidden = false;
        if (name) name.textContent = file.name;
        if (sizeEl) sizeEl.textContent = formatBytes(file.size);

        if (file.type.startsWith('image/') && image instanceof HTMLImageElement) {
            previewObjectUrl = URL.createObjectURL(file);
            image.src = previewObjectUrl;
            image.hidden = false;
            if (pdfIcon) pdfIcon.hidden = true;
        } else {
            if (image instanceof HTMLImageElement) {
                image.removeAttribute('src');
                image.hidden = true;
            }
            if (pdfIcon) pdfIcon.hidden = false;
        }
    };

    paymentFileInput.addEventListener('change', handleFileChange);

    // Drag & drop handlers on dropzone
    if (dropzone instanceof HTMLElement) {
        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                paymentFileInput.files = files;
                handleFileChange();
                paymentFileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        dropzone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                paymentFileInput.click();
            }
        });
    }

    const removeBtn = document.getElementById('removePaymentPreview');
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            paymentFileInput.value = '';
            handleFileChange();
            paymentFileInput.click();
        });
    }
};

const initRefundRequirementToggle = () => {
    const approveCancellation = document.getElementById('decisionApprove');
    const rejectCancellation = document.getElementById('decisionReject');
    if (!approveCancellation && !rejectCancellation) {
        return;
    }

    const paidRefundFields = ['refund_amount', 'refund_reference', 'refund_proof']
        .map((id) => document.getElementById(id))
        .filter((field) => field instanceof HTMLInputElement);

    const syncRefundRequirement = () => {
        const isApproved = Boolean(approveCancellation instanceof HTMLInputElement && approveCancellation.checked);
        paidRefundFields.forEach((field) => {
            field.required = isApproved;
        });
    };

    approveCancellation?.addEventListener('change', syncRefundRequirement);
    rejectCancellation?.addEventListener('change', syncRefundRequirement);
    syncRefundRequirement();
};

const initSmartBackNavigation = () => {
    const STACK_KEY = 'uyuy_nav_stack';
    const GOING_BACK_KEY = 'uyuy_nav_going_back';
    const isRoot = document.body?.dataset.navRoot === 'true';
    const isAuth = document.body?.dataset.navAuth === 'true';
    const currentUrl = window.location.pathname + window.location.search;

    const isBlockedForAuth = (url) => {
        if (!isAuth || !url) return false;
        const path = url.split('?')[0];
        return path === '/login' || path === '/register' || path === '/forgot-password' || path.startsWith('/reset-password');
    };

    const normalizePath = (url) => {
        if (!url) return '';
        try {
            const parsed = new URL(url, window.location.origin);
            return parsed.pathname + parsed.search;
        } catch {
            return url;
        }
    };

    const readStack = () => {
        try {
            const raw = sessionStorage.getItem(STACK_KEY);
            const arr = raw ? JSON.parse(raw) : [];
            if (!Array.isArray(arr)) return [];
            return arr.filter((item) => typeof item === 'string' && item.length > 0 && !isBlockedForAuth(item));
        } catch {
            return [];
        }
    };

    const writeStack = (arr) => {
        try {
            sessionStorage.setItem(STACK_KEY, JSON.stringify(arr.slice(-30)));
        } catch {}
    };

    let stack = readStack();
    let goingBack = false;
    try {
        goingBack = sessionStorage.getItem(GOING_BACK_KEY) === '1';
        sessionStorage.removeItem(GOING_BACK_KEY);
    } catch {}

    if (isRoot) {
        stack = [currentUrl];
    } else if (goingBack) {
        const backIdx = stack.lastIndexOf(currentUrl);
        stack = backIdx !== -1 ? stack.slice(0, backIdx + 1) : [...stack, currentUrl];
    } else {
        const existingIdx = stack.lastIndexOf(currentUrl);
        stack = existingIdx !== -1 ? stack.slice(0, existingIdx + 1) : [...stack, currentUrl];
    }
    writeStack(stack);

    const resolveTarget = (fallbackAttr) => {
        const currentStack = readStack();
        for (let i = currentStack.length - 1; i >= 0; i--) {
            if (currentStack[i] !== currentUrl && !isBlockedForAuth(currentStack[i])) {
                return { url: currentStack[i], stackIndex: i };
            }
        }
        const fallbackPath = normalizePath(fallbackAttr);
        if (fallbackPath && fallbackPath !== currentUrl && !isBlockedForAuth(fallbackPath)) {
            return { url: fallbackPath, stackIndex: -1 };
        }
        return null;
    };

    document.querySelectorAll('.editorial-back-btn').forEach((btn) => {
        if (!(btn instanceof HTMLButtonElement)) return;
        const fallback = btn.dataset.fallbackUrl || '';
        const target = resolveTarget(fallback);

        if (isRoot || !target) {
            const wrapper = btn.closest('.editorial-back-wrapper');
            if (wrapper instanceof HTMLElement) {
                wrapper.hidden = true;
            } else {
                btn.hidden = true;
            }
            return;
        }

        btn.addEventListener('click', () => {
            const latestTarget = resolveTarget(fallback);
            if (!latestTarget) return;
            if (latestTarget.stackIndex >= 0) {
                writeStack(readStack().slice(0, latestTarget.stackIndex + 1));
            }
            try {
                sessionStorage.setItem(GOING_BACK_KEY, '1');
            } catch {}
            window.location.href = latestTarget.url;
        });
    });
};

const initRealTimeCartStepper = () => {
    const forms = document.querySelectorAll('.customer-cart-stepper-form');
    if (!forms.length) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const formatIdr = (amount) => 'Rp ' + Number(amount).toLocaleString('id-ID');

    const recalcUI = () => {
        let grandTotal = 0;
        let totalBottles = 0;

        document.querySelectorAll('.customer-cart-item[data-unit-price]').forEach((itemEl) => {
            const unitPrice = parseInt(itemEl.dataset.unitPrice || '0', 10) || 0;
            const input = itemEl.querySelector('.customer-cart-stepper-input');
            const minus = itemEl.querySelector('[data-cart-step="minus"]');
            const plus = itemEl.querySelector('[data-cart-step="plus"]');
            const subtotalEl = itemEl.querySelector('[data-cart-item-subtotal]');

            let qty = parseInt(input instanceof HTMLInputElement ? input.value : '1', 10) || 1;
            qty = Math.max(1, Math.min(50, qty));

            const itemSubtotal = unitPrice * qty;
            grandTotal += itemSubtotal;
            totalBottles += qty;

            if (subtotalEl) subtotalEl.textContent = formatIdr(itemSubtotal);
            if (minus instanceof HTMLButtonElement) minus.disabled = qty <= 1;
            if (plus instanceof HTMLButtonElement) plus.disabled = qty >= 50;
        });

        document.querySelectorAll('[data-cart-grand-total]').forEach((el) => {
            el.textContent = formatIdr(grandTotal);
        });
        document.querySelectorAll('[data-cart-total-bottles]').forEach((el) => {
            el.textContent = String(totalBottles);
        });
        const navBadge = document.getElementById('navbarCartBadge');
        if (navBadge) navBadge.textContent = String(totalBottles);
    };

    const syncToServer = async (form, qty) => {
        if (!window.fetch || !csrfToken) {
            form.submit();
            return;
        }

        const payload = new URLSearchParams();
        payload.append('_method', 'PUT');
        payload.append('_token', csrfToken);
        payload.append('quantity', String(qty));

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: payload.toString(),
            });
            if (!res.ok) throw new Error('Update failed');
            const data = await res.json();
            if (data && data.checkout_token) {
                const tokenInput = document.querySelector('input[name="checkout_token"]');
                if (tokenInput instanceof HTMLInputElement) {
                    tokenInput.value = data.checkout_token;
                }
            }
        } catch {
            form.submit();
        }
    };

    forms.forEach((form) => {
        if (!(form instanceof HTMLFormElement)) return;
        const input = form.querySelector('.customer-cart-stepper-input');
        const minus = form.querySelector('[data-cart-step="minus"]');
        const plus = form.querySelector('[data-cart-step="plus"]');
        if (!(input instanceof HTMLInputElement)) return;

        minus?.addEventListener('click', () => {
            const current = parseInt(input.value, 10) || 1;
            if (current > 1) {
                input.value = String(current - 1);
                recalcUI();
                syncToServer(form, current - 1);
            }
        });

        plus?.addEventListener('click', () => {
            const current = parseInt(input.value, 10) || 1;
            if (current < 50) {
                input.value = String(current + 1);
                recalcUI();
                syncToServer(form, current + 1);
            }
        });

        input.addEventListener('input', recalcUI);
        input.addEventListener('change', () => {
            const clamped = Math.max(1, Math.min(50, parseInt(input.value, 10) || 1));
            input.value = String(clamped);
            recalcUI();
            syncToServer(form, clamped);
        });
    });
};

const initCopyTextButtons = () => {
    document.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;
        const btn = target.closest('[data-copy-text]');
        if (!(btn instanceof HTMLElement)) return;

        const text = btn.dataset.copyText || '';
        if (!text) return;

        try {
            await navigator.clipboard.writeText(text);
            const original = btn.dataset.originalLabel || btn.textContent || 'Salin';
            btn.dataset.originalLabel = original;
            btn.classList.add('copied');
            btn.textContent = 'Tersalin ✓';
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.textContent = original;
            }, 2000);
        } catch {}
    });
};

const initPoCountdown = () => {
    const nodes = document.querySelectorAll('[data-po-countdown][data-po-end]');
    if (!nodes.length) return;

    const updateAll = () => {
        const now = Date.now();
        nodes.forEach((node) => {
            if (!(node instanceof HTMLElement)) return;
            const endTs = Date.parse(node.dataset.poEnd || '');
            if (Number.isNaN(endTs)) return;
            const diff = endTs - now;
            if (diff <= 0) {
                node.textContent = 'Batas pemesanan telah berakhir';
                return;
            }
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const parts = [];
            if (days > 0) parts.push(`${days} hari`);
            parts.push(`${hours} jam`);
            if (days === 0) parts.push(`${mins} menit`);
            node.textContent = `Ditutup dalam ${parts.join(' ')}`;
        });
    };

    updateAll();
    window.setInterval(updateAll, 30000);
};

const initPageBehaviors = () => {
    document.querySelectorAll(filledFieldSelector).forEach(syncFilledFieldState);
    initProductDetailPricing();
    initPaymentProofPreview();
    initRefundRequirementToggle();
    initSmartBackNavigation();
    initRealTimeCartStepper();
    initCopyTextButtons();
    initPoCountdown();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageBehaviors);
} else {
    initPageBehaviors();
}
