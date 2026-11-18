# Uyuy Jastip Design System — Editorial Stone v2

Adapted from `references/DESIGN.md` and refined in **Editorial Stone v2** while preserving Uyuy Jastip's identity, Indonesian copy, existing data, and role workflows. Design tokens and component styles live in [`uyuy-theme.css`](file:///e:/Codex/resources/css/uyuy-theme.css) and compile through Vite (`resources/css/app.css`).

## Foundations

### Warm Mineral Surface & Accent Tokens

| Token | Value | Use |
|---|---:|---|
| Canvas (`--color-canvas` / `--color-limestone`) | `#e5e3dc` | Warm stone canvas behind workspace panels and storefront sections |
| Surface (`--color-surface` / `--color-chalk`) | `#faf9f6` | Crisp elevated content cards, KPI tiles, tables, navbar, and product panels |
| Subtle Surface (`--color-surface-subtle`) | `#f1efe9` | Secondary card bands, desktop sidebar column, table column headers, neutral icons |
| Tinted Surface (`--color-surface-tint`) | `#ebe8e0` | Footer/subtle bands and soft active highlights |
| Graphite (`--color-graphite`) | `#2f2f2a` | Primary buttons, Admin topbar, active sidebar menu background, service footer |
| Soot (`--color-soot`) | `#1e1e1a` | Primary typography, Owner topbar, high-contrast headings, `2px` keyboard focus ring |
| Muted Text (`--color-muted`) | `#52514b` | Labels, uppercase table headers, timestamps, breadcrumbs, supporting copy |
| Hairline / Strong Line | `rgba(30, 30, 26, .13)` / `.28` | Subtle card/table dividers and active form borders |
| Warm Amber Accent (`--color-accent-warm*`) | `#8b5e34` / ink `#6d4623` / bg `#f8f2e8` / border `#ddc6ad` | Pending payment proofs, awaiting verification, Admin priority KPI accent (`is-priority`), Customer attention tile (`is-attention`) |
| Sage Stone Accent (`--color-accent-sage*`) | `#2e523c` / bg `#ebf2ed` / border `#b6ccbe` | Open PO status (`Dibuka`), validated payment proofs (`Valid`), completed milestones |

### Shared Typography & Layout Token Reference

| Token | Value | Use |
|---|---:|---|
| `--font-gp` | `'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, ...` | Crisp sans-serif stack with zero network dependency |
| `--font-weight-regular` / `medium` / `semibold` | `400` / `500` / `600` | Restored multi-weight hierarchy for body (`400`), nav/labels (`500`), headings/KPI (`600`) |
| `--text-micro` / `--text-table-head` | `0.75rem` (`12px`) | Uppercase eyebrows (`.075em` tracking), uppercase table `<th>` headers, micro pills |
| `--text-meta` | `0.8125rem` (`13px`) | Breadcrumbs, timestamps, helper notes, verification hours, `.btn-sm` |
| `--text-label` / `--text-caption` | `0.875rem` (`14px`) | Standard buttons, form labels, nav links, table rows, card captions, badges |
| `--text-nav-workspace` | `0.90625rem` (`14.5px`) | Desktop Admin & Owner sidebar menu items |
| `--text-body-sm` | `0.9375rem` (`15px`) | Form inputs (`46px`), dashboard subtitles, hero secondary actions |
| `--text-body` | `1rem` (`16px`) | Editorial storefront copy, detail pages, FAQ answers, error copy |
| `--text-card-title` | `clamp(1.0625rem, 1.25vw, 1.125rem)` (`17px`–`18px`) | Panel/card headings and product card titles |
| `--text-subsection` | `clamp(1.3125rem, 1.65vw, 1.625rem)` (`21px`–`26px`) | Major panel H2 headings and PO schedule titles |
| `--text-subheading` | `clamp(1.45rem, 1.9vw, 1.75rem)` (`23px`–`28px`) | Editorial storefront section H2 headings |
| `--text-page-heading` / `--text-kpi` | `clamp(2rem, 2.55vw, 2.375rem)` (`32px`–`38px`) | Role & portal page H1; tabular KPI figures (`tabular-nums`) |
| `--text-heading` / `--text-display` | `clamp(2.75rem, 4.4vw, 3.625rem)` (`44px`–`58px`) | Homepage hero display heading (`line-height: 1.08`, `-0.022em` tracking) |
| `--container-focused` / `--container-wide` / `--container-workspace` | `880px` / `1280px` / `1480px` | Focused forms, public storefront/customer rail, Admin & Owner workspace rail |
| `--radius-cards` / `--radius-inputs` / `--radius-buttons` / `--radius-badge` / `--radius-images` | `6px` / `5px` / `5px` / `4px` / `4px` | Proportional modern Editorial Stone geometry |
| `--shadow-card` / `--shadow-elevated` | `0 1px 2px rgba(30,30,26,.04), 0 6px 18px rgba(30,30,26,.035)` / `0 2px 6px rgba(30,30,26,.06), 0 14px 32px rgba(30,30,26,.06)` | Soft physical mineral separation on `#faf9f6` surfaces |
| `--ratio-product` | `1 / 1` | Product imagery framing |
| `--duration-state` / `--loading-opacity` | `160ms` / `.78` | Smooth transitions and form submit states |

