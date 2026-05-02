# CFH Fund Management Application - Recreation Prompt

## Overview

Recreate a Laravel-based multi-tenant fund management system for fishing fleet operations. This is a comprehensive financial management application with transaction tracking, payment allocations, and real-time balance calculations.

**Database**: SQLite with user isolation
**Auth**: Email/password with role-based access (admin/user)
**Frontend**: Blade templates with Tailwind CSS

---

## 1. Project Setup

### Requirements
- PHP 8.2+
- Composer
- Node.js + npm
- SQLite (bundled with PHP)

### Initial Installation
```bash
# Create Laravel project
composer create-project laravel/laravel cfh-fund-management
cd cfh-fund-management

# Install dependencies
composer install
npm install

# Create .env from example
cp .env.example .env
php artisan key:generate

# Use SQLite - update .env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Create sqlite database
touch database/database.sqlite
```

### Admin User Seeding
- First registered user becomes admin automatically
- Default admin: admin@example.com / password

---

## 2. Database Schema

### Core Tables (14 tables)

1. **users** - Authentication & authorization
   - Fields: id, name, email, email_verified_at, password, role (admin/user), is_active, remember_token, timestamps
   - First user gets role=admin

2. **boats** - Fishing vessels
   - Fields: id, user_id, name, owner_phone, timestamps

3. **buyers** - Fish buyers
   - Fields: id, user_id, name, phone, address, notes, timestamps
   - Calculated: total_purchased, total_received, total_pending

4. **landings** - Fish catch records
   - Fields: id, user_id, boat_id, date, gross_value, status (Open/Partial/Settled), notes, timestamps
   - Status Logic:
     - Open: owner_pending == net_owner_payable
     - Partial: 0 < owner_pending < net_owner_payable
     - Settled: owner_pending == 0

5. **expenses** - Operational costs
   - Fields: id, user_id, boat_id, landing_id (nullable), date, type, vendor_name, amount, paid_amount, pending_amount, payment_status (Pending/Partial/Paid), notes, timestamps
   - Expense types seeded: Diesel, Ice, Ration, Petty Cash Advance, Unloading, Toll, Salary, Other

6. **invoices** - Buyer purchase invoices
   - Fields: id, user_id, buyer_id, boat_id, landing_id, invoice_date, original_amount, received_amount, pending_amount, status (Pending/Partial/Paid), notes, timestamps
   - Validation: original_amount cannot exceed landing gross_value

7. **receipts** - Cash received from buyers
   - Fields: id, user_id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode (Cash/GP/Bank), source (CashFromSales/PersonalFund/Bank/Other), notes, timestamps

8. **payments** - Payments to boat owners and expenses
   - Fields: id, user_id, boat_id (nullable), landing_id (nullable), date, amount, mode (Cash/GP/Bank), source (Cash/Bank), payment_for, loan_reference, vendor_name, notes, timestamps
   - Payment types seeded: Owner, Expense, Loan, Basheer, Personal, Mixed, Other

9. **payment_allocations** - Link payments to expenses/landings (polymorphic)
   - Fields: id, payment_id, allocatable_type, allocatable_id, amount, timestamps
   - Polymorphic: allocatable can be Expense or Landing

10. **transactions** - Audit trail for all financial activities (polymorphic)
    - Fields: id, user_id, type, mode, source, amount, boat_id, landing_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes, timestamps
    - Polymorphic: transactionable can be Payment or Receipt

11. **loans** - Loan records
    - Fields: id, user_id, loan_source_id, amount, date, due_date, status (Active/Repaid), interest_rate, notes, timestamps

12. **loan_sources** - Loan provider reference
    - Fields: id, user_id, name, contact, notes, timestamps
    - Seeded: Basheer, Personal, Bank, Other

13. **payment_types** - Dynamic payment type lookup
    - Fields: id, name, timestamps

14. **expense_types** - Dynamic expense type lookup
    - Fields: id, name, timestamps

---

## 3. Models & Relationships

