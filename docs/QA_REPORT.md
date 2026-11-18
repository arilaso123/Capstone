# Interim QA Notes — Desktop Redesign

**Date:** 10 November 2026  
**Scope:** Current desktop storefront and role-interface redesign. Tablet and phone QA remain deferred at the user's request.

**QA status:** On hold until the user explicitly requests QA. The results below record earlier checks only; they do not validate the later shared form-submission, filled/valid field, flash-notice, loading, validation-error, disabled-control, or token-alignment changes. Do not treat this document as final acceptance.

## Results

| Check | Result | Evidence / limit |
|---|---|---|
| Vite production build | PASS | `npm run build`; current CSS/JS assets and manifest generated successfully. |
| Blade compilation | PASS | `php artisan view:clear` and `php artisan view:cache` completed after the latest view edits. |
| PHP syntax | PASS | `php -l` passed for 81 PHP files in `app/`, `routes/`, `database/`, `tests/`, and `config/`. Blade files were validated through the Blade cache compiler. |
| Route registration | PASS | `php artisan route:list --except-vendor` listed all 53 application routes. |
| Diff whitespace check | PASS | `git diff --check` returned exit code 0. Git printed CRLF-to-LF working-copy warnings only. |
| PHPUnit suite | BLOCKED BEFORE TEST DISCOVERY | `php artisan test` reports that PHPUnit cannot open `vendor/autoload.php`. The file exists and can be included, but PHP's `is_readable()` returned `false` for all checked workspace files under the current restricted execution environment. No latest test assertions were run. |
| Browser console | NOT CHECKED | The browser review did not inspect developer-console output. |

## Desktop browser review

The existing local application at `127.0.0.1:8000` rendered these guest-facing pages in Edge InPrivate:

- Homepage
- Catalogue with the current product cards and filters
- Product detail with product image, PO availability, variant price, subtotal, and quantity control
- PO information with current period dates, FAQ, and admin-service schedule
- Login page
- Registration page with customer fields and agreement checkbox
- Forgot-password page with the email reset form

The navigation completed and rendered each guest-facing page. Some route transitions took roughly 1–2 seconds before the new page appeared. Customer demo login attempts on the temporary QA server returned a 500 before authentication because those requests resolved an older missing SQLite path. No account was authenticated, and no customer, order, or payment data was changed.

The login page's demo account helper is now rendered only when the application environment is `local` or `testing`. Its credential-fill script has the same guard, so demo emails and the sample password are not emitted into production login HTML.

## Not covered

- Tablet and phone review is deferred per the user's instruction. Historical responsive results from the earlier baseline are not approval of this redesign.
- Customer, Admin, and Owner authenticated screens were not opened in this final pass. The isolated QA server's login POST used a stale missing SQLite path, so no dashboard was reached.
- Checkout, payment upload, cancellation/refund decisions, product/PO mutations, and other write flows were not submitted.
- Browser console errors and network requests were not inspected.
- Existing screenshots under `qa-screenshots/` are from the earlier baseline and should not be treated as captures of the final desktop redesign.

## Data and environment

No application database or `.env` file was changed during this QA pass. A newly created disposable SQLite QA database was migrated and seeded, then removed with the temporary QA server after its login request exposed the stale-path issue. No user data was written. Build output is in the repository's normal `public/build/` directory.

### Remaining QA actions

1. Run `php artisan test` in a PHP environment where the PHPUnit bootstrap is readable.
2. Review the latest design-token alignment, filled/valid field treatments, shared flash announcements, and form loading/duplicate-submit, validation associations, and disabled states, including browser-history restoration.
3. Diagnose why the isolated QA server resolves the stale SQLite path, then review authenticated customer, Admin, and Owner views using a dedicated disposable QA database.
4. Check browser console and network errors across the reviewed pages.
5. Run tablet and phone visual QA when the user is ready to include those screen sizes.
