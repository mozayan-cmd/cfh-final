# CFH Fund Management - Complete Application Specification

## App Overview

**Application Name:** CFH Fund Management  
**Type:** Laravel Blade-based PHP Application (v11+)  
**Purpose:** Manage boat landing finances including buyer invoices, receipts, expenses, payments, cash management, loans, and bank accounts  
**Tech Stack:** Laravel 11, TailwindCSS (CDN), Vanilla JavaScript  
**Default Theme:** Light mode (dark mode toggle available)

---

## Modules

### 1. Dashboard (`/`)
**Purpose:** Overview of all financial metrics and quick navigation

**UI Elements:**
- 9 metric cards in a responsive grid (1/2/3 columns):
  - Cash in Hand (links to Cash Utilization)
  - Cash at Bank (links to Bank Report)
  - Buyer Pending (links to Receipts)
  - Boat Owner Pending (links to Payments with `payment_for=Owner`)
  - Expense Pending (links to Expenses with `status=pending`)
  - Outstanding Loans (links to Loans, shows breakdown: B=Basheer, P=Personal, O=Others)
  - Overdue Landings count
  - Unlinked Expenses total and count
  - Loan Repayments (links to Payments with `payment_for=Loan`)

**Recent Activity Tables:**
- Recent Landings (Date, Boat, Gross Value, Status)
- Recent Receipts (Date, Buyer, Amount, Mode)
- Recent Payments (Date, Boat, Amount, For)
- Pending Settlements (Date, Boat, Pending Amount, View link)

---

### 2. Boats (`/boats`)
**Purpose:** Manage fishing vessel records

**Features:**
- List all boats with owner phone number
- Create, View, Update, Delete operations
- Each boat linked to landings, invoices, expenses, payments, receipts, transactions

**Data Model:**
```
Boat {
  id, user_id, name, owner_phone
}
```

---

### 3. Landings (`/landings`)
**Purpose:** Track individual fishing trips with financial settlement

**Features:**
- Create landings with date and gross_value
- Attach expenses to landings
- Calculate owner payable (gross_value - expenses)
- Track owner payments against payable
- Calculate buyer pending (invoices - receipts)
- Settlement status: Pending, Partial, Settled

**Status Calculation Logic:**
- `Pending`: owner_pending > 0 AND total_owner_paid = 0
- `Partial`: total_owner_paid > 0 AND owner_pending > 0
- `Settled`: owner_pending <= 0

**Calculated Fields (via Landing Model Accessors):**
- `total_expenses`: SUM of all linked expenses
- `net_owner_payable`: gross_value - total_expenses
- `total_owner_paid`: SUM of payments where payment_for != 'Expense'
- `owner_pending`: net_owner_payable - total_owner_paid
- `total_invoices`: SUM of original_amount
- `total_received`: SUM of received_amount
- `total_buyer_pending`: SUM of pending_amount

---

### 4. Buyers (`/buyers`)
**Purpose:** Manage buyer accounts who purchase fish from landings

**Features:**
- List buyers with aggregated invoice statistics
- Sort by: Name, Total Purchased, Total Received, Pending, Invoice Count
- Create new buyer (name, phone, address, notes)
- View buyer details with all invoices
- Delete buyer (blocked if has invoices/receipts)

**Table Columns:**
- Name (sortable, links to buyer show page)
- Contact (phone)
- Total Purchased (sortable)
- Total Received (sortable)
- Pending (sortable, highlighted in yellow if > 0)
- Actions (View, Delete)

**Data Model:**
```
Buyer {
  id, user_id, name, phone, address, notes
}
```

**Relationship:** HasMany Invoices, HasMany Receipts

---

### 5. Invoices (`/invoices`)
**Purpose:** Create invoices for buyers purchasing fish from a landing

**Features:**
- Create invoice: buyer, boat, landing, invoice_date, original_amount, notes
- Auto-set: received_amount=0, pending_amount=original_amount, status='Pending'
- List with filters
- View invoice details
- Edit invoice date, amount, notes (updates pending_amount and status)
- Delete invoice (blocked if has receipts)
- Export CSV
- Import Invoices (paste mode or CSV upload)

**Import Format:**
```
buyer_name|amount
```
Example:
```
jonettan|5000
irctc|3500
```

