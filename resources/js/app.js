import './bootstrap';
import { initWordReveal } from './animations/wordReveal';

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

const initPageBehaviors = () => {
    document.querySelectorAll(filledFieldSelector).forEach(syncFilledFieldState);
    initProductDetailPricing();
    initPaymentProofPreview();
    initRefundRequirementToggle();
    initWordReveal();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageBehaviors);
} else {
    initPageBehaviors();
}
