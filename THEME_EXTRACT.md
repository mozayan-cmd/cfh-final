# CFH Theme Extract - Glassmorphism Design System

## Overview

This document describes the CFH Fund Management theme for application to other Laravel/Tailwind projects. The theme uses a **glassmorphism** design with frosted glass effects, dual light/dark mode support, and the Inter font family.

---

## 1. Fonts

### Google Fonts (import in `<head>`)
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
```

### Tailwind Configuration
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

---

## 2. Custom CSS Properties (app.css / tailwind.config)

Add this to your CSS file with `@theme` (Tailwind v4) or as custom properties:

```css
@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-serif: 'Georgia', ui-serif, serif;
    --font-mono: 'ui-monospace', monospace;

    /* Core Colors */
    --color-off-black: #111111;
    --color-warm-cream: #faf9f6;
    --color-fin-orange: #ff5600;
    --color-report-orange: #fe4c02;
    --color-oat-border: #dedbd6;
    --color-warm-sand: #d3cec6;
    --color-black-80: #313130;
    --color-black-60: #626260;
    --color-black-50: #7b7b78;
    --color-content-tertiary: #9c9fa5;

    /* Report/Data Colors */
    --color-report-blue: #65b5ff;
    --color-report-green: #0bdf50;
    --color-report-red: #c41c1c;
    --color-report-pink: #ff2067;
    --color-report-lime: #b3e01c;

    /* Scrollbar */
    --scrollbar-track: #faf9f6;
    --scrollbar-thumb: rgba(123, 123, 120, 0.4);
}

/* Scrollbar Styles */
* {
    scrollbar-width: thin;
    scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
}
*::-webkit-scrollbar { width: 10px; height: 10px; }
*::-webkit-scrollbar-track { background: var(--scrollbar-track); }
*::-webkit-scrollbar-thumb {
    background: var(--scrollbar-thumb);
    border-radius: 4px;
    border: 2px solid var(--scrollbar-track);
}
*::-webkit-scrollbar-thumb:hover { background: rgba(123, 123, 120, 0.6); }
*::-webkit-scrollbar-corner { background: var(--scrollbar-track); }
```

---

## 3. Theme Initialization Script

Add in `<head>` BEFORE any content to prevent flash of unstyled content (FOUC):

```html
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.add(savedTheme);
    })();
</script>
```

---

## 4. Base Body Styles

### Light Mode Background
```css
body {
    background: linear-gradient(to bottom right, #f8fafc, #e0f2fe, #f1f5f9);
    min-height: 100vh;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    color: #0f172a;
    transition: background-color 0.2s, color 0.2s;
}
```

### Dark Mode (applied when `html.dark` class exists)
```css
html.dark body {
    background: #0f172a;
    color: #ffffff;
}
```

---

## 5. Card Component (Glassmorphism)

### Light Mode Card
```css
.card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(203, 213, 225, 0.6);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s, border-color 0.2s;
}
```

### Dark Mode Card
```css
html.dark .card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}
```

---

## 6. Sidebar Styles

### Light Mode Sidebar
```css
aside {
    background: rgba(255, 255, 255, 0.8);
    border-right: 1px solid rgba(203, 213, 225, 0.6);
}
```

### Dark Mode Sidebar
```css
html.dark aside {
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
html.dark aside h1 { color: #ffffff !important; }
html.dark aside p { color: #94a3b8 !important; }
html.dark .sidebar-link { color: #ffffff !important; }
html.dark .sidebar-link:hover { background: rgba(255, 255, 255, 0.1) !important; }
html.dark .sidebar-link.active {
    background: rgba(255, 86, 0, 0.15) !important;
    border-left: 3px solid #ff5600 !important;
}
```

---

## 7. Form Inputs

### Light Mode Inputs
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

### Dark Mode Inputs
```css
html.dark input, html.dark select, html.dark textarea {
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
}
html.dark select option {
    background: #1e293b !important;
    color: #ffffff !important;
}
html.dark input::placeholder { color: rgba(255, 255, 255, 0.5) !important; }
html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
    border-color: #60a5fa !important;
    outline: 2px solid #60a5fa !important;
}
```

---

## 8. Dark Mode Toggle Button

Add a toggle button with this JavaScript:

```javascript
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    html.classList.remove('dark');
    html.classList.add(isDark ? 'light' : 'dark');
    localStorage.setItem('theme', isDark ? 'light' : 'dark');
}
```

Button example:
```html
<button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
    Switch to {{ session('theme') === 'dark' ? 'light' : 'dark' }} mode
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

### Standard Tailwind Colors Used:
- `slate-50` through `slate-900` for neutral grays
- `blue-500`, `blue-600` for primary interactions
- `green-500`, `green-600`, `green-700` for success states
- `red-500`, `red-600` for error states
- `yellow-500`, `yellow-600` for warnings
- `purple-500`, `purple-600` for special accents

---

## 10. Tailwind CSS Classes Commonly Used

### Cards & Containers
- `.card` - Custom glassmorphism card component
- `rounded-xl` - Border radius 12px
- `rounded-lg` - Border radius 8px
- `rounded-md` - Border radius 6px

### Shadows
- `shadow-sm` - `0 1px 2px rgba(0,0,0,0.05)`
- `shadow-md` - `0 4px 6px rgba(0,0,0,0.1)`
- `shadow-lg` - `0 10px 15px rgba(0,0,0,0.1)`

### Glass Effects
- `bg-white/70` - White with 70% opacity
- `bg-white/80` - White with 80% opacity
- `bg-white/10` - White with 10% opacity
- `backdrop-blur-lg` - Backdrop blur effect

### Transitions
- `transition-all duration-300` - Smooth transitions
- `hover:shadow-md` - Shadow on hover

### Gradients (optional background)
```css
background: linear-gradient(to bottom right, #f8fafc, #e0f2fe, #f1f5f9);
```

---

## 11. Quick Integration Checklist

1. [ ] Add Google Fonts Inter in `<head>`
2. [ ] Add Tailwind config with custom colors
3. [ ] Add theme initialization script BEFORE body
4. [ ] Add base body styles (light mode)
5. [ ] Add `.card` CSS class for both light/dark
6. [ ] Add sidebar glassmorphism styles
7. [ ] Add form input styles
8. [ ] Add dark mode toggle button + JavaScript

---

## 12. Important Notes

1. **DO NOT change any application logic** - This theme only affects presentation
2. The theme uses Tailwind CSS framework
3. Dark mode is controlled by adding/removing `html.dark` class
4. Theme preference is stored in `localStorage.getItem('theme')`
5. The glassmorphism effect uses `rgba(255,255,255, X)` alpha values
6. Custom colors are defined in Tailwind config, not all may be needed - use based on your app requirements