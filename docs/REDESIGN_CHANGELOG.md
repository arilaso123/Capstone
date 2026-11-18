# Redesign Changelog

## 10 November 2026 — Editorial stone redesign

- Added `REDESIGN_AUDIT.md` with the existing route/view architecture, role inventory, design constraints, and risk notes.
- Added `DESIGN_SYSTEM.md` to document the adapted reference tokens, components, role patterns, and responsive targets.
- Replaced the previous ivory/champagne UI stylesheet with the Soot/Limestone/Chalk/Graphite system, neutral component states, sharp geometry, high-contrast focus, reduced-motion behavior, and a no-network font fallback.
- Updated public navigation to an edge-to-edge editorial header and simplified menu labels without changing route destinations or auth controls.
- Added explicit Admin and Owner workspace layout hooks; made their top bars, sidebars, responsive navigation, tables, and work areas distinct from the customer storefront.
- Standardized catalogue cards to square product imagery and a responsive 5/4/3/2-column grid. Existing model-backed data, variant prices, filters, detail links, and pagination remain in place.
- Tightened the hero heading scale after desktop review and removed legacy colored PO border accents that overrode the monochrome theme.
- Fixed the existing homepage smoke-test setup with `RefreshDatabase`; the initial baseline run passed 18 tests and 98 assertions (latest suite status is recorded in `QA_REPORT.md`).
- Kept application routes, controllers, models, schema, session behavior, and business rules out of scope.

## 10 November 2026 — Desktop interface continuation

- Rebuilt the storefront homepage around the current PO schedule, catalogue-backed products, and the real order steps; removed unsupported bestseller, customer-volume, duration, and payment-provider claims.
- Added a shared product-card Blade component and used it on the homepage and catalogue for consistent square imagery, variant sizes, product names, descriptions, and starting prices.
- Reworked catalogue search and filters with explicit labels, accurate search copy, visible checkout availability, and clearer empty results while preserving existing query routes.
- Rebuilt PO information around actual period dates and checkout eligibility, with accessible native FAQ disclosures and the existing manual-refund and payment-proof-retention policies.
- Reworked product detail around a square chalk image plinth with contain-fit imagery, clear PO availability, variant selection, and keyboard-visible selection states.
- Separated per-bottle pricing from the quantity subtotal and kept subtotal updates bounded to the existing 1–50 quantity control.
- Enforced the reference's single regular text weight across the shared type system so hierarchy comes from scale and tracking.
- Rebuilt the Owner PO recap as a period-first report with data-source context, accurate empty states, a compact metric grid, and print styling.
- Reworked the Owner customer directory to remove its hard-coded customer estimate, clarify filtered-result counts, and retain search, pagination, contact links, and order totals.
- Rebuilt the Owner dashboard around the existing aggregated figures, status distribution, active PO dates, and direct management links; clarified that an administratively open PO still follows its WIB date window for checkout.
- Unified Owner PO create/edit forms with clear date and status semantics while preserving the existing routes, validation, locked-date behavior, and checkout date rules.
- Reworked the Admin dashboard into task queues and a readable recent-order table, with neutral status labels and counts sourced from the existing controller.
- Rebuilt the customer cart and checkout form with a clear item review, retained update/remove/checkout routes, visible validation messages, bounded quantity controls, and a separate shipping-cost explanation.
- Reworked the customer dashboard and order history into account summaries, status filters, transaction rows, and factual empty states using existing controller data.
- Rebuilt customer order detail, payment upload, and cancellation screens around the actual order lifecycle, private proof links, payment deadline, manual review, and the Owner-approved manual-refund-before-cancellation rule.
- Reworked login and registration as focused customer forms; limited the demo account helper and its credential-fill script to local/testing environments, while preserving authentication routes and CSRF protection.
- Updated Admin cancellation queue and order detail surfaces, and Owner product list/edit surfaces with clearer task hierarchy while retaining current routes, fields, and controller workflows.
- Reworked the Admin order queue, payment queue, and cancellation decision screen with accurate search labels, status filters, query-preserving pagination, and visible refund review context.
- Applied the same form system to password recovery/reset and Owner product creation; retained CSRF tokens and all existing backend field names, and corrected the create/edit category choices to match.
- Refined shared public navigation, role sidebars, and footer with current-route cues, safer external links, configured social channels, and an automatically updating year.
- Gated the demo login helper by environment after desktop QA found that the hard-coded demo emails and sample password were otherwise present in production login markup.
- Added a shared native-form submission state across public and role layouts: a screen-reader announcement, inline spinner, duplicate-submit guard, and back/forward cache reset. Submit controls remain enabled in the form payload so existing action values and business workflows are preserved.
- Normalized validation, error, and disabled control states to the monochrome system, with explicit error copy and a bordered marker rather than red-only feedback.
- Aligned shared typography, content/workspace widths, focus rings, button transition timing, and product-image ratios with named design tokens; added a reduced-motion treatment for the form-loading indicator.
- Consolidated customer/Admin/Owner flash notices and validation summaries into one accessible Blade component, retaining the role-specific validation heading and applying consistent spacing.
- Marked status and button icons as decorative for assistive technology; their visible status labels and button text remain the accessible names.
- Added a subtle filled state for text-entry and selection controls, and normalized Bootstrap's valid-field indicator to the monochrome design system.
- Connected server validation messages to their relevant customer, Owner, and Admin controls with `aria-invalid` and conditional `aria-describedby`, avoiding references to error elements when no error is present.
- Mapped the shared theme tokens to the exact semantic names from the local reference CSS, applied the 96px section rhythm to large desktop storefront sections, and documented why conflicting extracted JSON type variants are not used.
- Added missing validation feedback and error-to-field associations for product variant cost fields, order status/shipping fields, and PO/product management controls; normalized custom checkout error markers and control radii.

