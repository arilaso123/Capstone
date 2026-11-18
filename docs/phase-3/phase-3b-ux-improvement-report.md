# Phase 3B: Customer Payment UX Evaluation & Improvement Report
**Project:** Uyuy Jastip — Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web  
**Role:** Senior UX Researcher, Product Designer & Frontend Validation Engineer  
**Scope:** Usability, Information Architecture, Component Hierarchy, Accessibility, and Mobile UX of `/pelanggan/pesanan/{id}/pembayaran`  
**Design System:** Editorial Stone v2  
**Date:** 2026-11-14

---

## 1. User Cognitive Clarity: The 5 Essential Questions

A customer navigating to `/pelanggan/pesanan/{id}/pembayaran` must immediately and effortlessly answer five primary questions without ambiguity:

| Question | Current Interface Experience | Evaluation Rating | Findings & Observations |
| :--- | :--- | :--- | :--- |
| **1. Berapa nominal yang harus ditransfer?** | Displayed in two locations: (1) Inside the "TRANSFER BANK" summary as `Jumlah tagihan: Rp 120.000`, and (2) Inside the upload form as a readonly field `Jumlah yang ditransfer (Rp): 120000,00`. | **EXCELLENT** | Clear, bold, formatted in Indonesian rupiah (`Rp 120.000`). Pre-populated in form so user does not need to retype. |
| **2. Ke rekening BCA mana pembayaran dilakukan?** | Rendered inside a dedicated stone card: Bank BCA, Account `8720-1928-33` with one-click "Salin" button, and Account Name `UYUY JASTIP PARFUM`. | **EXCELLENT** | Highly legible monospace/bold font for digits, one-click copy button, clean merchant naming. |
| **3. Sampai kapan pembayaran harus diselesaikan?** | Renders below bank details: `Batas pembayaran: 15 November 2026, 10:52 WIB. Pesanan yang belum dibayar akan diproses sesuai batas waktu 24 jam sejak dibuat.` | **GOOD** | Clear timestamp in WIB. However, visual treatment is plain text rather than an alert or highlighted pill. |
| **4. Bagaimana cara mengunggah bukti transfer?** | Renders in the right-hand panel with numbered steps ("Langkah berikutnya") and a standard `<input type="file">` supporting JPG, PNG, PDF up to 2 MB. | **GOOD** | File chooser is straightforward; supports image & PDF; has instant client filename preview. |
| **5. Apa yang terjadi setelah bukti berhasil diunggah?** | Explicitly stated in numbered step 3: *"Admin akan memeriksa bukti secara manual dan statusnya tampil di detail pesanan."* and confirmed in post-upload flash message. | **EXCELLENT** | Manages user expectations realistically; prevents customer panic by clarifying that verification is manual by Admin. |

---

## 2. Visual Hierarchy & Layout Architecture

### 2.1 Desktop & Laptop Viewports (1440px & 1366px)
- **Two-Column Composition:**
  - **Left Column (45%):** Transaction & Bank context (Account details, copy button, total bill, deadline, and "Langkah berikutnya" instructional list).
  - **Right Column (55%):** Form submission card (Payment method, readonly amount, file picker, file preview, privacy note, and action buttons).
- **Proportions:** Conforms to Editorial Stone's structured editorial grid. Ample breathing room between sections (`gap: 1.5rem` to `2rem`).
- **Evidence Reference:** [01-customer-payment-desktop-1440.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/01-customer-payment-desktop-1440.png) & [02-customer-payment-laptop-1366.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/02-customer-payment-laptop-1366.png).

### 2.2 Mobile & Mobile Compact Viewports (390px & 360px)
- **Natural Stacking:** On mobile screens, the layout drops seamlessly to a single column:
  1. Header & Status Badge ("Menunggu Pembayaran").
  2. Rekening Pembayaran Card (Bank, Account Number, Copy Button, Merchant Name).
  3. Order Amount & Deadline Notice.
  4. Form Unggah Pembayaran (Inputs, File Picker, Submit Button).
  5. Langkah Berikutnya (Instructions).
- **Touch Ergonomics:** The "Salin" button provides an adequate touch target (`min-height: 38px`), easily reachable with the thumb.
- **Evidence Reference:** [03-customer-payment-mobile-390.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/03-customer-payment-mobile-390.png) & [04-customer-payment-mobile-360.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/04-customer-payment-mobile-360.png).

---