### User
```php
// Relationships
hasMany('boats')
hasMany('landings')
hasMany('expenses')
hasMany('invoices')
hasMany('receipts')
hasMany('payments')
hasMany('transactions')
hasMany('loans')
hasMany('loan_sources')
```

### Boat
```php
$fillable = ['user_id', 'name', 'owner_phone'];
// Relationships
belongsTo('user')
hasMany('landings')
hasMany('expenses')
hasMany('invoices')
hasMany('receipts')
hasMany('payments')
```

### Landing
```php
$fillable = ['user_id', 'boat_id', 'date', 'gross_value', 'status', 'notes'];
$casts = ['date' => 'date', 'gross_value' => 'decimal:2'];
// Relationships
belongsTo('user')
belongsTo('boat')
hasMany('expenses')
hasMany('invoices')
hasMany('receipts')
hasMany('payments')
morphMany('payment_allocations', 'allocatable')
// Accessors
getTotalExpensesAttribute()
getNetOwnerPayableAttribute() // gross_value - total_expenses
getOwnerPendingAttribute() // net_owner_payable - total_owner_paid
```

### Expense
```php
$fillable = ['user_id', 'boat_id', 'landing_id', 'date', 'type', 'vendor_name', 'amount', 'paid_amount', 'pending_amount', 'payment_status', 'notes'];
$casts = ['date' => 'date', 'amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'pending_amount' => 'decimal:2'];
// Relationships
belongsTo('user')
belongsTo('boat')
belongsTo('landing')
morphMany('payment_allocations', 'allocatable')
morphMany('transactions', 'transactionable')
// Accessor: getPaymentAttribute() - retrieves related payment via allocation
```

### Invoice
```php
$fillable = ['user_id', 'buyer_id', 'boat_id', 'landing_id', 'invoice_date', 'original_amount', 'received_amount', 'pending_amount', 'status', 'notes'];
$casts = ['invoice_date' => 'date', 'original_amount' => 'decimal:2', 'received_amount' => 'decimal:2', 'pending_amount' => 'decimal:2'];
// Relationships
belongsTo('user')
belongsTo('buyer')
belongsTo('boat')
belongsTo('landing')
hasMany('receipts')
```

### Receipt
```php
$fillable = ['user_id', 'buyer_id', 'invoice_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode', 'source', 'notes'];
$casts = ['date' => 'date', 'amount' => 'decimal:2'];
// Relationships
belongsTo('user')
belongsTo('buyer')
belongsTo('invoice')
belongsTo('boat')
belongsTo('landing')
morphOne('transactions', 'transactionable')
```

### Payment
```php
$fillable = ['user_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode', 'source', 'payment_for', 'loan_reference', 'vendor_name', 'notes'];
$casts = ['date' => 'date', 'amount' => 'decimal:2'];
// Relationships
belongsTo('user')
belongsTo('boat')
belongsTo('landing')
hasMany('payment_allocations')
morphOne('transactions', 'transactionable')
```

### PaymentAllocation
```php
$fillable = ['payment_id', 'allocatable_type', 'allocatable_id', 'amount'];
$casts = ['amount' => 'decimal:2'];
// Relationships
belongsTo('payment')
morphTo('allocatable') // Expense or Landing
```

### Transaction
```php
$fillable = ['user_id', 'type', 'mode', 'source', 'amount', 'boat_id', 'landing_id', 'cash_source_receipt_id', 'transactionable_type', 'transactionable_id', 'date', 'notes'];
$casts = ['date' => 'date', 'amount' => 'decimal:2'];
// Relationships
belongsTo('user')
morphTo('transactionable') // Payment or Receipt
```

### Loan
```php
$fillable = ['user_id', 'loan_source_id', 'amount', 'date', 'due_date', 'status', 'interest_rate', 'notes'];
$casts = ['date' => 'date', 'due_date' => 'date', 'amount' => 'decimal:2', 'interest_rate' => 'decimal:5,2'];
// Relationships
belongsTo('user')
belongsTo('loan_source')
hasMany('loan_repayments')
```