## QA follow-up

QA is on hold until the user requests it. `QA_REPORT.md` records earlier desktop checks as historical context; those results do not verify the latest shared form states or the token alignment in this continuation. Tablet and phone review remains deferred per user direction. Screenshots under `qa-screenshots/` are historical baseline captures, not captures of the final redesign.

## 11 November 2026 — UI/UX design refinement, responsive breakpoints, and role contrast fixes

- Enabled Bootstrap 5 pagination styling globally via `Paginator::useBootstrapFive()` in `app/Providers/AppServiceProvider.php`.
- Fixed dark-surface contrast regressions across `.role-topbar` (Graphite for Admin, Soot for Owner), inverted mobile navbar toggler icons, dark-badge text, and dark-surface headings in the footer.
- Completed `<x-order-timeline>` geometry (5-column connected horizontal track on `>= 768px` switching cleanly to a connected vertical timeline on mobile `<= 767.98px`) and aligned cancelled state markup to Editorial Stone.
- Unified storefront/navbar container rails, added global horizontal containment (`overflow-x: clip` / `min-width: 0`), suppressed empty flash wrappers, and reframed the homepage hero photograph cleanly above `.home-period-card` without image overlap.
- Added `<x-customer-portal-nav />` across customer portal views (`dashboard`, `cart`, `orders.index`, `orders.show`, `orders.payment`, `orders.cancellation`) and highlighted priority operational queues on the Admin dashboard.
- Fixed mismatched `<header>`/`</div>` HTML in `admin/payments/index.blade.php`, upgraded legacy Admin/Owner detail, period, and product pages (`admin/orders/show`, `admin/cancellations/show`, `owner/po-periods/index`, `owner/products/{index,create,edit}`) onto unified Editorial Stone `.workspace-page-header` headers and `<x-status-badge>` components, and pruned unused legacy CSS rules.
- Implemented responsive CSS breakpoints across `1399.98px`, `1199.98px`, `991.98px`, `767.98px`, `575.98px`, and `359.98px` covering catalog grids, payment transfer instructions, cart items, admin order filters, owner recap filters, and compact single-row tablet/mobile workspace navigation.
- Added `DEVELOPMENT_PROGRESS.md` with implementation records, changed-file inventory, build/template verification output, preserved backend contracts, and manual QA handover instructions.

## 11 November 2026 — Corrective UI/UX Redesign (Editorial Stone v2)

Based on visual diagnosis of actual desktop screenshots (`1366×768` captures for `/`, `/admin/dashboard`, `/owner/dashboard`, and `/pelanggan/dashboard`), implemented an elevated **Editorial Stone v2** redesign across CSS tokens, layouts, shared sidebars, and role/customer views:

- **Warm Mineral Multi-Surface Hierarchy & Depth (`resources/css/uyuy-theme.css`):** Replaced flat grey-on-grey wireframe layering (`#d1d3cf` on `#e5e5e5`) with a clear surface stack (`--color-canvas: #e5e3dc`, `--color-surface: #faf9f6`, `--color-surface-subtle: #f1efe9`, `--color-surface-tint: #ebe8e0`), proportional radii (`6px` panels/cards, `5px` buttons/controls, `4px` badges/images), and restrained elevation shadows (`--shadow-card`, `--shadow-elevated`).
- **Multi-Weight Typographic Clarity & Tabular Figures:** Lifted the global `font-weight: 400 !important` override on headings and emphasized elements, restoring a crisp `400`/`500`/`600` weight scale alongside desktop scale tokens (`--text-table-head` `12px`, `--text-meta` `13px`, `--text-label` `14px`, `--text-nav-workspace` `14.5px`, `--text-body-sm` `15px`, `--text-body` `16px`, `--text-page-heading` & `--text-kpi` `32–38px`, hero `--text-heading` `44–58px`) and `tabular-nums` alignment.
- **Semantic Accents & Status-vs-Proof Separation:** Introduced controlled Warm Amber (`#8b5e34` / `#f8f2e8`) and Sage Stone (`#2e523c` / `#ebf2ed`) mineral tokens for Admin priority queues (`is-priority`), Customer payment-attention KPI cards (`is-attention`), priority alert banners, and distinct `.admin-payment-status` / `.customer-proof-status` pills (`.is-valid`, `.is-pending`, `.is-rejected`, `.is-empty`).
- **Workspace Shell & Sticky Sidebar Clipping Fix (`layouts/{admin,owner}.blade.php`, `components/sidebar-{admin,owner}.blade.php`):** Aligned topbars to `68px`, sized desktop `.role-sidebar` to `256px` with `position: sticky; top: 68px; height: calc(100vh - 68px); overflow-y: auto` (fixing the topbar overlapping the Owner profile card), restructured `.dashboard-sidebar` into a 3-zone flex column keeping Logout pinned cleanly at the bottom, and elevated active navigation links to high-contrast Graphite (`#2f2f2a`) with Chalk text.
- **Admin, Owner & Customer View Refinements (`admin/dashboard.blade.php`, `admin/orders/index.blade.php`, `admin/cancellations/show.blade.php`, `owner/dashboard.blade.php`, `owner/po-periods/index.blade.php`, `owner/products/index.blade.php`, `customer/dashboard.blade.php`, `customer/orders/index.blade.php`):**
  - Added workspace breadcrumbs (`<nav class="workspace-breadcrumb">`), structured KPI headers with icons and `Prioritas` pills on actionable non-zero queues, enhanced priority alert box with `.admin-priority-icon`, and upgraded Admin order action links to styled `btn-sm` controls.
  - Eliminated desktop table horizontal overflow (`width: 100%; min-width: 0;`), added clickable ellipsis order numbers (`max-width: 195px` with full `title` tooltips), enforced single-line `tabular-nums` currency/phone columns (`Rp` never splits across lines), normalized cancellation refund framing (`rounded`), and made Owner action groups flex-wrap cleanly.

## 11 November 2026 — Website-wide Editorial Stone typography scale, layout proportions & system error pages

- Elevated and standardized the global `:root` typographic hierarchy in `resources/css/uyuy-theme.css`: `--text-micro` (`12px` tracked uppercase eyebrows only), `--text-meta` (`13px` table column headers, hints, timestamps, legal bar & badges), `--text-label` / `--text-caption` (`14px` nav links, standard buttons, form labels, table rows, filter controls, card copy & footer links), `--text-body-sm` (`15px` form inputs & workspace tables), `--text-body` (`16px` editorial prose), and responsive heading tokens (`--text-card-title` through `--text-heading` `clamp(2.45rem, 4.2vw, 3.5rem)`).
- Eliminated all remaining sub-13px `0.62rem–0.78rem` CSS size overrides across the navbar, hero period card, product cards, footer, auth screens, customer checkout/orders/payment views, and Admin/Owner tables, forms, and sidebars; prevented Bootstrap `.small` from compounding text downward below `14px`.
- Deduplicated `.navbar-uyuy` into a single authoritative stylesheet section (`min-height: 74px`), aligned storefront containers to `--container-wide` (`1280px`) and role workspaces to `--container-workspace` (`1440px`) + `264px` desktop sidebars, tightened vertical hero padding above the fold, and standardized desktop product grids at 4 comfortable columns (`1200px+`) -> 3 (`<1200px`) -> 2 (`<=767.98px`) -> 1 (`<380px`).
- Normalized interactive hit-targets (`44px` `.btn`, `46px` inputs/selects/textareas, `50px` primary submit/hero CTAs, `36px` readable inline `.btn-sm`), upgraded topbar/sidebar/workspace header actions (`components/navbar.blade.php`, `components/input.blade.php`, `components/sidebar-{admin,owner}.blade.php`, `layouts/{admin,owner}.blade.php`, `admin/orders/show.blade.php`, `owner/po-periods/index.blade.php`, `owner/products/index.blade.php`).
- Added resilient, zero-database-dependency, CSP-compliant Editorial Stone HTTP system error templates under `resources/views/errors/{layout,403,404,419,500}.blade.php`.
- Recompiled production Vite assets (`npm run build`), verified Blade compilation (`php artisan view:cache`), confirmed zero sub-13px numeric `rem` declarations and zero inline scripts/handlers, and updated `DESIGN_SYSTEM.md`, `UI_UX_GUIDELINE.md`, and `DEVELOPMENT_PROGRESS.md`.