**Filters (backend query params):**
- `buyer_id`: Filter by specific buyer OR special value `pending_only` (shows buyers with unpaid invoices)
- `boat_id`: Filter by boat
- `landing_id`: Filter by landing
- `status`: Filter by status (Pending, Partial, Paid)

**Summary Cards (top of page):**
- Total Value (cyan): SUM of original_amount
- Total Received (green): SUM of received_amount
- Pending Amount (yellow): SUM of pending_amount

**Table Structure:**
- Date, Buyer, Boat, Landing, Original, Received, Pending, Status, Actions

**Status Calculation:**
- `Pending`: pending_amount == original_amount AND received_amount == 0
- `Partial`: received_amount > 0 AND pending_amount > 0
- `Paid`: pending_amount <= 0

**Data Model:**
```
Invoice {
  id, user_id, buyer_id, boat_id, landing_id, 
  invoice_date, original_amount, received_amount, 
  pending_amount, status, notes
}
```

**Relationship:** HasMany Receipts, BelongsTo Buyer, Boat, Landing

---

### 6. Receipts (`/receipts`)
**Purpose:** Record payments received from buyers against invoices

**Features:**
- Create receipt: buyer, invoice, boat, landing, date, amount, mode, source, notes
- Auto-update invoice received_amount, pending_amount, status
- List with filters
- View receipt details
- Edit receipt (recalculates invoice balance)
- Delete receipt (recalculates invoice balance)
- Export CSV
- Import Receipts (paste mode or CSV upload)

**Import Format:** Plain amounts (one per line)

**Filters (backend query params):**
- `buyer_id`: Filter by buyer
- `boat_id`: Filter by boat
- `landing_id`: Filter by landing
- `mode`: Filter by mode (Cash, Bank). Note: Bank mode includes both Bank and GP

**Summary Cards:**
- Total Amount
- Cash (filtered by mode='Cash')
- Bank (filtered by mode IN ('Bank', 'GP'))

**Buyer Breakdown Section:**
- Shows when `boat_id` filter is active
- Cards for each buyer showing: name, receipt count, total amount, cash total, bank total

**Table Structure:**
- Date, Buyer, Invoice (date), Boat, Amount, Mode (with badge), Actions

**Mode Badges:**
- Cash: green badge
- Bank: blue badge

**Data Model:**
```
Receipt {
  id, user_id, buyer_id, invoice_id, boat_id, landing_id,
  date, amount, mode, source, notes
}
```

**Relationship:** BelongsTo Buyer, Invoice, Boat, Landing; MorphOne Transaction

**Receipt Deletion Logic:**
```php
// Updates invoice balance
$invoice->received_amount -= $receipt->amount;
$invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
$invoice->status = recalculated based on pending_amount;
```

---

### 7. Expenses (`/expenses`)
**Purpose:** Track all expenses associated with boats/landings

**Features:**
- Create expense with modal form
- Edit expense via modal
- Delete expense (blocked if has payment allocations)
- Add expense types
- List with filters
- Export CSV
- Import Expenses (paste mode or CSV upload)

**Import Format:**
```
date|type|vendor_name|amount
```
Example:
```
2024-01-15|Diesel|johney|5000
2024-01-16|Ice|irctc|2000
```

**Filters (backend query params):**
- `boat_id`: Filter by boat
- `landing_id`: Filter by landing
- `type`: Filter by expense type
- `vendor_status`: Special filter
  - `paid`: Show expenses from vendors with SUM(pending_amount)=0 across all their expenses
  - `pending`: Show expenses from vendors with SUM(pending_amount)>0 across all their expenses

**Summary Cards:**
- Total Amount (yellow): SUM of all filtered expenses
- Pending Amount (cyan): SUM of expenses where payment_status != 'Paid'

**Table Columns:**
- Date, Boat, Type, Vendor, Amount, Paid, Pending, Status, Actions

**Status Badges:**
- Paid: green
- Partial: yellow
- Pending: gray/slate

**Payment Integration:**
- "Pay" button links to payment creation with expense_id pre-selected
- Edit modal shows payment allocations

**Data Model:**
```
Expense {
  id, user_id, boat_id, landing_id, date, type, 
  vendor_name, amount, paid_amount, pending_amount, 
  payment_status, notes
}
```

