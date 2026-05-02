# CFH Fund Management - Application Blueprint

## Overview

| Attribute | Value |
|-----------|-------|
| **Name** | CFH Fund Management |
| **Type** | Laravel Blade PHP Application |
| **Version** | v1.0.0 |
| **Purpose** | Boat landing financial management system |
| **Tech Stack** | Laravel 12, TailwindCSS (CDN), Vanilla JS, MySQL |

---

## Architecture

### Directory Structure

```
cfh-fund-management/
├── app/
│   ├── Helpers/
│   │   └── app_helper.php          # Version helper function
│   ├── Http/
│   │   ├── Controllers/             # All module controllers
│   │   ├── Requests/               # Form request validators
│   │   └── Middleware/
│   ├── Models/                     # Eloquent models
│   └── Services/                    # Business logic services
├── config/
├── database/
│   ├── migrations/                 # Database schema
│   └── seeders/
├── docs/
│   ├── ROLLBACK.md                 # Rollback procedures
│   └── RELEASE_WORKFLOW.md         # Release documentation
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── main.blade.php      # Main layout with sidebar
│       ├── components/              # Reusable blade components
│       ├── auth/
│       ├── boats/
│       ├── buyers/
│       ├── cash/
│       ├── dashboard/
│       ├── expenses/
│       ├── invoices/
│       ├── landings/
│       ├── loans/
│       ├── payments/
│       ├── receipts/
│       ├── reports/
│       └── users/
├── routes/
│   └── web.php                     # All web routes
├── scripts/
│   └── release.sh                  # Release automation script
├── tests/
├── CHANGELOG.md
├── VERSION.md
└── latestprompt.md                # Complete specification
```

---

## Modules Overview

| Module | Route | Description |
|--------|-------|-------------|
| Dashboard | `/` | Financial overview and metrics |
| Boats | `/boats` | Fishing vessel management |
| Landings | `/landings` | Trip tracking with settlement |
| Buyers | `/buyers` | Buyer account management |
| Invoices | `/invoices` | Buyer invoice creation |
| Receipts | `/receipts` | Payment receipts from buyers |
| Expenses | `/expenses` | Expense tracking |
| Payments | `/payments` | Owner/expense/loan payments |
| Cash | `/cash` | Cash utilization tracking |
| Loans | `/loans` | Loan management |
| Bank | `/bank` | Bank balance tracking |
| Reports | `/reports` | Reports and backups |
| Users | `/users` | User management (admin) |
| Unlinked Expenses | `/unlinked-expenses` | Admin expense management |

---

## Database Schema

### Core Tables

#### users
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | User name |
| email | varchar | Email (unique) |
| password | varchar | Hashed password |
| role | enum | 'admin' or 'user' |
| is_active | boolean | Account status |
| created_at | timestamp | |
| updated_at | timestamp | |

#### boats
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| name | varchar | Boat name |
| owner_phone | varchar | Owner contact |
| created_at | timestamp | |
| updated_at | timestamp | |

#### landings
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| boat_id | bigint | Boat (FK) |
| date | date | Landing date |
| gross_value | decimal | Total fish value |
| status | enum | Pending/Partial/Settled |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### buyers
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| name | varchar | Buyer name |
| phone | varchar | Contact |
| address | text | Address |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### invoices
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| buyer_id | bigint | Buyer (FK) |
| boat_id | bigint | Boat (FK) |
| landing_id | bigint | Landing (FK) |
| invoice_date | date | Invoice date |
| original_amount | decimal | Total amount |
| received_amount | decimal | Amount paid |
| pending_amount | decimal | Amount due |
| status | enum | Pending/Partial/Paid |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### receipts
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| buyer_id | bigint | Buyer (FK) |
| invoice_id | bigint | Invoice (FK) |
| boat_id | bigint | Boat (FK) |
| landing_id | bigint | Landing (FK) |
| date | date | Receipt date |
| amount | decimal | Payment amount |
| mode | enum | Cash/Bank/GP |
| source | varchar | Payment source |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### expenses
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| boat_id | bigint | Boat (FK) |
| landing_id | bigint | Landing (FK, nullable) |
| date | date | Expense date |
| type | varchar | Expense type |
| vendor_name | varchar | Vendor/person name |
| amount | decimal | Total amount |
| paid_amount | decimal | Amount paid |
| pending_amount | decimal | Amount due |
| payment_status | enum | Pending/Partial/Paid |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### payments
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| boat_id | bigint | Boat (FK) |
| landing_id | bigint | Landing (FK, nullable) |
| date | date | Payment date |
| amount | decimal | Payment amount |
| mode | enum | Cash/Bank/GP |
| source | varchar | Payment source |
| payment_for | varchar | Owner/Expense/Loan/Other |
| loan_reference | varchar | For loan payments |
| vendor_name | varchar | For Other payments |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### payment_allocations
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| payment_id | bigint | Payment (FK) |
| allocatable_type | varchar | Morph type (Expense/Landing) |
| allocatable_id | bigint | Morph ID |
| amount | decimal | Allocated amount |
| created_at | timestamp | |
| updated_at | timestamp | |