### LoanSource
```php
$fillable = ['user_id', 'name', 'contact', 'notes'];
// Relationships
belongsTo('user')
hasMany('loans')
```

### PaymentType
```php
$fillable = ['name'];
```

### ExpenseType
```php
$fillable = ['name'];
```

---

## 4. Routes

### Authentication
```
GET  /login      - Show login form
POST /login      - Process login
GET  /logout     - Logout
```

### Dashboard
```
GET  /           - Dashboard (auth required)
```

### Resource Routes (all auth required)
```
boats           - CRUD for fishing boats
landings        - CRUD for fish catch records
expenses        - CRUD for expenses + import/export
invoices        - CRUD for buyer invoices + import
buyers          - CRUD for fish buyers
receipts        - CRUD for cash receipts + utilization
payments       - CRUD for payments + types management
loans           - CRUD for loans
unlinked-expenses - CRUD for expenses without landing
```

### Custom Routes
```
GET  /bank                    - Bank management dashboard
POST /bank/deposits           - Record cash-to-bank deposit
GET  /reports                 - Reports & control panel
GET  /cash/utilization        - Cash utilization breakdown
POST /receipts/{receipt}/utilization - Update cash utilization
POST /expenses/import         - Import expenses from CSV
POST /payments/types          - Add new payment type
GET  /payments/landings/{boat}     - API: get landings by boat
GET  /payments/expenses/{boat}     - API: get expenses by boat
GET  /payments/landing/{landing}   - API: get landing expenses
```

### Admin Only Routes
```
users (resource) - User management
```

---

## 5. Controllers

### DashboardController
- index() - Display dashboard with financial summaries

### BoatController
- Standard RESTful CRUD operations

### LandingController
- Standard RESTful CRUD operations
- Show page displays landing summary (gross_value, expenses, net payable, owner pending, status)

### ExpenseController
- Standard RESTful CRUD operations
- Additional methods:
  - showImport() - Display CSV import form
  - import() - Process CSV import
  - export() - Export to CSV
- List displays vendor name from payment's vendor_name if payment_for == "Other", else expense vendor_name

### PaymentController
- Standard RESTful CRUD operations
- Additional methods:
  - showImport() - Display import form
  - import() - Process import
  - export() - Export to CSV
  - getLandingsByBoat() - API endpoint
  - getExpensesByBoat() - API endpoint
  - getLandingExpenses() - API endpoint
  - storeType() - Add new payment type
- Create/Edit page features:
  - Optional boat selection
  - Optional landing selection (shows date, net_owner_payable, owner_pending)
  - Landing/expense summary display
  - Amount input with balance validation
  - Payment mode selection (Cash/GP/Bank) with available balance
  - Dynamic payment_for selection from PaymentType table
  - Conditional fields based on payment_for:
    - "Loan": loan_reference field
    - "Other": vendor_name field (required)
  - Dynamic allocation to multiple expenses/landings

### InvoiceController
- Standard RESTful CRUD operations
- Import functionality
- Validation: original_amount <= landing gross_value

### ReceiptController
- Standard RESTful CRUD operations
- Additional methods:
  - utilization() - Show cash utilization breakdown
  - updateUtilization() - Update cash utilization
- Store updates related invoice payment status

### BuyerController
- Standard RESTful CRUD operations
- Index displays calculated totals (total_purchased, total_received, total_pending)

### LoanController
- Standard RESTful CRUD operations

### BankController
- index() - Bank management dashboard
- storeDeposit() - Record cash-to-bank deposit

### ReportController
- index() - Reports and control panel

### UserController (Admin only)
- Standard RESTful CRUD operations

---

## 6. Service Layer

### PaymentPostingService
Post payment creation with:
1. Create Payment record (DB transaction wrapper)
2. Create PaymentAllocation records linking to expenses/landings
3. Create Transaction record
4. Update landing status based on owner_pending
5. Update expense status for allocated expenses