**Relationship:** BelongsTo Boat, Landing; HasMany PaymentAllocations; MorphMany Transactions

**Status Calculation:**
- `Paid`: pending_amount <= 0
- `Partial`: paid_amount > 0 AND pending_amount > 0
- `Pending`: paid_amount = 0 AND pending_amount = amount

---

### 8. Payments (`/payments`)
**Purpose:** Record payments made (to boat owners, for expenses, for loans)

**Features:**
- Create payment with multiple allocation options
- Edit payment (can modify allocations)
- Delete payment (reverses allocations)
- Export CSV
- Import Payments (paste mode or CSV upload)
- View payment with allocation details

**Import Format:** Plain amounts (one per line)

**Payment Modes:** Cash, GP, Bank

**Payment For Options:** Owner, Expense, Loan, Other (stored in PaymentType table)

**When Creating Payment:**
- Select Boat (required)
- Select Landing (optional, shows landing summary)
- Select Payment For: Owner, Expense, Loan, Other
- If "Expense": Select expenses with pending amounts
- If "Other": Enter vendor_name
- Enter amount, mode, date
- For Cash mode: Can optionally select a receipt as cash source
- Balance validation: Cannot exceed available cash/bank balance

**Balance Validation:**
```php
// Cash with receipt source: receipt amount - utilized - deposited
// Cash without source: total available cash
// Bank/GP: bank balance
// Loan repayment: validated against outstanding loan amount
```

**Filters (backend query params):**
- `boat_id`: Filter by boat
- `landing_id`: Filter by landing
- `mode`: Filter by mode (Cash, GP, Bank)
- `vendor`: Filter by vendor_name (dynamically populated from existing payments)

**Summary Card:**
- Total Amount (indigo): SUM of all filtered payments

**Table Columns:**
- Date, Boat, Landing, Amount, Mode (with badge), Type, Vendor, Actions

**Table Row Highlighting:**
- Loan payments: cyan background tint

**Allocation Display:**
- Type column: Shows "Loan" for loan payments, otherwise shows allocation type (expense type or "Landing")
- Vendor column: Shows loan_reference for loans, vendor_name for Other, or allocation vendor

**Data Model:**
```
Payment {
  id, user_id, boat_id, landing_id, date, amount, mode,
  source, payment_for, loan_reference, notes, vendor_name
}
```

**Relationship:** BelongsTo Boat, Landing; HasMany PaymentAllocations; MorphOne Transaction

**Payment Allocations:**
Payments can be allocated to:
- Expenses (via PaymentAllocation)
- Landings (direct owner payment against landing)

**PaymentPostingService Logic:**
```php
// 1. Create Payment record
// 2. Create Transaction record
// 3. For each expense allocation:
//    - Update expense paid_amount, pending_amount, payment_status
//    - Create PaymentAllocation record
//    - Create Transaction record
// 4. For landing direct payment:
//    - Update landing total_owner_paid
```

---

### 9. Cash Management (`/cash/utilization`)
**Purpose:** Track how cash receipts are utilized

**Features:**
- View cash receipts with utilization breakdown
- Deposit cash to bank
- Edit/delete cash transactions
- Cash utilization report
- Bank report

**Receipt Utilization Tracking:**
- Cash receipts come in from buyers
- Cash goes out via payments (mode=Cash)
- Track which receipt was used for each payment
- Calculate balance per receipt: amount - utilized - deposited

**Data Flow:**
```
Receipt (Cash) -> Transaction (Receipt type)
                    |
                    v
            Payment (Cash) -> Transaction (Payment type)
                    |
                    v
            PaymentAllocation
```

---

### 10. Loans (`/loans`)
**Purpose:** Track loans from various sources

**Loan Sources:** Basheer, Personal, Other

**Features:**
- List loans with outstanding amounts
- Create loan: source, amount, date, mode, notes
- Repay loan: amount, date, mode
- Add loan types

**Outstanding Loan Calculation:**
```php
outstanding = amount - repaid_amount
```
Only loans where `repaid_at IS NULL` are considered outstanding.

**Dashboard Breakdown:**
- B (Basheer): SUM of outstanding loans from Basheer source
- P (Personal): SUM of outstanding loans from Personal source
- O (Others): SUM of outstanding loans from Other sources

