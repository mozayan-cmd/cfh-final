# Technology Stack

**Analysis Date:** 2026-04-22

## Languages

**Primary:**
- PHP [Version not detected] - Laravel backend
- JavaScript - Frontend interactions

**Stylesheet:**
- CSS (Tailwind CSS v4 syntax)

## Runtime

**Environment:**
- Laravel (PHP framework)

**Build Tool:**
- Vite v7.0.7
- Package Manager: npm

## Frameworks

**CSS Framework:**
- Tailwind CSS v4.0.0 - Utility-first CSS framework
  - Vite plugin: `@tailwindcss/vite` v4.0.0

**JavaScript:**
- axios v1.11.0 - HTTP client (in `resources/js/bootstrap.js`)

**Laravel:**
- laravel-vite-plugin v2.0.0 - Vite integration for Laravel

## CSS Architecture

**Primary Stylesheet:**
- `resources/css/app.css` - Main CSS entry point

**Theme System:**
- CSS variables defined in `@theme` block within `app.css`
- Custom color palette using CSS custom properties

**Current Theme Colors (glassmorphism/dark):**
```css
--color-void: #000000;
--color-deepTeal: #02090A;
--color-darkForest: #061A1C;
--color-forest: #102620;
--color-darkCardBorder: #1E2C31;
--color-neon: #36F4A4;        /* Primary accent */
--color-aloe: #C1FBD4;
--color-pistachio: #D4F9E0;
--color-shade30: #D4D4D8;
--color-muted: #A1A1AA;
--color-shade50: #71717A;
--color-shade60: #52525B;
--color-shade70: #3F3F46;
```

**CDN Fallback:**
- `resources/views/layouts/main.blade.php` contains inline Tailwind config for CDN version
- Duplicates theme colors in `tailwind.config.theme.extend.colors`
- Contains additional component-level CSS styles (`.glass-card`, `.btn-primary`, `.btn-secondary`, etc.)

**Font:**
- Font family: `Inter` (defined in `--font-sans` CSS variable)
- Fallback: `ui-sans-serif, system-ui, sans-serif`

## Vite Configuration

**Config File:** `vite.config.js`

```javascript
plugins: [
    laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    }),
    tailwindcss(),
],
```

**Input Files:**
- `resources/css/app.css`
- `resources/js/app.js`

## Custom CSS Patterns

**Scrollbar Styling:**
- Custom scrollbar styles in `app.css` (lines 26-81)
- Duplicate scrollbar rules in `main.blade.php` (lines 60-81)
- Uses neon green theme color `rgba(54, 244, 164, 0.4)`

**Component Classes (in main.blade.php):**
- `.glass-card` - Frosted glass effect with backdrop blur
- `.sidebar-link` - Navigation link with hover states
- `.btn-primary` - White filled button with rounded corners
- `.btn-secondary` - Transparent button with border
- Form inputs styled with dark backgrounds

## Build Commands

```bash
npm run dev      # Development server with hot reload
npm run build    # Production build
```

## Theme Transition Note

**Upcoming Theme Change:**
- Moving from glassmorphism (dark theme with neon green #36F4A4)
- To Intercom-inspired warm editorial design
- New colors: warm cream #faf9f6, off-black #111111, Fin Orange #ff5600
- Updates needed in: `app.css` @theme block, `main.blade.php` inline styles and tailwind.config

---

*Stack analysis: 2026-04-22*