### ExpenseSettlementService
- updateExpenseStatus() - Recalculate paid_amount, pending_amount, payment_status

### LandingSummaryService
- getSummary() - Return array with:
  - total_expenses
  - total_expenses_paid
  - net_owner_payable
  - total_owner_paid
  - owner_pending
  - status
  - total_invoices, total_received, total_buyer_pending
- updateLandingStatus() - Update status based on owner_pending

### CashSourceTrackingService
- getAvailableCashReceipts() - Return receipts with available balance
- getTotalAvailableCash() - Sum of available balances
- getBankBalance() - Calculate from transactions
- getUtilizedAmount(receipt_id) - Sum payments referencing receipt
- getDepositedAmount(receipt_id) - Sum deposited amounts

### InvoiceImportService
- import() - Parse CSV, validate rows, create invoices, return results

### DashboardSummaryService
- getTotalRevenue()
- getTotalExpenses()
- getTotalOwnerPayments()
- getPendingOwnerPayments()
- getCashInHand()
- getBankBalance()
- getRecentTransactions()

---

## 7. Business Rules

### Balance Validation
- **Cash Mode**: Payment amount <= available cash receipts balance
- **Bank Mode**: Payment amount <= bank balance
- If payment linked to receipt: amount <= receipt available balance

### Status Calculations
- **Landing Status**: Based on owner_pending vs net_owner_payable
- **Expense Status**: Based on paid_amount vs amount
- **Invoice Status**: Based on received_amount vs original_amount
- **Receipt Status**: Based on amount - utilized - deposited

### User Isolation
- ALL queries must filter by `auth()->id()` or `user_id`
- No user can access another user's data

---

## 8. Validation Rules

### StorePaymentRequest
```php
'boat_id' => 'nullable|exists:boats,id'
'landing_id' => 'nullable|exists:landings,id'
'date' => 'required|date'
'amount' => 'required|numeric|min:0.01'
'mode' => 'required|in:Cash,GP,Bank'
'source' => 'required|in:Cash,Bank'
'payment_for' => 'required|in:PaymentType names'
'loan_reference' => 'nullable|string|max:255'
'vendor_name' => 'nullable|string|max:255' // Required if payment_for == 'Other'
'notes' => 'nullable|string'
'allocations' => 'nullable|array'
'allocations.*.type' => 'required_with:allocations|in:expense,landing'
'allocations.*.id' => 'required_with:allocations'
'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01'

// Custom validation:
// - vendor_name required if payment_for == 'Other'
// - Total allocation amount <= payment amount
// - Payment amount <= available balance
```

### StoreExpenseRequest
```php
'boat_id' => 'required|exists:boats,id'
'landing_id' => 'nullable|exists:landings,id'
'date' => 'required|date'
'type' => 'required|in:ExpenseType names'
'vendor_name' => 'nullable|string|max:255'
'amount' => 'required|numeric|min:0.01'
'notes' => 'nullable|string'
```

### StoreInvoiceRequest
```php
'buyer_id' => 'required|exists:buyers,id'
'boat_id' => 'required|exists:boats,id'
'landing_id' => 'required|exists:landings,id'
'invoice_date' => 'required|date'
'original_amount' => 'required|numeric|min:0.01'
// Custom: original_amount <= landing.gross_value
'notes' => 'nullable|string'
```

### StoreReceiptRequest
```php
'buyer_id' => 'required|exists:buyers,id'
'invoice_id' => 'required|exists:invoices,id'
'boat_id' => 'required|exists:boats,id'
'landing_id' => 'required|exists:landings,id'
'date' => 'required|date'
'amount' => 'required|numeric|min:0.01'
'mode' => 'required|in:Cash,GP,Bank'
'source' => 'nullable|in:CashFromSales,PersonalFund,Bank,Other'
'notes' => 'nullable|string'
```

---

## 9. Views Structure