#### loans
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| source | varchar | Basheer/Personal/Other |
| amount | decimal | Loan amount |
| repaid_amount | decimal | Amount repaid |
| date | date | Loan date |
| mode | varchar | Cash/Bank |
| notes | text | |
| repaid_at | timestamp | Completion date |
| created_at | timestamp | |
| updated_at | timestamp | |

#### transactions
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK) |
| type | varchar | Receipt/Payment |
| mode | varchar | Cash/Bank/GP |
| source | varchar | Source |
| amount | decimal | Amount |
| boat_id | bigint | Boat (FK, nullable) |
| landing_id | bigint | Landing (FK, nullable) |
| buyer_id | bigint | Buyer (FK, nullable) |
| invoice_id | bigint | Invoice (FK, nullable) |
| cash_source_receipt_id | bigint | Receipt (FK, nullable) |
| transactionable_type | varchar | Morph type |
| transactionable_id | bigint | Morph ID |
| date | date | Transaction date |
| notes | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### expense_types
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Type name |
| created_at | timestamp | |
| updated_at | timestamp | |

#### payment_types
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Type name (Owner/Expense/Loan/Other) |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Routes Summary

### Authentication
```
GET  /login          - Show login form
POST /login          - Process login
POST /logout         - Logout
```

### Protected Routes (auth middleware)
```
Boats:     resource /boats
Buyers:    resource /buyers
Landings:  resource /landings + attach expenses
Invoices:  resource + import/export
Expenses:  resource + import/export
Receipts:  resource + import/export
Payments:  resource + import/export
Cash:      /cash/utilization, /cash/deposit, /cash/report
Loans:     /loans + repay
Bank:      /bank + withdraw
Reports:   /reports + generate
Backups:   /backups (admin only)
Users:     resource (admin only)
```

---

## Services

| Service | Purpose |
|---------|---------|
| PaymentPostingService | Creates payments with allocations |
| ExpenseSettlementService | Manages expense payment tracking |
| LandingSummaryService | Calculates landing financials |
| InvoicePostingService | Handles receipt posting |
| InvoiceImportService | Parses invoice imports |
| CashSourceTrackingService | Tracks cash utilization |

---

## UI Components

### Layout
- Fixed sidebar (w-64) with navigation
- Scrollable main content area
- User info and theme toggle in sidebar footer

### Theme System
- Default: Light mode
- Toggle: localStorage persistence
- Implementation: `.dark` class on `<html>`
- FOUC prevention via inline script

### Table Pattern
```html
<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 260px);">
    <div class="overflow-x-auto flex-1 min-h-0">
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50 sticky top-0">
                <!-- headers -->
            </thead>
            <tbody>
                <!-- rows -->
            </tbody>
        </table>
    </div>
</div>
```

### Filter Form Pattern
```html
<form id="filterForm" method="GET">
    <div class="card rounded-xl p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- dropdowns with min-w-[150px] -->
            <!-- clear button -->
            <!-- summary cards -->
        </div>
    </div>
</form>
```

### Modal Pattern
```html
<div id="modalId" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <!-- content -->
    </div>
</div>
```

---

## Key Features

### Filters
| Page | Filters |
|------|---------|
| Payments | Boat, Landing Date, Mode, Vendor |
| Expenses | Boat, Type, Vendor Status |
| Invoices | Buyer, Boat, Landing Date, Status |
| Receipts | Buyer, Boat, Landing Date, Mode |
| Buyers | Sort by name/purchased/received/pending |

### Import/Export
- CSV export on all list pages
- Paste-mode import (one item per line)
- CSV file upload import

### Business Logic
- Invoice status auto-calculation
- Expense payment tracking
- Landing settlement calculation
- Cash balance tracking per receipt
- Loan repayment management

---

## Configuration

### Tailwind Config (CDN)
```javascript
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'off-black': '#111111',
                'fin-orange': '#ff5600',
                'report-orange': '#fe4c02',
                'report-blue': '#65b5ff',
                'report-green': '#0bdf50',
                'report-red': '#c41c1c',
            }
        }
    }
}
```

### User Scoping
All queries MUST include: `where('user_id', auth()->id())`

### Admin Role
Admin users have `role = 'admin'` and access to:
- User Management
- Unlinked Expenses
- Backups

---

## Version History

| Version | Date | Description |
|---------|------|-------------|
| v1.0.0 | 2026-04-23 | Initial stable release |

---

## Related Documents

| Document | Purpose |
|----------|---------|
| latestprompt.md | Complete application specification |
| CHANGELOG.md | Detailed change log |
| docs/RELEASE_WORKFLOW.md | Release procedures |
| docs/ROLLBACK.md | Rollback guide |
| VERSION.md | Version information |