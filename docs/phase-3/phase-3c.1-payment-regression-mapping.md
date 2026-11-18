# Phase 3C.1: Payment Regression Mapping Report
**Project:** Sistem Informasi Pengelolaan Pre-Order Parfum Berbasis Web pada Uyuy Jastip  
**Author:** Payment Security Specialist & Lead QA Engineer  
**Date:** 14 November 2026  
**Status:** 100% Covered & Verified (14/14 Scenarios Passing)  
**Primary Test Class:** `Tests\Feature\CustomerPaymentWorkflowTest`  

---

## 1. Executive Summary

This document provides a line-by-line reconciliation mapping between the **14 canonical Phase 3B Payment Workflow Scenarios** and the active automated test suite in the Uyuy Jastip codebase.

During Phase 3C, an editorial documentation error created the appearance that the payment test suite had been replaced with generic tests. This audit confirms that **all 14 original Phase 3B scenarios remain fully implemented, completely active, and 100% passing** inside [tests/Feature/CustomerPaymentWorkflowTest.php](file:///e:/Codex/tests/Feature/CustomerPaymentWorkflowTest.php).

---

## 2. Comprehensive 14-Scenario Mapping Matrix

| Scenario ID | Phase 3B Scenario Description | Current Test Method in Codebase | Assertions & Key Checks | Coverage Status | Test Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **PAY-01** | Customer can open a valid payment instruction page | `CustomerPaymentWorkflowTest::test_pay_01_customer_opens_valid_payment_page` | `assertOk()`, `assertSee('Instruksi transfer')`, `assertSee(config('jastip.bca_account'))`, `assertSee('Batas pembayaran')`, `assertSee('name="proof_file"')` | **Complete** | **PASS** |
| **PAY-02** | Expired payment deadline is strictly enforced | `CustomerPaymentWorkflowTest::test_pay_02_customer_opens_expired_payment` | Sets `$order->payment_deadline_at` to past; `assertRedirect(route('customer.orders.show'))`, `assertSessionHas('error')`, asserts error contains `'Batas pembayaran telah lewat'` | **Complete** | **PASS** |
| **PAY-03** | Valid JPEG/PNG payment proof upload | `CustomerPaymentWorkflowTest::test_pay_03_customer_uploads_valid_image_proof` | `Storage::fake('local')`, uploads fake JPEG; asserts redirected with success, file stored in private disk, `PaymentAttempt` created with `attempt_number: 1` and `STATUS_PENDING` | **Complete** | **PASS** |
| **PAY-04** | Valid PDF payment proof upload | `CustomerPaymentWorkflowTest::test_pay_04_customer_uploads_valid_pdf_proof` | `Storage::fake('local')`, uploads fake PDF; asserts redirected with success, file stored in private disk with `.pdf` extension | **Complete** | **PASS** |
| **PAY-05** | Invalid MIME type is rejected | `CustomerPaymentWorkflowTest::test_pay_05_customer_uploads_invalid_mime` | Uploads executable payload (`.exe`); `assertSessionHasErrors(['proof_file'])`, asserts `proof_file_path` remains `null` | **Complete** | **PASS** |
| **PAY-06** | Oversized proof file is rejected (> 2MB) | `CustomerPaymentWorkflowTest::test_pay_06_customer_uploads_oversized_file` | Uploads 3000 KB file (limit 2048 KB); `assertSessionHasErrors(['proof_file'])`, asserts `proof_file_path` remains `null` | **Complete** | **PASS** |
| **PAY-07** | Incorrect payment amount is rejected | `CustomerPaymentWorkflowTest::test_pay_07_customer_submits_incorrect_amount` | Submits Rp 100.000 for order total Rp 120.000; `assertSessionHasErrors(['amount'])`, asserts `proof_file_path` remains `null` | **Complete** | **PASS** |
| **PAY-08** | Client-side amount tampering is rejected (server authoritative) | `CustomerPaymentWorkflowTest::test_pay_08_customer_tampers_with_amount_server_side_authoritative` | Attacker manipulates POST body to submit Rp 1.000 for order Rp 150.000; `assertSessionHasErrors(['amount'])`, verifies `$order->total_amount` unchanged (150000), `$payment->amount` unchanged (150000) | **Complete** | **PASS** |
| **PAY-09** | Re-upload after Admin rejection preserves history | `CustomerPaymentWorkflowTest::test_pay_09_customer_reuploads_rejected_proof` | Uploads attempt #1 -> Admin rejects with notes -> uploads attempt #2; asserts `payment_attempts` table has 2 records, attempt #1 is `REJECTED`, attempt #2 is `PENDING`, payment status reset to `PENDING` | **Complete** | **PASS** |
| **PAY-10** | Customer cannot access another customer's payment (GET & POST) | `CustomerPaymentWorkflowTest::test_pay_10_customer_accesses_another_customers_payment` | User B attempts GET on User A's payment -> `assertForbidden()` (403); User B attempts POST payment upload for User A's order -> `assertForbidden()` (403) | **Complete** | **PASS** |
| **PAY-11** | Customer cannot access another customer's proof files (IDOR) | `CustomerPaymentWorkflowTest::test_pay_11_customer_accesses_another_customers_proof` | User B attempts GET on User A's proof route -> `assertForbidden()`; User A accesses their own proof -> `assertOk()` (200) | **Complete** | **PASS** |
| **PAY-12** | Admin can access the payment verification queue | `CustomerPaymentWorkflowTest::test_pay_12_admin_opens_payment_verification_queue` | Admin accesses `admin.payments.index` with pending filter -> `assertOk()`, sees order number; Admin accesses `admin.payments.verify` -> `assertOk()`, sees order number & formatted amount | **Complete** | **PASS** |
| **PAY-13** | Uploaded proof remains strictly private | `CustomerPaymentWorkflowTest::test_pay_13_uploaded_proof_remains_private` | `Storage::fake('local')` and `Storage::fake('public')`; verifies file exists on local disk, file is **missing from public disk**, authorized route responds with `X-Content-Type-Options: nosniff` and `Cache-Control: no-store` | **Complete** | **PASS** |
| **PAY-14** | Upload does not automatically approve payment | `CustomerPaymentWorkflowTest::test_pay_14_upload_does_not_automatically_verify_payment` | Customer uploads proof; verifies `$order->status` remains `WAITING_PAYMENT` (not approved), `$payment->verification_status` remains `PENDING` (not valid), `verified_by` and `verified_at` remain `null` | **Complete** | **PASS** |

---

## 3. Deep-Dive Security & Integrity Verification

### 3.1 Expired Payment Deadline Enforcement (PAY-02)
- **Mechanism:** In `app/Http/Controllers/Customer/OrderController.php`, both `payment($id)` and `uploadPayment(Request $request, $id)` invoke `$order->paymentDeadlinePassed()`.
- **Concurrency Protection:** Inside `uploadPayment`, a database row-lock is acquired (`Order::lockForUpdate()`) and `$lockedOrder->paymentDeadlinePassed()` is re-checked inside the transaction. If the deadline expires while the user is uploading, the upload is rejected.
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_02_customer_opens_expired_payment` passed. Live browser test on `/pelanggan/pesanan/11/pembayaran` redirected to `/pelanggan/pesanan/11` with flash error: `Batas pembayaran telah lewat...`. Screenshot: [08-expired-payment-redirection.png](file:///e:/Codex/docs/phase-3/phase-3c.1-evidence/08-expired-payment-redirection.png).

### 3.2 Amount Tampering & Machine-Readable Input (PAY-08)
- **Mechanism:** The payment form includes a machine-readable field: `<input type="hidden" id="amount" name="amount" value="120000.00">`.
- **Backend Defense:** `uploadPayment` validates the submitted amount:
  ```php
  if (!Money::matches($validated['amount'], $order->total_amount)) {
      return back()->withErrors(['amount' => 'Nominal transfer harus sama dengan total tagihan Rp ' . number_format((float)$order->total_amount, 0, ',', '.') . '.'])->withInput();
  }
  ```
  Even if the request passes validation, the amount persisted into `PaymentAttempt` and `Payment` is `$lockedOrder->total_amount` (authoritative database value).
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_08_customer_tampers_with_amount_server_side_authoritative` passed.

### 3.3 Re-upload & Audit History Preservation (PAY-09)
- **Mechanism:** When a proof is rejected by Admin, `payment_attempts` retains the rejected record with `verification_status = REJECTED` and `admin_notes`. When the customer re-uploads, a new row with `attempt_number = 2` is inserted with `verification_status = PENDING`.
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_09_customer_reuploads_rejected_proof` passed with database record count assertion (`$this->assertDatabaseCount('payment_attempts', 2)`).

### 3.4 Cross-Customer Access & IDOR Protection (PAY-10, PAY-11)
- **Mechanism:** Controlled by Laravel Policies (`OrderPolicy::interact` and `PaymentProofController` authorization). A customer cannot view another customer's payment instructions, submit payment on another customer's order, or view another customer's uploaded receipts.
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_10` and `test_pay_11` both passed, asserting HTTP 403 Forbidden on unauthorized access attempts.

### 3.5 Private Storage Isolation & Security Headers (PAY-13)
- **Mechanism:** Payment proofs are uploaded to the `local` private disk (`storage/app/private/payments/...`). The file is never placed in `public/` or `storage/app/public/`. Download/viewing is mediated exclusively through `PaymentProofController` which enforces:
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Cache-Control: no-store, private`
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_13_uploaded_proof_remains_private` passed.

### 3.6 No Automatic Verification (PAY-14)
- **Mechanism:** Submitting a proof changes `verification_status` to `menunggu_verifikasi` (PENDING). The order status remains `menunggu_pembayaran` (WAITING_PAYMENT) until an Admin explicitly performs a manual verification in the Admin panel.
- **Test Evidence:** `CustomerPaymentWorkflowTest::test_pay_14_upload_does_not_automatically_verify_payment` passed.

---

## 4. Execution Command & Output Reference

Executing the dedicated payment feature test suite:

```bash
php artisan test --filter=CustomerPaymentWorkflowTest
```

Output:
```text
   PASS  Tests\Feature\CustomerPaymentWorkflowTest
  ✓ pay 01 customer opens valid payment page                                  0.08s  
  ✓ pay 02 customer opens expired payment                                     0.05s  
  ✓ pay 03 customer uploads valid image proof                                 0.06s  
  ✓ pay 04 customer uploads valid pdf proof                                   0.15s  
  ✓ pay 05 customer uploads invalid mime                                      0.05s  
  ✓ pay 06 customer uploads oversized file                                    0.05s  
  ✓ pay 07 customer submits incorrect amount                                  0.04s  
  ✓ pay 08 customer tampers with amount server side authoritative             0.06s  
  ✓ pay 09 customer reuploads rejected proof                                  0.17s  
  ✓ pay 10 customer accesses another customers payment                        0.07s  
  ✓ pay 11 customer accesses another customers proof                          0.06s  
  ✓ pay 12 admin opens payment verification queue                             0.15s  
  ✓ pay 13 uploaded proof remains private                                     0.11s  
  ✓ pay 14 upload does not automatically verify payment                       0.04s  

  Tests:    14 passed (69 assertions)
  Duration: 1.15s
```

---

## 5. Conclusion

There are **zero coverage gaps** in the payment regression suite. All 14 scenarios originally defined in Phase 3B remain active, authoritative, and passing in the active project codebase.