```
resources/views/
├── layouts/
│   ├── main.blade.php      # Main layout with sidebar navigation
│   ├── guest.blade.php     # Guest layout (login)
│   └── app.blade.php
├── auth/
│   └── login.blade.php
├── dashboard/
│   └── index.blade.php
├── boats/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── landings/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php      # Shows landing summary
├── expenses/
│   ├── index.blade.php     # Shows vendor name (from payment if Other type)
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── import.blade.php
│   └── show.blade.php
├── payments/
│   ├── index.blade.php     # Shows vendor_name for "Other" type
│   ├── create.blade.php    # Dynamic allocation UI
│   ├── edit.blade.php
│   └── show.blade.php      # Shows vendor_name for "Other" type
├── invoices/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── import.blade.php
│   └── show.blade.php
├── receipts/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── utilization.blade.php
├── loans/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── buyers/
│   ├── index.blade.php      # Shows calculated totals
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── cash/
│   ├── show.blade.php
│   ├── deposit.blade.php
│   ├── edit-transaction.blade.php
│   └── utilization.blade.php
├── bank-management/
│   └── index.blade.php
├── reports/
│   ├── index.blade.php
│   ├── cash-report.blade.php
│   ├── bank-report.blade.php
│   └── settlement-pdf.blade.php
├── users/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── (no show - edit is main)
├── unlinked-expenses/
│   ├── index.blade.php
│   ├── edit.blade.php
│   └── (create is same as expenses create)
└── components/
    ├── delete-confirm.blade.php
    ├── delete-modal.blade.php
    ├── delete-modal-scripts.blade.php
    └── paste-import.blade.php
```

---

## 10. Key UI Features

### Payments Create Page
1. **Boat Selection** (optional dropdown)
2. **Landing Selection** (optional, shows summary when selected)
3. **Landing Summary Display**: gross_value, expenses, net payable, amount paid, pending
4. **Amount Input** with "Remaining After Payment" validation
5. **Payment Mode** selection (Cash/GP/Bank) with available balance display
6. **Payment For** selection (dynamic from PaymentType table)
7. **Conditional Fields**:
   - "Loan" selected: show loan_reference field
   - "Other" selected: show vendor_name field (required)
8. **Allocation Section**: Select multiple expenses/landings, auto-calculate allocated amounts

### Expenses Index Page
- Vendor column shows:
  1. Payment's vendor_name if payment_for == "Other"
  2. Otherwise, expense's vendor_name
  3. Otherwise, "-"

### Payments Index Page
- For "Other" type: display vendor_name
- For "Loan" type: display loan_reference
- For allocated expenses: show expense vendor name

---

## 11. Workflows

### Landing & Payment Settlement
1. Create Landing (date, gross_value, boat)
2. Add Expenses against landing
3. Create Invoice from buyer
4. Create Receipt from buyer payment
5. Create Owner Payment (allocate to landing)
6. Landing status updates (Open → Partial → Settled)

### Expense Payment
1. Create Expense
2. Create Payment (payment_for = 'Expense', allocate to expense)
3. Expense status updates (Pending → Partial → Paid)

### Cash to Bank
1. Create Cash Receipt
2. Record Bank Deposit
3. Bank balance increases

### Loan Payment
1. Create Loan
2. Create Payment (payment_for = 'Loan', loan_reference, nullable boat_id)
3. Track repayment

---

## 12. Core Features Summary

- Multi-user with data isolation
- Transaction-based operations with audit trail
- Real-time settlement and payment status
- Balance validation (prevents overspending)
- Polymorphic relationships for flexible linking
- Service-oriented business logic
- Dynamic payment/expense types
- CSV import/export capability
- Cash utilization tracking
- Bank balance management
- Loan tracking and repayment

---

## Notes

- Design/theme will be detailed separately in design.md
- Focus on functionality implementation first
- Use Tailwind CSS for styling
- All monetary fields use decimal:12,2 precision
- Status fields use strings, not booleans
- Use DB::transaction() for multi-step operations
- Use FormRequest classes for validation
