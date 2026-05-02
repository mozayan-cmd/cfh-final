# CFH Fund Management Application - Comprehensive Codebase Analysis

**Generated:** April 16, 2026  
**Version:** 1.0  
**Application Type:** Laravel Multi-Tenant Fund/Fishing Fleet Management System

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Database Schema](#database-schema)
3. [Models & Relationships](#models--relationships)
4. [Controllers & Routes](#controllers--routes)
5. [Service Layer](#service-layer)
6. [Key Business Logic](#key-business-logic)
7. [Recent Changes & Bug Fixes](#recent-changes--bug-fixes)
8. [Configuration & Environment](#configuration--environment)

---

## Executive Summary

The CFH Fund Management application is a Laravel-based system designed to manage fishing fleet finances, including:
- **Landings Management**: Track fish landing dates and gross values
- **Expense Tracking**: Record operational expenses (diesel, ice, salary, etc.)
- **Invoice & Receipt Management**: Handle buyer invoices and payment receipts
- **Payment Allocation**: Distribute payments between owner settlements and expense payments
- **Cash Flow Tracking**: Monitor cash in hand, bank balances, and cash-to-bank deposits
- **Loan Management**: Track loans from various sources (Basheer, Personal, Others) and repayments
- **Multi-user Support**: User-specific data isolation with admin capabilities

**Architecture Pattern**: Service-oriented with transaction-based operations  
**Database**: SQLite (primary) with user-specific isolation  
**Key Features**: Transaction logging, status tracking, expense settlement, cash source tracking

---

## Database Schema

### Core Tables

#### `users`
**Purpose**: User authentication and authorization  
**Key Columns**:
- `id` (PK): Primary key
- `name`: User's full name
- `email`: Email address (unique)
- `password`: Hashed password
- `role`: 'admin' or regular user
- `is_active`: Boolean flag for account status
- `remember_token`: For "remember me" functionality
- `created_at`, `updated_at`: Timestamps

**Business Rules**:
- First user automatically becomes admin
- Admins control user management, backups, and system settings
- Active status controls login ability

---

#### `boats`
**Purpose**: Represents fishing boats  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Owner user
- `name`: Boat name (e.g., "Boat A", "Fishing Vessel 1")
- `owner_phone`: Contact number (nullable)
- `created_at`, `updated_at`: Timestamps

**Relationships**:
- Boats have many landings, expenses, invoices, receipts, payments, transactions

---

#### `buyers`
**Purpose**: Track fish buyers  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Buyer record owner
- `name`: Buyer's business or personal name
- `phone`: Contact number (nullable)
- `address`: Business address (nullable)
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Calculated Attributes**:
- `total_purchased`: Sum of all original invoice amounts
- `total_received`: Sum of all received amounts
- `total_pending`: Sum of all pending amounts

---

#### `landings`
**Purpose**: Record fishing trip landing dates and gross values  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `boat_id` (FK): Associated boat
- `date`: Landing date
- `gross_value` (decimal:12,2): Total catch value
- `notes`: Additional details (nullable)
- `status` (enum): 'Open', 'Partial', 'Settled'
- `created_at`, `updated_at`: Timestamps

**Status Rules**:
- **Open**: No payments made against landing
- **Partial**: Some amount paid to boat owner
- **Settled**: All net payable amount (gross - expenses) paid to owner

**Calculated Values**:
- `total_expenses`: Sum of associated expenses
- `total_expenses_paid`: Sum of paid portion of expenses
- `net_owner_payable`: gross_value - total_expenses
- `total_owner_paid`: Sum of owner payments (payment_for != 'Expense')
- `owner_pending`: net_owner_payable - total_owner_paid
- `total_invoices`: Sum of invoice original amounts
- `total_received`: Sum of invoice received amounts
- `total_buyer_pending`: Sum of invoice pending amounts

---

#### `expenses`
**Purpose**: Track operational expenses against boats/landings  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `boat_id` (FK): Associated boat
- `landing_id` (FK, nullable): Linked landing (can be unlinked)
- `date`: Expense date
- `type` (string, not enum): Expense category (linked to expense_types table)
- `vendor_name`: Supplier name (nullable)
- `amount` (decimal:12,2): Total expense amount
- `paid_amount` (decimal:12,2): Amount already paid (default: 0)
- `pending_amount` (decimal:12,2): Remaining to pay
- `payment_status` (enum): 'Pending', 'Partial', 'Paid'
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Key Features**:
- Expenses can be unlinked from landings (landing_id nullable)
- Supports expense type management via expense_types table
- Tracks payment settlement through PaymentAllocation
- Payment status auto-calculated based on paid_amount vs amount

**Expense Types** (seeded):
- Diesel
- Ice
- Ration
- Petty Cash Advance
- Unloading
- Toll
- Salary
- Other

---

#### `invoices`
**Purpose**: Track buyer invoices for fish sales  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `buyer_id` (FK): Buyer purchasing fish
- `boat_id` (FK): Source boat
- `landing_id` (FK): Associated landing
- `invoice_date`: Date of invoice
- `original_amount` (decimal:12,2): Total invoice amount
- `received_amount` (decimal:12,2): Amount received from buyer (default: 0)
- `pending_amount` (decimal:12,2): Remaining amount due
- `status` (enum): 'Pending', 'Partial', 'Paid'
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Status Rules**:
- **Pending**: No payment received
- **Partial**: Some payment received
- **Paid**: Full amount received (pending_amount <= 0)

**Validation**:
- Invoice amount cannot exceed landing gross_value

---

#### `receipts`
**Purpose**: Track payment receipts from buyers  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `buyer_id` (FK): Paying buyer
- `invoice_id` (FK): Associated invoice
- `boat_id` (FK): Source boat
- `landing_id` (FK): Associated landing
- `date`: Receipt date
- `amount` (decimal:12,2): Payment amount
- `mode` (enum): 'Cash', 'GP' (gold/commodity), 'Bank'
- `source` (enum, nullable): 'CashFromSales', 'PersonalFund', 'Bank', 'Other'
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Purpose**: Records cash inflow from buyer payments; triggers transaction creation

---

#### `payments`
**Purpose**: Track payments made out for expenses and owner settlements  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `boat_id` (FK, nullable): Associated boat (nullable for loan repayments)
- `landing_id` (FK, nullable): Associated landing (nullable)
- `date`: Payment date
- `amount` (decimal:12,2): Total payment amount
- `mode` (enum): 'Cash', 'GP', 'Bank'
- `source` (enum): 'CashFromSales', 'PersonalFund', 'Bank', 'Other', 'Basheer'
- `payment_for` (string, not enum): Category of payment (linked to payment_types table)
- `loan_reference` (string, nullable): Reference if payment is loan-related
- `vendor_name` (string, nullable): Vendor details
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Recent Changes** (2026-03-30):
- `boat_id` made nullable to support loan repayments
- `loan_reference` field added to track loan-related payments

---

#### `payment_allocations`
**Purpose**: Link payments to their targets (expenses or landings)  
**Key Columns**:
- `id` (PK): Primary key
- `payment_id` (FK): Associated payment
- `allocatable_type` (string): Polymorphic type ('App\Models\Expense' or 'App\Models\Landing')
- `allocatable_id` (bigint): ID of allocatable entity
- `amount` (decimal:12,2): Allocated amount
- `created_at`, `updated_at`: Timestamps

**Purpose**: Implements polymorphic allocation allowing a single payment to split across multiple expenses or owner settlements

---

#### `transactions`
**Purpose**: Complete audit log of all cash movements  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Record owner
- `type` (enum): 'Receipt' or 'Payment'
- `mode` (enum): 'Cash', 'GP', 'Bank'
- `source` (enum, nullable): Money source
- `amount` (decimal:12,2): Transaction amount
- `boat_id` (FK, nullable): Associated boat
- `landing_id` (FK, nullable): Associated landing
- `buyer_id` (FK, nullable): Associated buyer
- `invoice_id` (FK, nullable): Associated invoice
- `cash_source_receipt_id` (FK, nullable): Source receipt for cash deposits (2026-03-30 addition)
- `transactionable_type` (string, nullable): Polymorphic type
- `transactionable_id` (bigint, nullable): Polymorphic ID
- `date`: Transaction date
- `notes`: Additional details (nullable)
- `created_at`, `updated_at`: Timestamps

**Purpose**: Maintains complete audit trail; used for cash flow calculations and reconciliation

**Recent Addition** (2026-03-30):
- `cash_source_receipt_id` field added to track source of cash-to-bank deposits
- Allows linking cash deposits back to original receipt

---

#### `loans`
**Purpose**: Track borrowed funds and repayments  
**Key Columns**:
- `id` (PK): Primary key
- `user_id` (FK): Borrower
- `source` (enum): 'Basheer', 'Personal', 'Others'
- `amount` (decimal:15,2): Original loan amount
- `repaid_amount` (decimal:15,2, nullable): Amount repaid so far
- `date`: Loan date
- `mode` (string): Payment mode ('Cash', 'GP', 'Bank')
- `notes`: Additional details (nullable)
- `repaid_at` (timestamp, nullable): Date when fully repaid
- `created_at`, `updated_at`: Timestamps

**Status Methods**:
- `isOutstanding()`: Returns true if repaid_at is null
- `isRepaid()`: Returns true if repaid_at is set
- `getOutstandingAmount()`: Calculates amount - repaid_amount

---

#### `expense_types`
**Purpose**: Lookup table for expense categories  
**Key Columns**:
- `id` (PK)
- `name` (string, unique): Type name
- `created_at`, `updated_at`

**Seeded Values**:
- Diesel, Ice, Ration, Petty Cash Advance, Unloading, Toll, Salary, Other

**Note**: Expenses reference this table by name, not by ID

---

#### `payment_types`
**Purpose**: Lookup table for payment categories  
**Key Columns**:
- `id` (PK)
- `name` (string, unique): Type name
- `created_at`, `updated_at`

**Purpose**: Allows flexible payment categorization (Owner, Expense, Loan, Mixed, etc.)

---

#### `loan_sources`
**Purpose**: Lookup table for loan source categories  
**Key Columns**:
- `id` (PK)
- `name` (string, unique): Source name
- `created_at`, `updated_at`

**Purpose**: Extends loan source enum with user-defined sources

---

### Support Tables

#### `cache`, `jobs`, `failed_jobs`, `sessions`
- Standard Laravel tables for caching, queuing, and session management

#### `telescope_entries`, `telescope_entries_tags`
- Laravel Telescope debugging tables (for local/debug environments)

---

## Models & Relationships

### Model Hierarchy

#### User (Authenticatable)
```php
class User extends Authenticatable {
    // Fillable: name, email, password, role, is_active
    
    // Relations
    hasMany(Landing)
    hasMany(Invoice)
    hasMany(Expense)
    hasMany(Receipt)
    hasMany(Payment)
    hasMany(Boat)
    hasMany(Buyer)
    hasMany(Loan)
    hasMany(Transaction)
    
    // Methods
    isAdmin(): bool           // id === 1 OR role === 'admin'
    isActive(): bool          // is_active === true/1/'1'
}
```

**Key Features**:
- Automatic admin assignment for first user
- Role-based access control (admin middleware routes)
- Activity tracking across all models

---

#### Boat
```php
class Boat extends Model {
    // Fillable: user_id, name, owner_phone
    
    // Relations
    hasMany(Landing)
    hasMany(Expense)
    hasMany(Invoice)
    hasMany(Receipt)
    hasMany(Payment)
    hasMany(Transaction)
    
    // Method
    getRelatedRecords(): array  // Returns count and sum of related entities
}
```

---

#### Buyer
```php
class Buyer extends Model {
    // Fillable: user_id, name, phone, address, notes
    
    // Relations
    hasMany(Invoice)
    hasMany(Receipt)
    hasMany(Transaction)
    
    // Attributes
    getTotalPurchasedAttribute(): float
    getTotalReceivedAttribute(): float
    getTotalPendingAttribute(): float
    
    // Method
    getRelatedRecords(): array
}
```

---

#### Landing
```php
class Landing extends Model {
    // Fillable: user_id, boat_id, date, gross_value, notes, status
    // Casts: date (date), gross_value (decimal:2)
    
    // Relations
    belongsTo(Boat)
    hasMany(Expense)
    hasMany(Invoice)
    hasMany(Receipt)
    hasMany(Payment)
    hasMany(Transaction)
    
    // Calculated Attributes
    getTotalExpensesAttribute(): float
    getTotalExpensesPaidAttribute(): float
    getNetOwnerPayableAttribute(): float     // gross_value - total_expenses
    getTotalOwnerPaidAttribute(): float      // payments where payment_for != 'Expense'
    getOwnerPendingAttribute(): float        // net_owner_payable - total_owner_paid
    getTotalInvoicesAttribute(): float
    getTotalReceivedAttribute(): float
    getTotalBuyerPendingAttribute(): float
    
    // Method
    getRelatedRecords(): array
}
```

---

#### Expense
```php
class Expense extends Model {
    // Fillable: user_id, boat_id, landing_id, date, type, vendor_name, 
    //           amount, paid_amount, pending_amount, payment_status, notes
    // Casts: date (date), amount/paid_amount/pending_amount (decimal:2)
    
    // Relations
    belongsTo(User)
    belongsTo(Boat)
    belongsTo(Landing)
    morphMany(PaymentAllocation, 'allocatable')
    morphMany(Transaction, 'transactionable')
    
    // Attribute
    getPayment(): ?Payment  // Gets related payment via PaymentAllocation
    getAllocatedAmountAttribute(): float
    
    // Static Method
    types(): array  // Returns ExpenseType names
    
    // Method
    getRelatedRecords(): array
}
```

**Key Feature**: Can be unlinked from landings and tracked separately (used for handling expenses that arrive before/after expected landing)

---

#### Invoice
```php
class Invoice extends Model {
    // Fillable: user_id, buyer_id, boat_id, landing_id, invoice_date,
    //           original_amount, received_amount, pending_amount, status, notes
    // Casts: invoice_date (date), amounts (decimal:2)
    
    // Relations
    belongsTo(User)
    belongsTo(Buyer)
    belongsTo(Boat)
    belongsTo(Landing)
    hasMany(Receipt)
    hasMany(Transaction)
    
    // Method
    getRelatedRecords(): array
}
```

---

#### Receipt
```php
class Receipt extends Model {
    // Fillable: user_id, buyer_id, invoice_id, boat_id, landing_id,
    //           date, amount, mode, source, notes
    // Casts: date (date), amount (decimal:2)
    
    // Relations
    belongsTo(User)
    belongsTo(Buyer)
    belongsTo(Invoice)
    belongsTo(Boat)
    belongsTo(Landing)
    morphOne(Transaction, 'transactionable')
    
    // Method
    getRelatedRecords(): array
}
```

---

#### Payment
```php
class Payment extends Model {
    // Fillable: user_id, boat_id, landing_id, date, amount, mode,
    //           source, payment_for, loan_reference, notes, vendor_name
    // Casts: date (date), amount (decimal:2)
    
    // Relations
    belongsTo(User)
    belongsTo(Boat)           // Nullable (for loan repayments)
    belongsTo(Landing)
    hasMany(PaymentAllocation, 'allocations')
    morphOne(Transaction, 'transactionable')
    
    // Method
    getRelatedRecords(): array
}
```

---

#### PaymentAllocation (Polymorphic)
```php
class PaymentAllocation extends Model {
    // Fillable: payment_id, allocatable_type, allocatable_id, amount
    // Casts: amount (decimal:2)
    
    // Relations
    belongsTo(Payment)
    morphTo(Allocatable)  // Can be Expense or Landing
}
```

---

#### Transaction (Audit Log)
```php
class Transaction extends Model {
    // Fillable: user_id, type, mode, source, amount, boat_id, landing_id,
    //           buyer_id, invoice_id, cash_source_receipt_id,
    //           transactionable_type, transactionable_id, date, notes
    // Casts: date (date), amount (decimal:2)
    
    // Relations
    belongsTo(User)
    belongsTo(Boat)
    belongsTo(Landing)
    belongsTo(Buyer)
    belongsTo(Invoice)
    belongsTo(Receipt, 'cash_source_receipt_id', 'cashSourceReceipt')  // NEW (2026-03-30)
    morphTo(Transactionable)
}
```

---

#### Loan
```php
class Loan extends Model {
    // Fillable: user_id, source, amount, repaid_amount, date, mode, notes, repaid_at
    // Casts: amount/repaid_amount (decimal:2), date (date), mode (string), repaid_at (datetime)
    
    // Relations
    belongsTo(User)
    
    // Scopes
    outstanding()
    bySource(string $source)
    bySources(array $sources)
    
    // Methods
    isOutstanding(): bool
    isRepaid(): bool
    markAsRepaid(): void
    addRepayment(float $amount): void
    
    // Attribute
    getOutstandingAmountAttribute(): float  // amount - repaid_amount
}
```

---

#### ExpenseType
```php
class ExpenseType extends Model {
    // Fillable: name
    hasMany(Expense, 'type', 'name')  // Links by name, not ID
}
```

---

#### PaymentType
```php
class PaymentType extends Model {
    // Fillable: name
    hasMany(Payment, 'payment_for', 'name')
}
```

---

#### LoanSource
```php
class LoanSource extends Model {
    // Fillable: name
    hasMany(Loan, 'source', 'name')
}
```

---

## Controllers & Routes

### Route Structure

**Authentication Routes** (Guest/Public):
```
GET    /               → DashboardController@index (redirects if not auth)
GET    /login          → LoginController@showLoginForm
POST   /login          → LoginController@login
```

**Authenticated Routes** (Protected by 'auth' middleware):

#### Boats
```
GET    /boats           → BoatController@index
POST   /boats           → BoatController@store
GET    /boats/{id}      → BoatController@show
PUT    /boats/{id}      → BoatController@update
DELETE /boats/{id}      → BoatController@destroy
```

#### Buyers
```
GET    /buyers          → BuyerController@index
POST   /buyers          → BuyerController@store
GET    /buyers/{id}     → BuyerController@show
PUT    /buyers/{id}     → BuyerController@update
DELETE /buyers/{id}     → BuyerController@destroy
```

#### Landings (Full CRUD + Custom)
```
GET    /landings                              → LandingController@index
POST   /landings                              → LandingController@store
GET    /landings/{id}                         → LandingController@show
GET    /landings/{id}/edit                    → LandingController@edit
PUT    /landings/{id}                         → LandingController@update
DELETE /landings/{id}                         → LandingController@destroy
POST   /landings/{id}/attach-expenses         → LandingController@attachExpenses
```

#### Invoices (Import + Standard CRUD)
```
GET    /invoices                              → InvoiceController@index
GET    /invoices/create                       → InvoiceController@create
POST   /invoices                              → InvoiceController@store
GET    /invoices/{id}                         → InvoiceController@show
PUT    /invoices/{id}                         → InvoiceController@update
DELETE /invoices/{id}                         → InvoiceController@destroy
GET    /invoices/import                       → InvoiceController@import
POST   /invoices/preview                      → InvoiceController@preview
POST   /invoices/process-import               → InvoiceController@processImport
GET    /invoices/landings/{boat}              → InvoiceController@getLandingsByBoat (API)
GET    /invoices/pending-landings/{boat}      → InvoiceController@getLandingDatesWithPendingInvoices (API)
GET    /invoices/export                       → InvoiceController@export
```

#### Expenses (Import + Standard CRUD)
```
GET    /expenses                              → ExpenseController@index
POST   /expenses                              → ExpenseController@store
GET    /expenses/{id}                         → ExpenseController@show
PUT    /expenses/{id}                         → ExpenseController@update
DELETE /expenses/{id}                         → ExpenseController@destroy
GET    /expenses/export                       → ExpenseController@export
GET    /expenses/import                       → ExpenseController@import
POST   /expenses/import/preview               → ExpenseController@previewImport
POST   /expenses/import/process               → ExpenseController@processImport
POST   /expenses/types                        → ExpenseController@storeType
```

#### Receipts (Import + Standard CRUD + API)
```
GET    /receipts                              → ReceiptController@index
GET    /receipts/create                       → ReceiptController@create
POST   /receipts                              → ReceiptController@store
GET    /receipts/{id}                         → ReceiptController@show
GET    /receipts/{id}/edit                    → ReceiptController@edit
PUT    /receipts/{id}                         → ReceiptController@update
DELETE /receipts/{id}                         → ReceiptController@destroy
GET    /receipts/export                       → ReceiptController@export
GET    /receipts/import                       → ReceiptController@import
POST   /receipts/import/preview               → ReceiptController@previewImport
POST   /receipts/import/process               → ReceiptController@processImport
GET    /receipts/invoices/{buyer}             → ReceiptController@getInvoicesByBuyer (API)
GET    /receipts/api/buyers                   → ReceiptController@getBuyersByBoat (API)
GET    /receipts/api/invoices                 → ReceiptController@getInvoicesByBuyerAndLanding (API)
```

#### Payments (Import + Standard CRUD + API)
```
GET    /payments                              → PaymentController@index
GET    /payments/create                       → PaymentController@create
POST   /payments                              → PaymentController@store
GET    /payments/{id}                         → PaymentController@show
GET    /payments/{id}/edit                    → PaymentController@edit
PUT    /payments/{id}                         → PaymentController@update
DELETE /payments/{id}                         → PaymentController@destroy
GET    /payments/export                       → PaymentController@export
GET    /payments/import                       → PaymentController@import
POST   /payments/import/preview               → PaymentController@previewImport
POST   /payments/import/process               → PaymentController@processImport
POST   /payments/types                        → PaymentController@storeType
GET    /payments/landings/{boat}              → PaymentController@getLandingsByBoat (API)
GET    /payments/landing/{landing}            → PaymentController@getLandingExpenses (API)
GET    /payments/expenses/{boat}              → PaymentController@getExpensesByBoat (API)
```

#### Cash Management (Prefix: /cash)
```
GET    /cash/utilization                      → CashController@utilization
GET    /cash/deposit                          → CashController@createDeposit
POST   /cash/deposit                          → CashController@storeDeposit
GET    /cash/transaction/{id}/edit            → CashController@editTransaction
PUT    /cash/transaction/{id}                 → CashController@updateTransaction
DELETE /cash/transaction/{id}                 → CashController@destroyTransaction
GET    /cash/receipt/{id}                     → CashController@show
GET    /cash/api/available-receipts           → CashController@getAvailableReceipts (API)
GET    /cash/report                           → CashReportController@cashReport
GET    /cash/bank-report                      → CashReportController@bankReport
```

#### Loans (Prefix: /loans)
```
GET    /loans/                                → LoanController@index
GET    /loans/create                          → LoanController@create
POST   /loans/                                → LoanController@store
POST   /loans/types                           → LoanController@storeType
POST   /loans/{id}/repay                      → LoanController@repay
```

#### Reports (Prefix: /reports)
```
GET    /reports/                              → ReportController@index
GET    /reports/generate                      → ReportController@generateReport
```

#### Backups (Admin-only, Prefix: /backups)
```
GET    /backups/                              → BackupController@index
POST   /backups/create                        → BackupController@create
POST   /backups/clear                         → BackupController@clear
POST   /backups/clear-user/{user}             → BackupController@clear
GET    /backups/download/{filename}           → BackupController@download
POST   /backups/restore                       → BackupController@restore
DELETE /backups/{filename}                    → BackupController@destroy
```

#### Users (Admin-only)
```
GET    /users                                 → UserController@index
GET    /users/create                          → UserController@create
POST   /users                                 → UserController@store
GET    /users/{id}                            → UserController@show
GET    /users/{id}/edit                       → UserController@edit
PUT    /users/{id}                            → UserController@update
DELETE /users/{id}                            → UserController@destroy
POST   /users/{id}/toggle-active              → UserController@toggleActive
```

#### Unlinked Expenses (Admin-only)
```
GET    /unlinked-expenses                     → UnlinkedExpenseController@index
GET    /unlinked-expenses/{id}/edit           → UnlinkedExpenseController@edit
PUT    /unlinked-expenses/{id}                → UnlinkedExpenseController@update
```

#### API Endpoint (Generic)
```
GET    /api/related-records/{model}/{id}      → Returns related records for any model
```

---

### Key Controller Details

#### PaymentController
**Injected Services**:
- PaymentPostingService
- ExpenseSettlementService
- LandingSummaryService
- CashSourceTrackingService

**Key Methods**:
- `index()`: Lists payments with filtering by boat, landing, mode, source, payment_for
- `create()`: Shows payment creation form; loads available cash receipts and bank balance
- `store()`: Validates cash/bank balance and posts payment via PaymentPostingService
- `show()`: Displays payment details with allocations
- `getLandingsByBoat()`: API endpoint returning landing summary data
- `getExpensesByBoat()`: API endpoint returning pending expenses
- `getLandingExpenses()`: API endpoint returning expenses for specific landing

**Balance Validation Logic** (in store):
```
If mode === 'Cash' AND cash_source_receipt_id specified:
  - Calculate: available = receipt.amount - utilized - deposited
  - Verify: amount <= available

If mode IN ['Bank', 'GP']:
  - Verify: amount <= bank_balance (from CashSourceTrackingService)

If mode === 'Cash' AND no receipt_id:
  - Verify: amount <= total_available_cash
```

---

#### InvoiceController
**Key Methods**:
- `index()`: Lists invoices with filtering and pending-only option
- `create()`: Shows invoice creation form
- `store()`: Creates invoice with initial values (received_amount=0, pending_amount=original_amount)
- `update()`: Updates invoice; validates against landing gross_value
- `import()`: Shows import form
- `preview()`: Parses CSV/paste data with buyer name | amount format
- `processImport()`: Processes validated invoice batch import

**Status Calculation**:
```
If pending_amount <= 0:
  status = 'Paid'
Else if received_amount > 0:
  status = 'Partial'
Else:
  status = 'Pending'
```

---

#### ExpenseController
**Key Methods**:
- `index()`: Lists expenses with filtering by boat, landing, type, payment_status
- `store()`: Creates expense with landing resolution logic
  - Empty landing_id: remains unlinked
  - landing_id === 'next': finds next future landing for boat
  - Specific landing_id: validates it belongs to boat
- `update()`: Updates and recalculates status
- `destroy()`: Prevents deletion if payment allocations exist
- `storeType()`: Adds new expense type

---

#### ReceiptController
**Key Methods**:
- `index()`: Lists receipts with buyer breakdown when boat_id filtered
- `store()`: Records receipt via InvoicePostingService; validates invoice ownership
- `getInvoicesByBuyer()`: API returning pending/partial invoices for buyer
- `getBuyersByBoat()`: API returning buyers with pending invoices for boat/landing
- `getInvoicesByBuyerAndLanding()`: API for cascading dropdown filtering

---

#### CashController
**Key Methods**:
- `utilization()`: Dashboard showing cash receipt utilization with deposits and payments
- `createDeposit()`: Form for depositing cash to bank
- `storeDeposit()`: Validates cash balance and creates transaction
- `show()`: Details of specific cash receipt with linked payments/deposits

---

#### LoanController
**Key Methods**:
- `index()`: Groups outstanding loans by source; shows balances by source
- `create()`: Shows loan creation form
- `store()`: Records loan via Loan model; creates transaction
- `storeType()`: Adds new loan source
- `repay()`: Records partial/full repayment; marks as repaid when balance reaches zero

---

#### DashboardController
**Services**:
- DashboardSummaryService

**Key Methods**:
- `index()`: Aggregates all summary data and displays dashboard

---

## Service Layer

### PaymentPostingService
**Purpose**: Orchestrates payment posting with allocations and transaction creation

**Key Methods**:
```php
postPayment(array $data): Payment
  - Creates Payment record
  - Processes allocations (creates PaymentAllocation records)
  - For expense allocations: calls ExpenseSettlementService
  - Creates Transaction audit record
  - Updates landing status via LandingSummaryService
  - Returns Payment (transactional)

createAllocation(Payment $payment, array $data): PaymentAllocation
  - Creates PaymentAllocation
  - Delegates expense updates to ExpenseSettlementService
  - Returns PaymentAllocation

reversePayment(Payment $payment): void
  - Reverses all allocations (expense status updates)
  - Deletes PaymentAllocation records
  - Deletes associated Transaction
  - Updates landing status

updatePayment(Payment $payment, array $data): Payment
  - Calls reversePayment()
  - Updates Payment attributes
  - Reprocesses allocations
  - Updates Transaction
```

---

### InvoicePostingService
**Purpose**: Handles receipt posting and cash deposit creation

**Key Methods**:
```php
postReceipt(array $data): Receipt
  - Validates invoice ownership
  - Creates Receipt record
  - Updates Invoice: received_amount, pending_amount, status
  - Creates Transaction
  - Returns Receipt (transactional)

depositCashToBank(array $data): Transaction
  - Validates cash_source_receipt_id
  - Creates Transaction with type='Receipt', mode='Bank', source='Cash'
  - Links to source receipt via cash_source_receipt_id
  - Returns Transaction (transactional)
```

---

### ExpenseSettlementService
**Purpose**: Manages expense payment status updates

**Key Methods**:
```php
allocatePayment(PaymentAllocation $allocation): void
  - Updates Expense: paid_amount += allocation.amount
  - Recalculates pending_amount and payment_status
  - Saves Expense

updateExpenseStatus(Expense $expense): void
  - Recalculates and saves expense status based on paid/pending amounts

reverseAllocation(PaymentAllocation $allocation): void
  - Decrements Expense paid_amount
  - Recalculates pending_amount and payment_status
  - Saves Expense
```

**Status Calculation Logic**:
```
If pending <= 0:
  status = 'Paid'
Else if paid > 0:
  status = 'Partial'
Else:
  status = 'Pending'
```

---

### LandingSummaryService
**Purpose**: Calculates comprehensive landing financial summaries

**Key Method**:
```php
getSummary(Landing $landing): array
  Returns:
  {
    'gross_value': float,
    'total_expenses': float,
    'total_expenses_paid': float,
    'total_expenses_pending': float,
    'net_owner_payable': float,
    'total_owner_paid': float,
    'owner_pending': float,
    'total_invoices': float,
    'total_received': float,
    'total_buyer_pending': float,
    'status': string  // 'Open', 'Partial', 'Settled', 'Overpaid'
  }

updateLandingStatus(Landing $landing): void
  - Calculates summary
  - Updates landing.status

calculateStatus(float $ownerPending, float $netOwnerPayable): string
  If ownerPending <= 0 AND netOwnerPayable <= 0:
    return 'Settled'
  If netOwnerPayable <= 0:
    return 'Overpaid'
  If ownerPending <= 0:
    return 'Settled'
  If totalPaid > 0:
    return 'Partial'
  Return 'Open'
```

---

### CashSourceTrackingService
**Purpose**: Tracks cash flow, balances, and deposit sources

**Key Methods**:
```php
getCashReceiptsWithUtilization(): Collection
  - Returns all Cash-mode receipts
  - Calculates utilized_amount, deposited_amount, balance for each

getCashPayments(): Collection
  - Returns all Cash-mode payments (excluding loan sources)

getUtilizedAmount(int $receiptId): float
  - Sum of payments using this receipt as source

getDepositedAmount(int $receiptId): float
  - Sum of bank deposits from this receipt

getLinkedPayments(int $receiptId): Collection
  - Returns transactions using receipt as cash source

getLinkedDeposits(int $receiptId): Collection
  - Returns bank transactions from this receipt

getBankBalance(): float
  - Calculates: bankReceipts + cashDeposits + loanReceipts 
               - bankPayments - cashWithdrawals

getAvailableCashReceipts(): Collection
  - Returns cash receipts with balance > 0

getTotalAvailableCash(): float
  - Sum of all balances from available receipts
```

---

### DashboardSummaryService
**Purpose**: Aggregates all financial summaries for dashboard

**Key Methods**:
```php
getSummary(): array
  Returns dashboard overview:
  {
    'cash_in_hand': float,
    'cash_at_bank': float,
    'buyer_pending': float,
    'boat_owner_pending': float,
    'expense_pending': float,
    'personal_fund_used': float,
    'loan_payments': float,
    'basheer_pending': float,
    'overdue_landings': int,
    'outstanding_loans': int,
    'unlinked_expenses': array
  }

getCashInHand(): float
  - cashReceipts + loanReceipts + withdrawals - payments - deposits

getCashAtBank(): float
  - bankReceipts + cashDeposits + loanReceipts - bankPayments

getBuyerPending(): float
  - Sum of Invoice.pending_amount

getBoatOwnerPending(): float
  - Iterates landings: net_owner_payable - owner_paid

getExpensePending(): float
  - Sum of Expense.pending_amount

getOutstandingLoans(): array
  {
    'total': float,
    'Basheer': float,
    'Personal': float,
    'Others': float
  }

getUnlinkedExpenses(): array
  {
    'total': float,
    'count': int,
    'by_boat': [...] with boat_name, total, count
  }
```

---

### LoanTrackingService
**Purpose**: Manages loan lifecycle and balance tracking

**Constants**:
```php
const SOURCES = ['Basheer', 'Personal', 'Others'];
```

**Key Methods**:
```php
getOutstandingLoans(): Collection
  - Returns repaid_at IS NULL loans

getOutstandingLoansBySource(): array
  - Indexed array by source constant

getBalanceBySource(string $source): float
  - Outstanding amount for source

getTotalOutstanding(): float
  - Sum of all outstanding amounts

getBalances(): array
  {
    'Basheer': float,
    'Personal': float,
    'Others': float,
    'total': float
  }

recordLoan(array $data): Loan
  - Creates loan record

markAsRepaid(Loan $loan): void
  - Sets repaid_at = now()

markAsRepaidBySource(string $source): int
  - Marks all outstanding loans from source as repaid

getSummary(): array
  {
    'outstanding': float,
    'by_source': array,
    'count': int
  }
```

---

### InvoiceImportService
**Purpose**: Parses and imports invoice batches

**Key Methods**:
```php
parseFile($file): array
  - Parses CSV/text file
  - Returns: { parsed, errors, total_lines }

parseLine(string $line): ?array
  - Extracts buyer name and amount
  - Patterns: "Name Amount", "Name Rs. Amount", "Name 1000.50"
  - Validates: name length, amount > 0
  - Returns: { buyer_name, amount, is_valid, warning? }

getLandingForImport(int $boatId, ?string $landingDate): ?Landing
  - Finds landing by boat and date
```

---

## Key Business Logic

### 1. Payment Allocation Logic
**Pattern**: Payments can allocate to multiple targets via PaymentAllocation

**Workflow**:
1. User creates Payment with amount and mode
2. System validates available balance based on mode and source
3. User allocates portions to Expenses and/or Landings
4. For Expense allocations:
   - ExpenseSettlementService updates expense paid_amount
   - Status automatically recalculates
5. Transaction created for audit trail
6. Landing status updated based on owner settlement

**Example**: Payment of ₹10,000 split across:
- ₹6,000 to Expense 1 (Diesel)
- ₹4,000 to Landing 5 (Owner settlement)

---

### 2. Cash Source Tracking
**Pattern**: Cash from receipts can be utilized for payments or deposited to bank

**Workflow**:
1. Receipt created (cash payment from buyer)
2. Receipt available for use as cash source
3. When Payment created with mode='Cash':
   - System calculates: balance = receipt.amount - utilized - deposited
   - Validates payment amount <= balance
4. Transaction records cash_source_receipt_id
5. CashSourceTrackingService tracks linkage

**Key Constraint**: Only available cash can be used for payments
```
Available Cash = ReceiptAmount - UtilizedInPayments - DepositedToBank
```

---

### 3. Landing Settlement Calculation
**Complex Formula**:
```
Landing Settlement:
  Gross Value: ₹100,000
  - Expenses: ₹15,000 (Diesel ₹5K, Salary ₹10K)
  = Net Owner Payable: ₹85,000
  
  Owner Received: ₹50,000
  = Owner Pending: ₹35,000
  
  Status:
    - Open: No payments
    - Partial: Some payments made
    - Settled: All net payable paid
```

---

### 4. Invoice & Receipt Cycle
**Workflow**:
1. Landing created → Invoices created for buyers
2. Initial Invoice: received_amount=0, pending_amount=original_amount, status='Pending'
3. Buyer makes payment → Receipt created
4. Receipt posting:
   - Increments invoice.received_amount
   - Recalculates invoice.pending_amount
   - Updates invoice.status
   - Creates transaction
5. Invoice status progression: Pending → Partial → Paid

---

### 5. Unlinked Expenses
**Pattern**: Expenses without landing can be attached later

**Use Cases**:
- Expense incurred before landing date
- Expense not yet assigned to landing
- Future expense for upcoming landing

**Attachment Options**:
- Manual: Null, specific landing_id, or 'next' (next future landing)
- Automatic: System finds next landing when landing is created

---

### 6. Loan Management
**Workflow**:
1. Loan created: amount set, repaid_amount=0, repaid_at=NULL
2. Repayments recorded against loan
3. Each repayment:
   - Increments loan.repaid_amount
   - Creates Payment + Transaction
   - If repaid_amount >= amount: sets repaid_at = now()
4. Outstanding calculation: amount - repaid_amount

---

### 7. Multi-Source Cash Reconciliation
**Sources**:
- CashFromSales: Cash receipts from buyers
- PersonalFund: Personal money contributed
- Bank: Bank account funds
- Basheer/Others: Loan sources
- Cash: Generic source

**Dashboard Cash Calculations**:
```
Cash In Hand = 
  + Cash receipts from sales
  + Loan receipts (Basheer, Personal, Others)
  + Cash withdrawals from bank
  - Cash payments
  - Cash deposited to bank

Cash At Bank =
  + Bank/GP receipts
  + Cash deposits from sales
  + Loan receipts to bank
  - Bank payments
```

---

## Recent Changes & Bug Fixes

### 1. Payment View Error Fix (PAY-004)
**Date**: 2026-04-16  
**Issue**: Viewing loan repayment payments threw null pointer error
**Root Cause**: 
- Loan repayments have nullable boat_id
- View tried to access `$payment->boat->name` without null check

**Solution**:
```php
// Before
{{ $payment->boat->name }}

// After
{{ $payment->boat ? $payment->boat->name : '-' }}
```

**File Modified**: `resources/views/payments/show.blade.php` (line 32)

---

### 2. Nullable Boat ID on Payments (2026-03-30)
**Migration**: `2026_03_30_023724_make_boat_id_nullable_on_payments.php`

**Reason**: Support loan repayments which don't have associated boats

**Change**: `boat_id` foreign key changed from NOT NULL to nullable

---

### 3. Loan Reference Field (2026-03-30)
**Migration**: `2026_03_30_023040_add_loan_reference_to_payments_table.php`

**Reason**: Track which loan a payment is for

**Field Added**: `loan_reference` (string, nullable)

---

### 4. Cash Source Receipt ID (2026-03-30)
**Migration**: `2026_03_30_100000_add_cash_source_receipt_id_to_transactions.php`

**Reason**: Enable tracking cash-to-bank deposit sources

**Fields Added to transactions**:
- `cash_source_receipt_id` (FK, nullable, references receipts.id)
- Index on `cash_source_receipt_id`

**Impact**: Links transactions back to source receipts for cash flow tracking

---

### 5. Expense Type String Conversion (2026-03-29)
**Migration**: `2026_03_29_163401_change_expenses_type_to_string.php`

**Reason**: Allow flexible expense types via lookup table

**Change**: Expense type changed from enum to string (linked to expense_types table)

---

### 6. Tea Types Support (2026-03-29)
**Migration**: `2026_03_29_160001_add_tea_types_to_expenses_table.php`

**Reason**: Extended expense categorization

**Added Types**: Tea-related expense categories to expense_types

---

### 7. Payment Types Table (2026-04-13)
**Migration**: `2026_04_13_000003_create_payment_types_table.php`

**Reason**: Create lookup for flexible payment categorization

**Purpose**: Allows dynamic payment categories (Owner, Expense, Loan, etc.)

---

### 8. Loans Table (2026-04-02)
**Migration**: `2026_04_02_023457_create_loans_table.php`

**New Features**:
- Tracks borrowed funds from Basheer, Personal, Others
- Supports partial repayments
- Tracks full repayment date

**Fields**:
- source, amount, date, repaid_amount, repaid_at, mode

---

### 9. Source Enum Updates (2026-03-31)
**Migration**: `2026_03_31_000001_update_source_enum_values.php`

**Reason**: Add new source types

**Updated Enums**: Added 'Basheer' to source enums

---

### 10. Basheer Source Addition (2026-03-31)
**Migration**: `2026_03_31_000002_add_basheer_source.php`

**Reason**: Explicitly support Basheer as loan source

---

## Configuration & Environment

### Database Configuration
**File**: `config/database.php`

**Default Connection**: SQLite  
**Primary Database**: `database/database.sqlite`  
**Admin Database**: `database/admin.sqlite` (user-specific)

**Foreign Keys**: Enabled (`DB_FOREIGN_KEYS=true`)

### Application Configuration
**File**: `config/app.php`

**Framework**: Laravel (latest)  
**Debug Mode**: Configured via `APP_DEBUG` environment variable

### Multi-Tenancy
**Pattern**: User-based data isolation (not true multi-tenancy)

**Implementation**:
- All queries filtered by `auth()->id()`
- No database-per-user separation
- Single SQLite file with user_id field on all tables

### Key Directories
- `app/Models/`: All Eloquent models
- `app/Http/Controllers/`: Request handlers
- `app/Services/`: Business logic services
- `database/migrations/`: Schema files
- `database/factories/`: Model factories
- `database/seeders/`: Initial data seeders
- `resources/views/`: Blade templates
- `routes/web.php`: Route definitions
- `bootstrap/`: Application bootstrap

### Environment Variables (Typical)
```
APP_NAME="CFH Fund Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
DB_CONNECTION=sqlite
AUTH_GUARD=web
```

---

## Advanced Features

### 1. Polymorphic Relationships
**Implementation**: PaymentAllocation & Transaction use morph relations

**PaymentAllocation**:
```
allocatable_type: 'App\Models\Expense' | 'App\Models\Landing'
allocatable_id: ID
```

**Transaction**:
```
transactionable_type: 'App\Models\Payment' | 'App\Models\Receipt' | 'App\Models\Loan'
transactionable_id: ID
```

---

### 2. Transaction Logging
**Automatic**: Every receipt, payment, and deposit creates transaction

**Purpose**: Complete audit trail for reconciliation

**Used For**:
- Cash flow calculations
- Balance verifications
- Historical tracking

---

### 3. Dynamic Lookups
**Expense Types**: User-extensible via migrations  
**Payment Types**: User-extensible via API  
**Loan Sources**: User-extensible via API

---

### 4. Import/Export Features
**Import Supported**:
- Invoices (CSV/paste: "Buyer | Amount")
- Expenses (CSV format)
- Receipts (CSV format)
- Payments (CSV format)

**Export Supported**:
- CSV export for all major entities
- Summary reports

---

### 5. Calculated Attributes
**Landing**:
- total_expenses
- total_owner_paid
- owner_pending
- net_owner_payable
- status

**Buyer**:
- total_purchased
- total_received
- total_pending

**Loan**:
- outstanding_amount

---

## Summary Statistics

| Component | Count | Notes |
|-----------|-------|-------|
| Models | 13 | User, Boat, Buyer, Landing, Expense, Invoice, Receipt, Payment, PaymentAllocation, Transaction, Loan, ExpenseType, PaymentType, LoanSource |
| Controllers | 18 | Main CRUD + specialized (Backup, Cash, Loan, Report, etc.) |
| Services | 9 | PaymentPosting, InvoicePosting, ExpenseSettlement, CashTracking, LandingSummary, DashboardSummary, LoanTracking, InvoiceImport, UserDatabase |
| Migrations | 43+ | Core tables + 10+ recent changes and extensions |
| Routes | 80+ | REST resources + custom actions + API endpoints |
| Key DB Relationships | 40+ | Complex web of foreign keys and polymorphic relations |

---

## Validation & Constraints

### Key Business Validations
1. **Invoice Amount**: Cannot exceed landing gross_value
2. **Payment Amount**: Cannot exceed available balance (cash) or bank balance
3. **Expense Deletion**: Prevented if payment allocations exist
4. **Landing Assignment**: Expenses must belong to boat before assignment to landing
5. **Receipt Ownership**: Receipt buyer must match invoice buyer
6. **Loan Repayment**: Cannot exceed outstanding balance

---

## Performance Considerations

### Eager Loading
- Controllers use `.with()` for related entities
- Prevents N+1 queries in lists and shows

### Indexes
- Foreign keys indexed automatically
- Polymorphic relations have manual indexes

### Transactions
- PaymentPostingService: DB::transaction()
- InvoicePostingService: DB::transaction()
- ExpenseSettlementService: DB::transaction()
- Ensures data consistency

---

## Access Control

### Public Routes
- Login page

### Authenticated Routes
- All CRUD operations
- Reports
- Dashboard

### Admin-only Routes
- User management
- Backups
- Unlinked expenses management
- System administration

---

**End of Codebase Analysis**

---

## Appendix: File Locations Reference

### Models
- `app/Models/User.php`
- `app/Models/Boat.php`
- `app/Models/Buyer.php`
- `app/Models/Landing.php`
- `app/Models/Expense.php`
- `app/Models/Invoice.php`
- `app/Models/Receipt.php`
- `app/Models/Payment.php`
- `app/Models/PaymentAllocation.php`
- `app/Models/Transaction.php`
- `app/Models/Loan.php`
- `app/Models/ExpenseType.php`
- `app/Models/PaymentType.php`
- `app/Models/LoanSource.php`

### Controllers
- `app/Http/Controllers/`: All 19 controllers

### Services
- `app/Services/`: All 9 services

### Routes
- `routes/web.php`: Main route definitions

### Migrations
- `database/migrations/`: All 43+ migration files

### Configuration
- `config/app.php`: Application settings
- `config/database.php`: Database configuration
- `config/auth.php`: Authentication configuration