**Data Model:**
```
Loan {
  id, user_id, source, amount, repaid_amount, 
  date, mode, notes, repaid_at
}
```

---

### 11. Bank Management (`/bank`)
**Purpose:** Track bank balance and withdrawals

**Features:**
- View bank balance
- Withdraw to cash

**Data Model:**
```
Transaction {
  id, user_id, type, mode, source, amount, 
  boat_id, landing_id, buyer_id, invoice_id,
  cash_source_receipt_id, transactionable_type, 
  transactionable_id, date, notes
}
```

Bank balance = SUM of transactions where mode IN ('Bank', 'GP') AND type = 'Receipt' (minus withdrawals)

---

### 12. Control Panel (`/reports`)
**Purpose:** Access reports and backup functionality

**Features:**
- Settlement reports (per landing)
- Cash report
- Bank report
- Database backup (admin only)
  - Create backup
  - Download backup
  - Restore backup
  - Clear data (user-specific or all)

---

### 13. User Management (`/users`) - Admin Only
**Purpose:** Manage application users

**Features:**
- List users
- Create user
- Edit user
- Toggle user active status
- Delete user

**User Model:**
```
User {
  id, name, email, password, role (admin/user), is_active
}
```

---

### 14. Unlinked Expenses (`/unlinked-expenses`) - Admin Only
**Purpose:** Manage expenses not linked to any landing

**Features:**
- List unlinked expenses
- Edit unlinked expenses
- Link expenses to landings

---

## Data Models Summary

### Core Entities

| Entity | Key Fields | Relationships |
|--------|-----------|---------------|
| User | id, name, email, password, role, is_active | HasMany Boats, Landings, Expenses, Invoices, etc. |
| Boat | id, user_id, name, owner_phone | HasMany Landings, Expenses, Invoices, Payments, Receipts |
| Landing | id, user_id, boat_id, date, gross_value, notes, status | BelongsTo Boat; HasMany Expenses, Invoices, Payments, Receipts, Transactions |
| Buyer | id, user_id, name, phone, address, notes | HasMany Invoices, Receipts |
| Invoice | id, user_id, buyer_id, boat_id, landing_id, invoice_date, original_amount, received_amount, pending_amount, status, notes | BelongsTo Buyer, Boat, Landing; HasMany Receipts, Transactions |
| Receipt | id, user_id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode, source, notes | BelongsTo Buyer, Invoice, Boat, Landing; MorphOne Transaction |
| Expense | id, user_id, boat_id, landing_id, date, type, vendor_name, amount, paid_amount, pending_amount, payment_status, notes | BelongsTo Boat, Landing; HasMany PaymentAllocations; MorphMany Transactions |
| Payment | id, user_id, boat_id, landing_id, date, amount, mode, source, payment_for, loan_reference, notes, vendor_name | BelongsTo Boat, Landing; HasMany PaymentAllocations; MorphOne Transaction |
| PaymentAllocation | id, payment_id, allocatable_type, allocatable_id, amount | BelongsTo Payment; MorphTo (Expense or Landing) |
| Loan | id, user_id, source, amount, repaid_amount, date, mode, notes, repaid_at | BelongsTo User |
| Transaction | id, user_id, type, mode, source, amount, boat_id, landing_id, buyer_id, invoice_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes | BelongsTo User, Boat, Landing, Buyer, Invoice; MorphTo transactionable |
| ExpenseType | id, name | Used for categorizing expenses |
| PaymentType | id, name | Used for categorizing payments (Owner, Expense, Loan, Other) |
| LoanSource | id, name | Used for categorizing loans (Basheer, Personal, Other) |

### Lookup Tables
- ExpenseType: id, name
- PaymentType: id, name
- LoanSource: id, name

---

## Filters & Logic

### Filter Architecture
All main list pages use:
1. `<form id="filterForm">` with GET method and auto-submit on select change
2. Backend query scopes in Controllers
3. URL query parameters for filter state

### Filter Details by Page

