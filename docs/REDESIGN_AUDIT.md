# UI/UX Redesign Audit — Uyuy Jastip

**Audit date:** 10 November 2026  
**Scope:** Current Laravel application in this workspace and all local design references: `references/DESIGN.md`, `references/uy-studio-variables.css`, and `references/uy-studio-tokens.json`. No screenshot files were present in `references/`.  
**Method:** Read-only source review before presentation changes; trace routes to controllers/views; inspect shared layouts, stylesheet, Vite entry points, current local assets and role navigation. The live [UY Studio site](https://www.uy-studio.com/) was also consulted for visual context on 10 November 2026. No database rows or `.env` values were inspected or changed for this redesign.

## Executive summary

The application is a server-rendered Laravel 12 storefront and operations system. It uses role-protected web routes, Eloquent-backed controllers, Blade views, Bootstrap 5.3, and Vite. Customer shopping, admin operations, and owner management already have distinct routes and page groups, but their shared visual layer was an ivory/champagne theme with rounded cards, shadows, inline accent colors, and a single generic content style. This did not match the supplied editorial stone reference or distinguish operational pages from the storefront.

The redesign keeps those three role journeys and their existing backend contracts. It replaces the shared palette, typography, surface treatment, navigation, catalogue geometry, and role shell styling. It uses the existing Uyuy Jastip identity and product data; it does not carry UY Studio naming, copy, product claims, or brand marks into the app.

## Architecture and integration map

| Layer | Current implementation | Redesign boundary |
|---|---|---|
| Runtime | Laravel 12 / PHP 8.2+, Composer-managed | No framework/dependency upgrade |
| Routing | `routes/web.php` groups public, guest/auth, customer, admin, owner routes; `routes/console.php` provides scheduled/console operations | No route or middleware contract change |
| Controllers | `app/Http/Controllers/{Customer,Admin,Owner}` plus public/auth controllers | No business logic/query changes for presentation work |
| Data | Eloquent models in `app/Models/`; MySQL/MariaDB migrations and existing order/payment/PO records | No schema, migration, or DB mutation |
| Views | Blade pages in `resources/views/pages`, `customer`, `admin`, `owner`, `auth`; shared `components` | Restyle via shared design system and layouts |
| Frontend | Bootstrap 5.3 CDN, Bootstrap Icons CDN, Vite `resources/css/app.css` and `resources/js/app.js` | Keep Bootstrap/Vite; remove remote font dependency and preserve existing JS behaviors |
| Assets | Uyuy logo variants and perfume hero in `public/images`; product photos use persisted image paths | Reuse assets; no invented product records or external UY Studio imagery |

## Route, role, and page inventory

| Role | Routes / capabilities | Blade pages |
|---|---|---|
| Public / guest | Home, catalogue search/filter/pagination, product detail, PO information, register/login/password reset | `pages/home`, `pages/catalog`, `pages/product-detail`, `pages/po-info`; `auth/*` |
| Customer | Dashboard; add/update/remove cart items; checkout; order history/detail; upload payment proof; request cancellation | `customer/dashboard`, `customer/cart`, `customer/orders/{index,show,payment,cancellation}` |
| Admin | Operations dashboard; order listing/detail/status update; payment review; cancellation review | `admin/dashboard`, `admin/orders/{index,show}`, `admin/payments/{index,verify}`, `admin/cancellations/{index,show}` |
| Owner | Executive dashboard; PO period create/edit/open/close; product create/edit/activate; recap; customer list; admin operations access | `owner/dashboard`, `owner/po-periods/{index,create,edit}`, `owner/products/{index,create,edit}`, `owner/recap/index`, `owner/customers/index`; shared admin pages |

### Existing role and business constraints to preserve

- Customer, admin, and owner routes are grouped behind `auth` and role middleware as declared in `routes/web.php`.
- Owner is allowed to access operational admin endpoints, while admin-only staff are not granted owner management routes.
- Controller-supplied models, totals, status counts, PO state, pricing, pagination, flash messages, validation errors, and uploaded proofs remain data-driven.
- Checkout availability depends on the active PO and its valid WIB date window; payment proof and refund proof visibility remains controller-authorized.
- Product photo URL/storage-path handling and product variant selection remain wired to current model data.

## Current visual and usability findings

1. `resources/css/uyuy-theme.css` previously defined a warm ivory/champagne/mocha theme, imported remote fonts, used rounded 12–20px cards, multiple shadows/gradients, and old catalogue image dimensions.
2. Public, admin, and owner layouts shared the same tonal language despite different tasks. Admin and owner sidebars and dashboard cards appeared as a consumer-facing page rather than an operational workspace.
3. Numerous views used inline colors, radii, and shadows; changing only top-level CSS tokens would leave inconsistent surfaces.
4. Catalogue markup already had dynamic filters, variants, image paths, descriptions, starting prices, and pagination. Its grid was Bootstrap 3/4 columns, while each product card reused generic card styling.
5. Storefront and panel navigation existed and role-specific links were present. Mobile layouts relied on Bootstrap collapse and table responsiveness rather than a separate mobile product experience.
6. Existing local hero and Uyuy logo assets are available. Product photos come from current product records and are not replaced with placeholder data.

## Reference interpretation and strategy

`DESIGN.md` describes a restrained, architectural editorial system: Soot `#24241f`, Limestone `#d1d3cf`, Chalk `#e5e5e5`, Graphite `#333333`; regular-weight sans typography; generous spacing; square surfaces; hairline borders; minimal motion; and an edge-to-edge five-column product grid. Its UY Studio name, product-letter system, product copy, and brand claims are reference content only and are not applied to Uyuy Jastip.

The local CSS variables repeat the same semantic type scale and identify a 96px section gap, 16px card padding, 13px element gap, and 3px input radius. `DESIGN.md` cautions that extracted type summaries are independent measurements and recommendations; the exported JSON contains conflicting repeated type variants (including a 48px entry with a 0.38 line height), so the named semantic values in `DESIGN.md` and `uy-studio-variables.css` are the implementation source. The live reference currently includes product groups beyond fragrance; only its visual language is relevant to this perfume-focused project.

Adaptation decisions: use an available system-font stack with GP/Inter fallbacks because GP files are not present; retain Uyuy name/logo and existing commerce claims; use the gallery-inspired palette on customer pages; give admin/owner a compact workspace with persistent role navigation, visible tables, status labels, and real metrics. On small screens, the product catalogue becomes two columns and operational tables remain horizontally scrollable rather than forcing a five-column grid.

## Scope, constraints, and risks

- Preserve routes, controller methods, auth/authorization, form names, CSRF, validation, Eloquent relationships, existing JS pricing/quantity behavior, and all PO/payment/cancellation rules.
- No migrations, DB writes, `.env` reads, package changes, or payment integration changes are part of this visual redesign.
- Existing CDN references for Bootstrap and Bootstrap Icons remain external dependencies; the new design stylesheet does not request web fonts.
- Blade contains many inline style fragments. The shared CSS overrides known legacy theme patterns; new components should prefer design tokens and classes over inline presentation styles.
- A full role-authenticated browser check may depend on the local database/server being available. QA status records only checks actually run and screenshots actually captured.

## Redesign implementation inventory

- Shared CSS tokens, typography, focus treatment, buttons, controls, cards, badges, tables, pagination, footer, and reduced-motion support.
- Public storefront navigation plus responsive catalogue grid (5 desktop / 4 laptop / 3 tablet / 2 mobile columns).
- Separate visual treatment for admin/owner top bar, sidebar, main work area, data tables, and status surfaces.
- Documentation and QA evidence in `DESIGN_SYSTEM.md`, `REDESIGN_CHANGELOG.md`, and `QA_REPORT.md`.
