# FishBiz Mobile - Specification Document

> **Project:** Mobile-First Fishing Business Management WebApp

---

## 1. Overview

**Purpose:** A mobile-first webapp for managing fishing business operations - boats, landings, invoicing, expenses, payments, loans, and cash management. Designed for touch-based interaction on mobile phones, accessible via browser, installable as PWA.

**Target Users:** Fishing business owners, boat operators, and staff who need to record transactions on the go using mobile devices.

**Key Principle:** All features from the existing Laravel app must be preserved; only the GUI changes for mobile-first touch interaction.

---

## 2. Tech Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Frontend | React 18 + Tailwind CSS 4 | Mobile-first responsive |
| Build Tool | Vite | Fast dev server + PWA support |
| Backend | Node.js + Express | REST API |
| Database | SQLite (dev) / PostgreSQL (prod) | Easy local testing |
| ORM | Prisma | Type-safe database queries |
| Auth | JWT + Phone OTP | Both login methods |
| State | React Query + Zustand | Server + local state |
| Forms | React Hook Form | Touch-optimized forms |
| PWA | vite-plugin-pwa | Offline-capable |

---

## 3. UI/UX Specification

### 3.1 Layout Structure

```
+--------------------------------+
|         Header Bar (56px)      |  <- Fixed top, logo + notifications
+--------------------------------+
|                                |
|         Main Content           |  <- Scrollable, full-height
|         (touch-optimized)      |
|                                |
+--------------------------------+
|       Bottom Nav Bar (64px)      |  <- Fixed bottom, 5 icons
+--------------------------------+
```

### 3.2 Touch-First Design Rules

| Rule | Standard |
|------|----------|
| Minimum tap target | 44x44px |
| Button height | 48px minimum |
| Input fields | 48px height |
| List item height | 64px minimum |
| Padding horizontal | 16px |
| Bottom nav safe zone | 80px (iOS home indicator) |
| Icon size | 24px |
| Font size (body) | 16px minimum |
| Spacing unit | 8px base |

### 3.3 Navigation - Bottom Navigation (5 tabs)

| Tab | Icon | Screen | Description |
|-----|------|--------|-------------|
| 1 | Home | Dashboard | Overview, quick actions, recent activity |
| 2 | Wallet | Transactions | All financial transactions |
| 3 | Anchor | Boats | Boat & buyer management |
| 4 | Chart | Reports | Financial reports & charts |
| 5 | Menu | More | Settings, profile, help, logout |

### 3.4 Responsive Breakpoints

| Name | Width | Layout |
|------|-------|--------|
| Mobile | < 640px | Single column, bottom nav |
| Tablet | 640px - 1024px | 2-column, side nav optional |
| Desktop | > 1024px | 3-column, side nav |

---

## 4. Feature Modules

### A. Boats Module
- CRUD operations
- Fields: name, registration_number, owner_name, capacity, status

### B. Buyers Module
- CRUD operations
- Fields: name, phone, email, address, notes

### C. Landings Module
- CRUD operations
- Auto-calculate gross_value = gross_weight x price_per_kg
- Link to boat and buyer

### D. Invoices Module
- CRUD operations
- Link to landings
- Status tracking

### E. Receipts Module
- CRUD operations
- Link to invoices

### F. Expenses Module
- CRUD operations
- Categories: fuel, supplies, maintenance, wages, other

### G. Payments Module
- CRUD operations
- Types: expense, loan_repayment, advance

### H. Loans Module
- CRUD operations
- Track repayments

### I. Cash & Bank
- View balances
- Transaction history

### J. Reports
- Cash flow, income, expense summaries
- Export to PDF/CSV

---

## 5. API Endpoints

All endpoints use `/api/v1/` prefix with standard REST patterns:
- GET /resource - List
- POST /resource - Create
- GET /resource/:id - Read
- PUT /resource/:id - Update
- DELETE /resource/:id - Delete

---

## 6. Testing Strategy

Local development:
- Backend: `npm run server` (port 3000)
- Frontend: `npm run dev` (port 5173)
- Database: SQLite in prisma/dev.db