#### Payments Page
| Filter | Query Param | Backend Logic |
|--------|-------------|---------------|
| Boat | `boat_id` | WHERE boat_id = ? |
| Landing Date | `landing_id` | WHERE landing_id = ? |
| Payment Mode | `mode` | WHERE mode = ? (Cash/GP/Bank) |
| Vendor | `vendor` | WHERE vendor_name = ? (dynamic from existing data) |
| Clear | - | Redirect to base route (no params) |

#### Expenses Page
| Filter | Query Param | Backend Logic |
|--------|-------------|---------------|
| Boat | `boat_id` | WHERE boat_id = ? |
| Landing | `landing_id` | WHERE landing_id = ? |
| Type | `type` | WHERE type = ? |
| Vendor Status | `vendor_status` | GROUP BY vendor_name HAVING SUM(pending_amount) = 0 or > 0 |
| Clear | - | Redirect to base route |

#### Invoices Page
| Filter | Query Param | Backend Logic |
|--------|-------------|---------------|
| Buyer | `buyer_id` | WHERE buyer_id = ? OR WHERE buyer_id IN (SELECT buyers with pending) |
| Boat | `boat_id` | WHERE boat_id = ? |
| Landing Date | `landing_id` | WHERE landing_id = ? |
| Status | `status` | WHERE status = ? (Pending/Partial/Paid) |
| Clear | - | Redirect to base route |

#### Receipts Page
| Filter | Query Param | Backend Logic |
|--------|-------------|---------------|
| Buyer | `buyer_id` | WHERE buyer_id = ? |
| Boat | `boat_id` | WHERE boat_id = ? |
| Landing Date | `landing_id` | WHERE landing_id = ? |
| Payment Mode | `mode` | WHERE mode = ? OR WHERE mode IN ('Bank','GP') when mode=Bank |
| Clear | - | Redirect to base route |

#### Buyers Page
| Filter | Query Param | Backend Logic |
|--------|-------------|---------------|
| Filter | `filter` | pending: total_pending > 0; no_pending: total_pending = 0 |
| Sort | `sort` | name, purchased, received, pending, invoices |
| Direction | `direction` | asc, desc |

---

## UI/UX Rules

### Theme System
- **Default**: Light mode
- **Toggle**: Button in sidebar footer, persists to localStorage
- **Implementation**: Class `dark` on `<html>` element
- **FOUC Prevention**: Inline script in `<head>` applies theme before page render

### Color Palette (Tailwind Extended)
```javascript
colors: {
  'off-black': '#111111',
  'warm-cream': '#faf9f6',
  'fin-orange': '#ff5600',
  'report-orange': '#fe4c02',
  'oat-border': '#dedbd6',
  'warm-sand': '#d3cec6',
  'report-blue': '#65b5ff',
  'report-green': '#0bdf50',
  'report-red': '#c41c1c',
  'report-pink': '#ff2067',
  'report-lime': '#b3e01c',
}
```

### Scrollable Table Pattern
**All main data tables use this pattern:**
```html
<div class="card rounded-xl overflow-hidden table-container flex flex-col" style="height: calc(100vh - 260px);">
    <div class="overflow-x-auto flex-1 min-h-0">
        <table class="w-full">
            <thead class="bg-slate-50/60 dark:bg-slate-700/50">
                <tr class="... sticky top-0 bg-slate-100 dark:bg-slate-700/80 z-10">
                    <!-- headers -->
                </tr>
            </thead>
            <tbody>
                <!-- rows -->
            </tbody>
        </table>
    </div>
</div>
```

**Height Adjustments:**
- Payments/Expenses: `calc(100vh - 260px)`
- Invoices: `calc(100vh - 280px)`
- Buyers: `calc(100vh - 340px)` (has "Add New Buyer" form below)
- Receipts: `calc(100vh - 260px)`

**Table Container CSS:**
```css
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-container table {
    min-width: 600px;
}
```

### Filter Section Pattern
```html
<form id="filterForm" method="GET" action="{{ route('module.index') }}" class="mb-6">
    <div class="card rounded-xl p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Filter dropdowns -->
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Label</label>
                <select name="param" id="filterParam" class="w-full ... dark:...">
                    <option value="">All Options</option>
                    @foreach($options as $option)
                        <option value="{{ $option->id }}" {{ request('param') == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Clear button -->
            <div class="flex gap-2">
                <a href="{{ route('module.index') }}" class="bg-slate-200 ...">Clear</a>
            </div>
            <!-- Summary cards -->
            <div class="bg-cyan-50 dark:bg-cyan-900/30 ...">
                <p class="text-xs ...">Label</p>
                <p class="text-lg font-bold ...">₹{{ number_format($total, 2) }}</p>
            </div>
        </div>
    </div>
</form>
```

