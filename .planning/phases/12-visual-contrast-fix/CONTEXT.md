# CONTEXT: Visual Design Audit and Contrast Fix

**Phase Type:** Visual UI Fix / Design System Enhancement

---

## Vision

Fix the BoatLedger application's visual contrast issues to improve readability and user experience. The application uses a "Design System Inspired by Intercom" theme with glassmorphism, but several visual elements have poor contrast and readability issues.

---

## Critical Issues to Fix (Locked Decisions)

### D-01: Card Borders (PRIORITY 1)
- **Current State:** Summary cards (Total Purchased, Total Received, Total Pending, etc.) have barely visible borders
- **Problem:** Borders are too light (~10-15% opacity) and blend with the background
- **FIX Required:** Increase border opacity from current ~10-15% to at least 25-30%
- **Implementation:** Use `border-white/30` or `border-white/[0.25]` for glassmorphic cards
- **Additional:** Add subtle shadow for depth: `shadow-lg shadow-black/10`

### D-02: Filter Dropdown Inconsistency
- **Current State:** Some dropdowns have dark backgrounds (Filter, Sort By, Direction) while others are light gray
- **Problem:** Inconsistent visual appearance across the application
- **FIX Required:** Make all filter dropdowns consistent 
- **Implementation:** Use `bg-slate-800/80` with `border-white/20`
- **Hover States:** Ensure hover states are visible: `hover:bg-slate-700/90`

### D-03: Table Styling
- **Current State:** Table headers have low contrast gray backgrounds, row separators and borders are nearly invisible
- **FIX Required:** 
  - Header: `bg-slate-700/50` with `border-white/10`
  - Row borders: `border-slate-600/30` (not `border-white/5`)
  - Hover states: `hover:bg-slate-700/30`

### D-04: Text Readability
- **Current State:** Some text appears washed out against glassmorphic backgrounds
- **FIX Required:** Ensure minimum contrast ratios:
  - Primary text: `text-white` or `text-slate-100`
  - Secondary text: `text-slate-300` (NOT `text-slate-400` or lighter)
  - Labels/headers: `text-slate-200`

### D-05: Glass Card Improvements
- **Current State:** `backdrop-blur-md bg-white/5`
- **IMPROVED:** `backdrop-blur-lg bg-white/10 border border-white/25`
- **Additional:** Add inner glow: `ring-1 ring-inset ring-white/10`

---

## Pages to Audit and Fix

1. Dashboard (`resources/views/dashboard/index.blade.php`)
2. Boats (`resources/views/boats/index.blade.php`)
3. Landings (`resources/views/landings/index.blade.php`)
4. Buyers (`resources/views/buyers/index.blade.php`)
5. Invoices (`resources/views/invoices/index.blade.php`)
6. Import Invoices (`resources/views/invoices/import.blade.php`)
7. Receipts (`resources/views/receipts/index.blade.php`)
8. Expenses (`resources/views/expenses/index.blade.php`)
9. Layout (`resources/views/layouts/main.blade.php`)

---

## the agent's Discretion

The agent has discretion to:
1. Determine the best Tailwind classes to achieve the contrast improvements
2. Apply consistent styling across all pages
3. Fix any additional contrast issues discovered during the audit
4. Ensure all form inputs and buttons have proper focus states

---

## Research Notes

This is a visual/UI-only fix. No external research needed. The codebase uses:
- Tailwind CSS via CDN
- Custom color palette defined in `resources/css/app.css` and in the layout's Tailwind config
- Blade templates with Tailwind utility classes

No external dependencies or library lookups required. Level 0 discovery applies.