## 3. Form Usability & Interaction Analysis

### 3.1 One-Click Account Number Copying
- **Current Behavior:** Clicking `#copyBankAccount` executes `navigator.clipboard.writeText('8720192833')`.
- **Feedback:** Updates `#copyBankAccountStatus` with:
  - Success: *"Nomor rekening berhasil disalin."*
  - Fallback: *"Nomor rekening tidak dapat disalin otomatis. Silakan salin secara manual."*
- **Accessibility:** Uses `role="status"` and `aria-live="polite"` so screen readers announce the copy result without focus jarring.
- **Evidence Reference:** [05-copy-account-feedback.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/05-copy-account-feedback.png).

### 3.2 Dynamic File Selection & Preview
- **Current Behavior:** When a user selects a file, JavaScript detects the event, releases prior object URLs (`URL.revokeObjectURL`), reveals `#customerPaymentPreview`, displays the filename, and renders a thumbnail if the file is an image.
- **PDF Handling:** If a PDF is selected, the thumbnail `<img>` is cleanly hidden (`hidden = true`), while the PDF filename remains clearly presented.
- **Evidence Reference:** [06-file-preview-feedback.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/06-file-preview-feedback.png).

### 3.3 Double-Submission Prevention
- Handled globally in `resources/js/app.js`: upon form submission, the button enters `ui-submit-is-loading` state, sets `aria-busy="true"`, and displays an accessible hidden status: *"Permintaan sedang diproses. Mohon tunggu."* This prevents accidental duplicate uploads.

---

## 4. Error Handling & Feedback Usability

### 4.1 Payment Deadline Expiration Alert
- **Scenario:** Customer attempts to access an expired order.
- **UX Flow:** Immediate redirect to `/pelanggan/pesanan/{id}` with an amber warning banner:
  > *"Batas pembayaran telah lewat. Pesanan ini tidak dapat menerima unggahan baru dan akan dibatalkan otomatis."*
- **Clarity:** 100% understandable; leaves no doubt why the upload form is unavailable.
- **Evidence Reference:** [07-expired-order-redirect-alert.png](file:///e:/Codex/docs/phase-3/phase-3b-test-evidence/07-expired-order-redirect-alert.png).

### 4.2 Form Validation Errors
- Inline validation messages appear directly underneath the offending input field (e.g. `#amount-error`, `#proof-file-error`).
- Inputs carry `aria-invalid="true"` and `aria-describedby` linking to the error text ID for assistive technology compliance.

---

## 5. UX Improvement Recommendations & Backlog

The current implementation is functional, stable, and visually coherent. The following minor, high-impact polish items are recommended for consideration in subsequent UX milestones (Phase 3 Milestone 2):

### Item UX-REC-01: Deadline Visual Emphasis (Countdown / Highlight Badge)
- **Current Observation:** The deadline text (`Batas pembayaran: 15 November 2026, 10:52 WIB`) is styled as body text with a `<strong>` tag inside the bank details card.
- **Recommendation:** Wrap the deadline in a subtle tinted badge (e.g. `bg-warning-subtle` or Editorial Stone warm amber pill) with a clock icon (`<i class="bi bi-clock-history"></i>`) so customers under tight time limits spot the deadline immediately.

### Item UX-REC-02: Amount Field Formatting in Input
- **Current Observation:** The input field shows `120000.00` in the HTML number input.
- **Recommendation:** Add a visual prefix helper `Rp` and format the display with Indonesian thousands separators (e.g. `Rp 120.000`), keeping the underlying numerical value in sync.

### Item UX-REC-03: Drag & Drop File Zone
- **Current Observation:** The file upload uses a standard native browser file input button (`Choose File`).
- **Recommendation:** Implement a dashed drag-and-drop dropzone with an upload cloud icon (`<i class="bi bi-cloud-arrow-up"></i>`), enabling desktop customers to drag receipts directly from WhatsApp Desktop or email downloads.

### Item UX-REC-04: Copy Account Button Visual State
- **Current Observation:** The copy feedback appears in text below the account card.
- **Recommendation:** Temporarily toggle the button text itself from "Salin" to "Tersalin ✓" with a 2-second timeout, providing instantaneous tactile confirmation.

---

## 6. Conclusion
The customer payment page fulfills all core usability, operational, and business objectives. Customers are provided with complete billing precision, secure private storage safeguards, and transparent expectations regarding manual admin verification.