### Auto-Submit on Filter Change
```javascript
document.getElementById('filterParam').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});
```

### Form Input Styling
**Light Mode:**
```css
input, select, textarea {
    background: #ffffff;
    border: 1px solid #dedbd6;
    color: #111111;
    border-radius: 4px;
    padding: 12px 16px;
}
input:focus, select:focus, textarea:focus {
    border-color: #ff5600;
    outline: 2px solid #ff5600;
    outline-offset: 0;
}
```

**Dark Mode Overrides:**
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
```

### Card Component
```css
.card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(203, 213, 225, 0.6);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
html.dark .card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
```

### Scrollbar Styling
**Light Mode:**
```css
* {
    scrollbar-color: rgba(148, 163, 184, 0.5) #f1f5f9;
}
```

**Dark Mode:**
```css
html.dark * {
    scrollbar-color: rgba(100, 100, 100, 0.4) #1e293b;
}
```

### Modal Pattern
```html
<div id="modalId" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="card rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Title</h3>
            <button onclick="closeModal('modalId')" class="...">X</button>
        </div>
        <form>
            <!-- form fields -->
        </form>
    </div>
</div>
```

### Delete Confirmation Modal
Uses API endpoint `/api/related-records/{model}/{id}` to fetch related records before deletion.

---

## Business Logic

### Invoice-Receipt Balance Calculation
When receipt is created/updated/deleted:
```php
$invoice->received_amount = $invoice->received_amount + $receipt_amount;
$invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
$invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : 
                   ($invoice->received_amount > 0 ? 'Partial' : 'Pending');
```

### Expense Payment Settlement
When payment is created with expense allocation:
```php
$expense->paid_amount += $allocation_amount;
$expense->pending_amount = $expense->amount - $expense->paid_amount;
$expense->payment_status = $expense->pending_amount <= 0 ? 'Paid' : 'Partial';
```

### Landing Owner Payment Tracking
```php
// Owner payable = gross_value - total_expenses
$net_owner_payable = $landing->gross_value - $landing->expenses()->sum('amount');

// Owner paid = SUM of payments where payment_for != 'Expense'
$total_owner_paid = $landing->payments()->where('payment_for', '!=', 'Expense')->sum('amount');

// Owner pending = net_payable - paid
$owner_pending = $net_owner_payable - $total_owner_paid;
```

### Cash Source Tracking
Cash receipts can be tracked individually:
```php
$receipt->amount  // Total cash received
$receipt->utilized  // Amount used in payments (via cash_source_receipt_id)
$receipt->deposited  // Amount deposited to bank
$balance = $receipt->amount - $receipt->utilized - $receipt->deposited;
```

### Loan Repayment
```php
$loan->repaid_amount += $repayment_amount;
if ($loan->repaid_amount >= $loan->amount) {
    $loan->repaid_at = now();
}
```

### User Scoping
ALL queries include `WHERE user_id = auth()->id()` to ensure data isolation between users.

### Admin vs Regular Users
Admin users have additional access:
- User Management (`/users`)
- Unlinked Expenses (`/unlinked-expenses`)
- Backup/Restore (`/backups`)

---

## API Design

### RESTful Routes Pattern
```
GET     /resource            -> index
GET     /resource/create      -> create
POST    /resource             -> store
GET     /resource/{id}        -> show
GET     /resource/{id}/edit    -> edit
PUT     /resource/{id}        -> update
DELETE  /resource/{id}        -> destroy
```

### API Endpoints

#### Invoices
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/invoices/landings/{boat}` | Get landings for boat with invoice totals |
| GET | `/invoices/pending-landings/{boat}` | Get landings with pending invoices |
| POST | `/invoices/preview` | Preview import data |
| POST | `/invoices/process-import` | Process imported invoices |

