# Collapsible Sidebar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a toggle button to the sidebar header that collapses the sidebar to icon-only view (w-20 ~80px) and expands back to full width (w-64 ~256px).

**Architecture:** Modify the main Blade layout to add a toggle button in the sidebar header, add CSS transitions for smooth collapse/expand animation, and JavaScript to handle the toggle state. Simple localStorage not included - defaults to expanded on each page load.

**Tech Stack:** Laravel Blade templates, Tailwind CSS, vanilla JavaScript

---

### Task 1: Add Toggle Button to Sidebar Header

**Files:**
- Modify: `resources/views/layouts/main.blade.php:424-428`

- [ ] **Step 1: Add toggle button HTML**

Replace the sidebar header div (lines 424-428):
```php
<div class="p-6 border-b border-slate-200 dark:border-white/10 flex-shrink-0 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">CFH</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Fund Management</p>
    </div>
    <button id="sidebarToggle" type="button" aria-label="Toggle sidebar" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-all duration-200">
        <svg id="sidebarToggleIcon" class="w-5 h-5 text-slate-600 dark:text-slate-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
        </svg>
    </button>
</div>
```

- [ ] **Step 2: Verify changes**

Open `resources/views/layouts/main.blade.php` and confirm the toggle button is added at lines 424-435.

---

### Task 2: Add CSS for Collapsed State

**Files:**
- Modify: `resources/views/layouts/main.blade.php:46-420` (style section)

- [ ] **Step 1: Add collapsed sidebar CSS**

Add before `</style>` (around line 419):
```css
/* Collapsed sidebar states */
.sidebar-collapsed aside {
    width: 5rem !important;
}
.sidebar-collapsed .sidebar-content {
    opacity: 0;
    visibility: hidden;
}
.sidebar-collapsed .sidebar-nav a {
    justify-content: center;
    padding: 0.75rem;
}
.sidebar-collapsed .sidebar-nav a span {
    display: none;
}
.sidebar-collapsed .sidebar-user {
    flex-direction: column;
    gap: 0.5rem;
}
.sidebar-collapsed .sidebar-user > div:first-child {
    justify-content: center;
}
.sidebar-collapsed .sidebar-user-text {
    display: none;
}
.sidebar-collapsed .sidebar-logout {
    padding: 0.5rem;
}
.sidebar-collapsed .sidebar-logout span {
    display: none;
}
.sidebar-collapsed .sidebar-theme-btn span {
    display: none;
}

/* Main content adjustment */
.main-expanded {
    margin-left: 16rem;
}
.main-collapsed {
    margin-left: 5rem;
}

/* Toggle icon rotation */
.sidebar-collapsed #sidebarToggleIcon {
    transform: rotate(180deg);
}
```

- [ ] **Step 2: Verify CSS added**

Check that collapsed CSS exists before `</style>` tag.

---

### Task 3: Add JavaScript Toggle Functionality

**Files:**
- Modify: `resources/views/layouts/main.blade.php:592-636` (script section)

- [ ] **Step 1: Add sidebar toggle JavaScript**

Add before the theme toggle script (around line 593):
```javascript
// Sidebar Toggle Functionality
(function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const body = document.body;
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
            body.classList.toggle('main-collapsed');
            
            // Toggle icon rotation is handled by CSS
            // Store state in sessionStorage
            const isCollapsed = body.classList.contains('sidebar-collapsed');
            sessionStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }
    
    // Restore sidebar state from sessionStorage
    const storedState = sessionStorage.getItem('sidebarCollapsed');
    if (storedState === 'true') {
        body.classList.add('sidebar-collapsed');
        body.classList.add('main-collapsed');
    }
})();
```

- [ ] **Step 2: Verify JavaScript added**

Check that sidebar toggle script is added before the theme toggle script.

---

### Task 4: Apply Dynamic Classes for Sidebar Width

**Files:**
- Modify: `resources/views/layouts/main.blade.php:424` (aside tag)

- [ ] **Step 1: Update aside classes for dynamic width**

Change the aside tag (line 424):
```php
<aside class="fixed left-0 top-0 h-screen w-64 card border-r border-slate-200 dark:border-white/10 flex flex-col bg-white/80 dark:bg-white/5 transition-all duration-300">
```

Update main tag (line 564):
```php
<main class="flex-1 p-8 h-screen overflow-y-auto transition-all duration-300">
```

- [ ] **Step 2: Verify dynamic classes**

Check that `transition-all duration-300` is added to both aside and main elements.

---

### Task 5: Add Nav Element Classes

**Files:**
- Modify: `resources/views/layouts/main.blade.php:429` (nav tag)

- [ ] **Step 1: Add classes to nav and user sections**

Add class `sidebar-nav` to nav element:
```php
<nav class="p-4 space-y-1 flex-1 overflow-y-auto sidebar-nav">
```

Add classes to user section div (around line 526):
```php
<div class="p-4 border-t border-slate-200 dark:border-white/10 flex-shrink-0 sidebar-user">
```

- [ ] **Step 2: Add classes to user info elements**

Add `sidebar-user-text` to the user info div:
```php
<div class="flex-1 min-w-0 sidebar-user-text">
```

Add `sidebar-logout` to logout button:
```php
<button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 sidebar-logout">
```

Add `sidebar-theme-btn` to theme toggle button:
```php
<button id="themeToggle" type="button" aria-label="Switch to light mode" class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-300 dark:border-white/20 bg-slate-100 dark:bg-white/10 hover:bg-slate-200 dark:hover:bg-white/20 transition-all duration-200 text-slate-700 dark:text-white sidebar-theme-btn">
```

---

### Task 6: Test and Verify

**Files:**
- Browser test: `http://localhost` (or your local URL)

- [ ] **Step 1: Test toggle functionality**

1. Open dashboard or any authenticated page
2. Click the toggle button (arrow icon) in sidebar header
3. Verify sidebar collapses to ~80px width
4. Verify icons remain visible and centered
5. Verify toggle icon rotates 180°
6. Click toggle button again
7. Verify sidebar expands back to full width
8. Verify toggle icon rotates back

- [ ] **Step 2: Verify dark mode compatibility**

1. Toggle to dark mode using theme button
2. Test collapse/expand in dark mode
3. Verify all styling works correctly

---

### Summary

**Total Tasks:** 6
**Estimated Time:** 10-15 minutes

**Completion Criteria:**
- [ ] Toggle button visible in sidebar header
- [ ] Clicking toggle collapses sidebar to icon-only (~80px)
- [ ] Clicking toggle expands sidebar back to full width
- [ ] Smooth animation transition (300ms)
- [ ] Works in both light and dark modes
- [ ] Toggle icon rotates to indicate state