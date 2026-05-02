# CFH Fund Management - Theme Specification

## Overview

This theme is a **clean, solid-color design** (NOT glassmorphism). It uses solid background colors for all elements in both light and dark modes. No transparency or alpha-blended colors are used.

---

## 1. Base Configuration

### Tailwind Config
Add to your `tailwind.config.js` or inline script:

```javascript
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'off-black': '#111111',
                'warm-cream': '#faf9f6',
                'fin-orange': '#ff5600',
                'report-orange': '#fe4c02',
                'oat-border': '#dedbd6',
                'warm-sand': '#d3cec6',
                'black-80': '#313130',
                'black-60': '#626260',
                'black-50': '#7b7b78',
                'content-tertiary': '#9c9fa5',
                'report-blue': '#65b5ff',
                'report-green': '#0bdf50',
                'report-red': '#c41c1c',
                'report-pink': '#ff2067',
                'report-lime': '#b3e01c',
            }
        }
    }
}
```

### Google Fonts (import in `<head>`)
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
```

### Theme Initialization Script (prevent FOUC)
Add in `<head>` BEFORE any content:

```html
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.add(savedTheme);
    })();
</script>
```

---

## 2. Base Body Styles

### Light Mode (Default)
```css
body {
    background: linear-gradient(to bottom right, #f8fafc, #e0f2fe, #f1f5f9);
    min-height: 100vh;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    color: #0f172a;
    transition: background-color 0.2s, color 0.2s;
}
```

### Dark Mode (when `html.dark` class exists)
```css
html.dark body {
    background: #0f172a;
    color: #ffffff;
}
```

---

## 3. Card Component (Solid Colors - NO Glassmorphism)

### Light Mode Card
```css
.card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s, border-color 0.2s;
}
```

### Dark Mode Card
```css
html.dark .card {
    background: #1e293b;
    border: 1px solid #334155;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}
```

---

## 4. Sidebar Styles

### Light Mode
```css
aside {
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
}
```

### Dark Mode
```css
html.dark aside {
    background: #1e293b !important;
    border-color: #334155 !important;
}
html.dark aside h1 { color: #ffffff !important; }
html.dark aside p { color: #94a3b8 !important; }
html.dark .sidebar-link { color: #ffffff !important; }
html.dark .sidebar-link:hover { background: #334155 !important; }
html.dark .sidebar-link.active {
    background: #1e3a8a !important;
    border-left: 3px solid #ff5600 !important;
}
```

---

## 5. Form Inputs (Solid Backgrounds)

### Light Mode
```css
input, select, textarea {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 4px;
    padding: 12px 16px;
    transition: border-color 200ms ease;
}
input:focus, select:focus, textarea:focus {
    border-color: #2563eb;
    outline: 2px solid #2563eb;
    outline-offset: 0;
}
input::placeholder { color: #94a3b8; }
```

### Dark Mode
```css
html.dark input, html.dark select, html.dark textarea {
    background: #1e293b !important;
    border-color: #475569 !important;
    color: #ffffff !important;
}
html.dark select option {
    background: #1e293b !important;
    color: #ffffff !important;
}
html.dark input::placeholder, html.dark textarea::placeholder {
    color: rgba(255, 255, 255, 0.5) !important;
}
html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
    border-color: #60a5fa !important;
    outline: 2px solid #60a5fa !important;
}
```

---

## 6. Text Color Overrides

### Light Mode Text (Darken for readability)
```css
.text-slate-400 { color: #64748b !important; }
.text-slate-300 { color: #475569 !important; }
.text-slate-200 { color: #334155 !important; }
.text-slate-500 { color: #475569 !important; }
.text-black-50 { color: #475569 !important; }
.text-content-tertiary { color: #64748b !important; }
```

### Dark Mode Text (Lighten for readability)
```css
html.dark .text-slate-400 { color: #94a3b8 !important; }
html.dark .text-slate-300 { color: #cbd5e1 !important; }
html.dark .text-slate-200 { color: #e2e8f0 !important; }
html.dark .text-slate-500 { color: #94a3b8 !important; }
html.dark .text-black-50 { color: #94a3b8 !important; }
html.dark .text-content-tertiary { color: #94a3b8 !important; }

/* Fix text-off-black in dark mode */
html.dark .text-off-black { color: #ffffff !important; }
```

---

## 7. Scrollbar Styles (Solid Colors)

### Light Mode
```css
* {
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
}
*::-webkit-scrollbar { width: 10px; height: 10px; }
*::-webkit-scrollbar-track { background: #f1f5f9; }
*::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
    border: 2px solid #f1f5f9;
}
*::-webkit-scrollbar-thumb:hover { background: #64748b; }
*::-webkit-scrollbar-corner { background: #f1f5f9; }
```

### Dark Mode
```css
html.dark * {
    scrollbar-width: thin;
    scrollbar-color: #475569 #1e293b;
}
html.dark *::-webkit-scrollbar-track { background: #1e293b; }
html.dark *::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
    border: 2px solid #1e293b;
}
html.dark *::-webkit-scrollbar-thumb:hover { background: #64748b; }
html.dark *::-webkit-scrollbar-corner { background: #1e293b; }
```

---

## 8. Dark Mode Toggle Button

### JavaScript Function
```javascript
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    html.classList.remove('dark');
    html.classList.add(isDark ? 'light' : 'dark');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}
```

### Button Example
```html
<button onclick="toggleTheme()" 
        class="px-4 py-2 text-black-50 hover:text-off-black">
    Switch to <span id="theme-text">{{ session('theme') === 'dark' ? 'light' : 'dark' }} mode</span>
</button>
```

---

## 9. Color Palette Summary

| Color Name | Hex Code | Usage |
|-----------|---------|-------|
| `off-black` | #111111 | Primary text, headings |
| `warm-cream` | #faf9f6 | Background accent |
| `fin-orange` | #ff5600 | Primary brand color, active states |
| `report-orange` | #fe4c02 | Alerts, warnings |
| `report-blue` | #65b5ff | Information, links |
| `report-green` | #0bdf50 | Success states |
| `report-red` | #c41c1c | Error states |
| `report-pink` | #ff2067 | Special highlights |
| `report-lime` | #b3e01c | Accent highlights |
| `oat-border` | #dedbd6 | Border accent |
| `warm-sand` | #d3cec6 | Background accent |
| `black-80` | #313130 | Text shade |
| `black-60` | #626260 | Text shade |
| `black-50` | #7b7b78 | Secondary text |
| `content-tertiary` | #9c9fa5 | Tertiary text |

### Standard Tailwind Colors Used:
- `slate-50` through `slate-900` for neutral grays
- `blue-500`, `blue-600`, `blue-700` for primary interactions
- `green-500`, `green-600`, `green-700` for success states
- `red-500`, `red-600` for error states
- `yellow-500`, `yellow-600` for warnings
- `purple-500`, `purple-600` for special accents

---

## 10. Key Design Principles

1. **NO GLASSMORPHISM** - Do NOT use `rgba()` with alpha < 1.0 for backgrounds
2. **SOLID COLORS ONLY** - All backgrounds must be fully opaque (alpha = 1.0)
3. **Light Mode**: White/Light Gray backgrounds (`#ffffff`, `#f1f5f9`)
4. **Dark Mode**: Dark Blue-Gray backgrounds (`#0f172a`, `#1e293b`, `#334155`)
5. **Text Contrast**: Ensure text is readable in both modes
   - Light mode: Dark text (`#0f172a`, `#334155`, `#64748b`)
   - Dark mode: Light text (`#ffffff`, `#e2e8f0`, `#94a3b8`)
6. **Cards**: Use `.card` class with solid background
7. **Scrollbars**: Solid colors, no transparency
8. **Form Inputs**: Solid backgrounds in both modes

---

## 11. Implementation Checklist

- [ ] Add Google Fonts Inter in `<head>`
- [ ] Add Tailwind config with custom colors
- [ ] Add theme initialization script BEFORE body content
- [ ] Add base body styles (light/dark)
- [ ] Add `.card` CSS class (solid colors for both modes)
- [ ] Add sidebar styles (solid colors)
- [ ] Add form input styles (solid colors)
- [ ] Add text color overrides for dark mode
- [ ] Add scrollbar styles (solid colors)
- [ ] Add dark mode toggle button + JavaScript
- [ ] Test all pages in both light and dark modes
- [ ] Verify NO `rgba()` with alpha < 1.0 is used
- [ ] Verify text is readable in dark mode (white/light colors)

---

## 12. Important Notes

1. **This is NOT a glassmorphism theme** - No frosted glass effects
2. **All colors must be solid** - No transparency in backgrounds
3. **Dark mode** is controlled by adding/removing `html.dark` class
4. **Theme preference** is stored in `localStorage.getItem('theme')`
5. **Test in both modes** - Every page must be readable in light AND dark
6. **Common mistake to avoid**: Using `text-off-black` in dark mode without override (it's `#111111` - nearly black!)
7. **Fix for `text-off-black` in dark mode**: Add `html.dark .text-off-black { color: #ffffff !important; }`