#### Receipts
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/receipts/invoices/{buyer}` | Get pending invoices for buyer |
| GET | `/receipts/api/buyers` | Get buyers with pending invoices (filtered by boat/landing) |
| GET | `/receipts/api/invoices` | Get invoices for buyer (filtered by boat/landing) |
| POST | `/receipts/import/preview` | Preview import |
| POST | `/receipts/import/process` | Process import |

#### Payments
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/payments/landings/{boat}` | Get landings for boat |
| GET | `/payments/landing/{landing}` | Get pending expenses for landing |
| GET | `/payments/expenses/{boat}` | Get pending expenses for boat |
| POST | `/payments/import/preview` | Preview import |
| POST | `/payments/import/process` | Process import |

#### Expenses
| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/expenses/import/preview` | Preview import |
| POST | `/expenses/import/process` | Process import |
| POST | `/expenses/types` | Add expense type |

#### Cash Management
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/cash/api/available-receipts` | Get cash receipts with available balance |
| POST | `/cash/deposit` | Deposit cash to bank |

#### General
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/related-records/{model}/{id}` | Get related records for delete confirmation |

---

## Import Formats

### Invoice Import
**Format:** `buyer_name|amount` (pipe-separated)
```
jonettan|5000
irctc|3500
```

### Expense Import
**Format:** `date|type|vendor_name|amount` (pipe-separated)
```
2024-01-15|Diesel|johney|5000
2024-01-16|Ice|irctc|2000
```

### Receipt/Payment Import
**Format:** Plain amounts (one per line)
```
5000
3500
2500
```

---

## Edge Cases & Fixes Applied

### Filters Removed
1. **Payment Source Filter** - Removed from Payments page (was `source` dropdown with Cash/Personal/Bank/Basheer/Other)
2. **Payment For Filter** - Removed from Payments page
3. **Status Filter** - Removed from Expenses page (kept in vendor_status as special options)

### Filters Added
1. **Vendor Filter** - Added to Payments page
   - Populated dynamically from `vendor_name` field in payments
   - Query param: `?vendor=<vendor_name>`
   - Clear shows all vendors

### UI Improvements
1. **Light Mode Text Darkening** - Light mode uses darker text colors for better readability:
   - `text-slate-400` becomes `#64748b`
   - `text-slate-500` becomes `#475569`

2. **Scrollable Tables** - All main tables now have:
   - Fixed height container
   - Sticky headers
   - Consistent styling across all pages

3. **Dark Mode Form Inputs** - All form elements properly styled for dark mode with explicit `dark:` classes

4. **Modal Dark Mode** - All modals properly styled for dark mode

5. **Summary Cards** - Moved to filter section where applicable for cleaner layout

### Business Logic Fixes
1. **Receipt Deletion** - Properly reverses invoice balance update
2. **Payment Deletion** - Reverses expense allocations and landing payments
3. **Invoice Editing** - Validates amount doesn't exceed landing gross_value
4. **Expense Landing Assignment** - Supports `landing_id = 'next'` to auto-assign to next future landing

---

## Final Notes for Rebuild

### Critical Implementation Order
1. **Database Migrations** - Create all tables with proper indexes and foreign keys
2. **Models** - Define relationships before building controllers
3. **Services** - Implement business logic (PaymentPostingService, ExpenseSettlementService, LandingSummaryService, InvoicePostingService, InvoiceImportService, CashSourceTrackingService)
4. **Controllers** - Build CRUD operations with filtering
5. **Views** - Use consistent table pattern with scrollable containers
6. **Layout** - Implement theme system with FOUC prevention
7. **JavaScript** - Add auto-submit filters, modals, delete confirmations

### Key Services
- `PaymentPostingService`: Handles payment creation with allocations
- `ExpenseSettlementService`: Manages expense payment tracking
- `LandingSummaryService`: Calculates landing financials
- `InvoicePostingService`: Handles receipt posting
- `InvoiceImportService`: Parses and imports invoices
- `CashSourceTrackingService`: Tracks cash utilization

### User Isolation
Every query MUST include `user_id = auth()->id()` for data isolation.

### Authentication
Uses Laravel's built-in auth with custom LoginController. Admin users have `role = 'admin'`.

### Common Patterns
- Form auto-submit on select change
- Sticky table headers
- Modal-based create/edit forms
- Delete confirmation with related records check
- Import with paste mode support
- CSV export
- Dynamic filters from database