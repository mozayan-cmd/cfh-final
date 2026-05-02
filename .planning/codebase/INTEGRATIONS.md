# External Integrations

**Analysis Date:** 2026-04-22

## CDN Resources

**Tailwind CSS (CDN):**
- URL: `https://cdn.tailwindcss.com`
- Used in: `resources/views/layouts/main.blade.php` (line 8)
- Purpose: Fallback/polygon for development; provides tailwind.config via inline script
- Note: Primary build uses Vite + @tailwindcss/vite plugin

**Tailwind Config (Inline):**
- Location: `main.blade.php` lines 9-31
- Extends theme with custom colors matching the `@theme` block in `app.css`
- Duplicates color palette for CDN version

## Fonts

**Inter Font:**
- Source: Google Fonts (implied by name, not explicitly imported in examined files)
- CSS variable: `--font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif, ...`
- Used in: All text via Tailwind's `font-sans` utility

**No External Font CDN Import Detected:**
- Font may be loaded elsewhere (not in examined layout files)
- Verify if Inter is loaded via `_shared.blade.php` or other included views

## Icons

**Inline SVG Icons:**
- All icons are inline SVG in `main.blade.php`
- Heroicons style (stroke-based, 24x24 viewBox)
- SVG paths used for:
  - Dashboard (home icon)
  - Boats (lightning bolt)
  - Landings (clipboard)
  - Buyers (users group)
  - Invoices (document)
  - Import Invoices (upload)
  - Receipts (currency)
  - Expenses (dollar)
  - Payments (credit card)
  - Cash Management (wallet)
  - Loans (building)
  - Bank Management (building)
  - Control Panel (settings)
  - User Management (user)
  - Unlinked Expenses (link)
  - Logout (sign-out)

**No Icon Library CDN:**
- Icons are hand-coded inline SVGs
- No external icon library (Font Awesome, Heroicons CDN, etc.)

## HTTP Client

**Axios:**
- Package: `axios` v1.11.0
- Location: `resources/js/bootstrap.js`
- Configured as: `window.axios` with default headers
- Default header: `X-Requested-With: XMLHttpRequest`

## JavaScript Dependencies

**Via npm (built with Vite):**
- `@tailwindcss/vite` - Tailwind Vite plugin
- `laravel-vite-plugin` - Laravel Vite integration
- `tailwindcss` - CSS framework
- `vite` - Build tool
- `axios` - HTTP client
- `concurrently` - Run multiple scripts

**Not Using (CDN):**
- No Alpine.js CDN
- No other JS library CDNs

## Environment Configuration

**Required env vars (not examined for security):**
- `.env` file present (not read - contains environment configuration)
- Database, cache, session, and app configuration stored there

## No External Services Detected

**The following are NOT used:**
- No Stripe/Payment processor CDN
- No Google Analytics
- No external API integrations
- No image/CDN for assets
- No font CDN (Inter may be self-hosted or loaded differently)

---

*Integration audit: 2026-04-22*