> Note: Typography preserves a crisp 3-weight hierarchy (`400`/`500`/`600`) without webfont downloads or CDN font dependencies. Currency totals, table counts, phone numbers, and KPI figures enforce `font-variant-numeric: tabular-nums`.

## Surface and component rules

- **Multi-level surface elevation:** Page canvas uses `#e5e3dc` (`--color-canvas`); elevated cards, tables, and KPI blocks use `#faf9f6` (`--color-surface`) with subtle border (`rgba(30, 30, 26, .13)`) and `--shadow-card`; sidebars and table headers use `#f1efe9` (`--color-surface-subtle`).
- **Interactive targets:** Standard `.btn` uses `min-height: 44px` (`var(--text-label)`); primary Hero CTA uses `min-height: 50px` (`var(--text-body-sm)`); compact table row `.btn-sm` uses `min-height: 36px` (`var(--text-meta)`); form controls (`<input>`, `<select>`, `.form-select`) use `#ffffff` fill, `min-height: 46px`, and `5px` radius.
- **Proof & Status clarity:** Order and PO status badges (`<x-status-badge />`, `4px` radius, `13px`) remain clearly distinct from payment verification proof pills (`.admin-payment-status` / `.customer-proof-status`) with subtle warm/sage/neutral semantic variants (`.is-valid`, `.is-pending`, `.is-rejected`, `.is-empty`).
- **Validation & Feedback:** Invalid controls display an accessible focus/outline border and link messages via `aria-invalid`/`aria-describedby`. Native submissions announce processing states cleanly via `.ui-submit-is-loading` inside `resources/js/app.js` without inline JS.
- **Shared Flash Notices:** Rendered through `components/flash-messages.blade.php`; empty containers output nothing.

## Role & Workspace Architecture

### Public & Customer Storefront
- Crisp `#faf9f6` header (`76px` min-height) on a `1280px` rail (`--container-wide`), paired with an editorial `1.08fr / .92fr` hero, `16/10` bottle showcase stacked above `.home-period-card`, and a responsive 4-column (`>= 1200px`) -> 3-column (`< 1200px`) -> 2-column (`<= 767.98px`) -> 1-column (`< 380px`) product grid.
- Customer portal includes `<x-customer-portal-nav />` (`Dashboard`, `Pesanan saya`, `Keranjang`, `Katalog parfum`), KPI cards with `.is-attention` highlighting unpaid orders, and non-wrapping tabular currency cells (`.customer-order-amount`).

### Admin & Owner Desktop Workspaces
- **Topbar (`.role-topbar`):** `68px` min-height (`Graphite` `#2f2f2a` for Admin, `Soot` `#1e1e1a` for Owner) fixed sticky at `top: 0` with `z-index: 1030`.
- **Sticky Sidebar (`.role-sidebar`):** `256px` desktop width with `position: sticky; top: 68px; height: calc(100vh - 68px); overflow-y: auto` preventing the topbar from overlapping `.sidebar-profile-card` during scroll. Features a 3-zone flex column (`sidebar-profile-card` -> `sidebar-nav-section` with dark Graphite `.sidebar-menu-link.active` -> bottom-docked `sidebar-footer`). Collapses cleanly into a scrollable single-row navigation strip at `<= 991.98px`.
- **Desktop Tables:** Full-width `min-width: 0; width: 100%;` within `1480px` workspace containers so `1366px` and `1440px` viewports avoid horizontal clipping; order numbers get controlled `max-width: 195px` ellipsis truncation with full `title` tooltips; currency totals and phone columns use `white-space: nowrap !important` and `tabular-nums`.

## Responsive and Accessibility Targets

- Reviewed layout geometry targets: **1440px**, **1366px**, **768px**, and **390px** (plus `< 380px` guardrail).
- Uses global horizontal overflow protection (`overflow-x: clip`, `min-width: 0`), visible `2px` `:focus-visible` keyboard outlines, decorative icon shielding (`aria-hidden="true"`), and `prefers-reduced-motion` support.
