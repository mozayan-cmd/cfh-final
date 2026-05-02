# CFH Fund Management Application - Complete Specification

**Version:** 2.0  
**Updated:** April 16, 2026  
**Application Type:** Laravel Multi-Tenant Fund/Fishing Fleet Management System  
**Database:** SQLite with User Isolation

---

## Table of Contents

1. [Application Overview](#application-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Schema](#database-schema)
4. [Models & Relationships](#models--relationships)
5. [Routes & Controllers](#routes--controllers)
6. [Views & Frontend](#views--frontend)
7. [Service Layer & Business Logic](#service-layer--business-logic)
8. [Features & Workflows](#features--workflows)
9. [Recent Bug Fixes & Corrections](#recent-bug-fixes--corrections)
10. [Validation Rules](#validation-rules)

---

## Application Overview

### Purpose
CFH Fund Management is a comprehensive financial management system for fishing fleet operations. It tracks:
- **Landings**: Fish catch dates and gross values
- **Expenses**: Operational costs (fuel, ice, labor, etc.)
- **Invoices & Receipts**: Buyer transactions and payment tracking
- **Payments**: Distribution to boat owners and expense settlement
- **Cash Flow**: Cash sources, deposits, and bank balances
- **Loans**: Loan tracking and repayment management
- **Transactions**: Audit trail for all financial activities

### Key Characteristics
- **Multi-user**: Each user has isolated data
- **Transaction-based**: All operations create transaction records
- **Status Tracking**: Real-time settlement and payment status
- **Balance Validation**: Prevents overspending based on available cash/bank balance
- **Polymorphic Relationships**: Transactions linked to multiple entity types
- **Service-Oriented**: Business logic encapsulated in service classes

---

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm
- SQLite (included with PHP)

### Setup Steps

```bash
# 1. Clone or create project
git clone <repo> cfh-fund-management
cd cfh-fund-management

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate:fresh --force

# 5. Seed initial data
php artisan db:seed --class=DatabaseSeeder

# 6. Build frontend assets
npm run build

# 7. Start server
php artisan serve --host=0.0.0.0 --port=8000
```

### Default Credentials
```
Email: admin@example.com
Password: password
Role: Admin
```

### Environment Configuration (.env)
```
APP_NAME="CFH Fund Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_MAILER=log
```

---

## Database Schema

### Core Tables (13 Main Tables)

#### 1. `users`
**Purpose**: User authentication and authorization

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  email_verified_at TIMESTAMP,
  password VARCHAR(255),
  role VARCHAR(50) DEFAULT 'user',  -- 'admin' or 'user'
  is_active BOOLEAN DEFAULT true,
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Key Features**:
- First user becomes admin
- `is_active` controls login ability
- Role-based access control

**Relationships**:
- `hasMany('boats')`
- `hasMany('landings')`
- `hasMany('expenses')`
- `hasMany('invoices')`
- `hasMany('receipts')`
- `hasMany('payments')`
- `hasMany('transactions')`
- `hasMany('loans')`

---

#### 2. `boats`
**Purpose**: Represents fishing boats/vessels

```sql
CREATE TABLE boats (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  name VARCHAR(255),
  owner_phone VARCHAR(20) NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Relationships**:
- `belongsTo('users')`
- `hasMany('landings')`
- `hasMany('expenses')`
- `hasMany('invoices')`
- `hasMany('receipts')`
- `hasMany('payments')`

---

#### 3. `buyers`
**Purpose**: Track fish buyers and their transaction history

```sql
CREATE TABLE buyers (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  name VARCHAR(255),
  phone VARCHAR(20) NULLABLE,
  address VARCHAR(500) NULLABLE,
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Calculated Attributes**:
- `total_purchased`: SUM(invoices.original_amount)
- `total_received`: SUM(invoices.received_amount)
- `total_pending`: SUM(invoices.pending_amount)

**Relationships**:
- `belongsTo('users')`
- `hasMany('invoices')`
- `hasMany('receipts')`

---

#### 4. `landings`
**Purpose**: Record fishing trip landing dates, dates, and gross values

```sql
CREATE TABLE landings (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  boat_id BIGINT FOREIGN KEY,
  date DATE,
  gross_value DECIMAL(12, 2),
  status VARCHAR(50) DEFAULT 'Open',  -- 'Open', 'Partial', 'Settled'
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Status Logic**:
- **Open**: No payments made (owner_pending == net_owner_payable)
- **Partial**: Some payment made (0 < owner_pending < net_owner_payable)
- **Settled**: Fully paid (owner_pending == 0)

**Calculated Fields**:
```
total_expenses = SUM(expenses.amount WHERE landing_id = id)
total_expenses_paid = SUM(payment allocations for expenses)
net_owner_payable = gross_value - total_expenses
total_owner_paid = SUM(payments WHERE landing_id = id AND payment_for != 'Expense')
owner_pending = net_owner_payable - total_owner_paid
```

**Relationships**:
- `belongsTo('users')`
- `belongsTo('boats')`
- `hasMany('expenses')`
- `hasMany('invoices')`
- `hasMany('receipts')`
- `hasMany('payments')`

---

#### 5. `expenses`
**Purpose**: Track operational expenses against boats and landings

```sql
CREATE TABLE expenses (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  boat_id BIGINT FOREIGN KEY,
  landing_id BIGINT FOREIGN KEY NULLABLE,  -- Can be unlinked
  date DATE,
  type VARCHAR(255),  -- String type (linked to expense_types)
  vendor_name VARCHAR(255) NULLABLE,
  amount DECIMAL(12, 2),
  paid_amount DECIMAL(12, 2) DEFAULT 0,
  pending_amount DECIMAL(12, 2),
  payment_status VARCHAR(50),  -- 'Pending', 'Partial', 'Paid'
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Expense Types** (Seeded):
- Diesel
- Ice
- Ration
- Petty Cash Advance
- Unloading
- Toll
- Salary
- Other

**Payment Status Logic**:
- **Pending**: paid_amount == 0
- **Partial**: 0 < paid_amount < amount
- **Paid**: paid_amount >= amount

**Key Features**:
- Expenses can be linked to landing_id or left NULL (unlinked)
- Payment is tracked through PaymentAllocation polymorphic relationship
- Vendor name can be specified for tracking supplier details
- Type is a string (not enum) to allow custom expense types

**Relationships**:
- `belongsTo('users')`
- `belongsTo('boats')`
- `belongsTo('landings')`
- `morphMany('payment_allocations', 'allocatable')`
- `morphMany('transactions', 'transactionable')`

---

#### 6. `invoices`
**Purpose**: Track buyer invoices for fish sales

```sql
CREATE TABLE invoices (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  buyer_id BIGINT FOREIGN KEY,
  boat_id BIGINT FOREIGN KEY,
  landing_id BIGINT FOREIGN KEY,
  invoice_date DATE,
  original_amount DECIMAL(12, 2),
  received_amount DECIMAL(12, 2) DEFAULT 0,
  pending_amount DECIMAL(12, 2),
  status VARCHAR(50),  -- 'Pending', 'Partial', 'Paid'
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Validation Rules**:
- Invoice amount cannot exceed landing gross_value
- Status auto-calculated from received_amount vs original_amount

**Relationships**:
- `belongsTo('users')`
- `belongsTo('buyers')`
- `belongsTo('boats')`
- `belongsTo('landings')`
- `hasMany('receipts')`

---

#### 7. `receipts`
**Purpose**: Track cash receipts/payments from buyers

```sql
CREATE TABLE receipts (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  buyer_id BIGINT FOREIGN KEY,
  invoice_id BIGINT FOREIGN KEY,
  boat_id BIGINT FOREIGN KEY,
  landing_id BIGINT FOREIGN KEY,
  date DATE,
  amount DECIMAL(12, 2),
  mode VARCHAR(50),  -- 'Cash', 'GP', 'Bank'
  source VARCHAR(50) NULLABLE,  -- 'CashFromSales', 'PersonalFund', 'Bank', 'Other'
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Key Features**:
- Triggers automatic invoice payment tracking
- Creates transaction record for cash management
- Links to landing and invoice for full traceability

**Relationships**:
- `belongsTo('users')`
- `belongsTo('buyers')`
- `belongsTo('invoices')`
- `belongsTo('boats')`
- `belongsTo('landings')`

---

#### 8. `payments`
**Purpose**: Track payments to boat owners and for expenses

```sql
CREATE TABLE payments (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  boat_id BIGINT FOREIGN KEY NULLABLE,  -- Nullable for non-boat payments
  landing_id BIGINT FOREIGN KEY NULLABLE,
  date DATE,
  amount DECIMAL(12, 2),
  mode VARCHAR(50),  -- 'Cash', 'GP', 'Bank'
  source VARCHAR(50),  -- 'Cash', 'Bank'
  payment_for VARCHAR(255),  -- Dynamic, validated against PaymentType
  loan_reference VARCHAR(255) NULLABLE,  -- For loan payments
  vendor_name VARCHAR(255) NULLABLE,  -- NEW: For 'Other' payment type
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Payment Types** (Seeded and Dynamic):
- Owner
- Expense
- Loan
- Basheer
- Personal
- Mixed
- Other (NEW)

**Key Features**:
- Payment types are stored in PaymentType table (dynamically validated)
- Supports null boat_id for loan repayments and non-boat-specific payments
- Vendor_name field to specify party for 'Other' payment types
- Loan_reference for loan payment details
- Creates PaymentAllocation records to link to expenses/landings
- Validates against available balance (cash/bank)

**Recent Additions**:
- `vendor_name` field for "Other" payment type vendor identification
- Dynamic payment type validation via PaymentType lookup table
- Nullable boat_id for loan and non-boat payments

**Relationships**:
- `belongsTo('users')`
- `belongsTo('boats')`
- `belongsTo('landings')`
- `hasMany('payment_allocations')`
- `morphOne('transactions', 'transactionable')`

---

#### 9. `payment_allocations`
**Purpose**: Link payments to expenses or landings (polymorphic)

```sql
CREATE TABLE payment_allocations (
  id BIGINT PRIMARY KEY,
  payment_id BIGINT FOREIGN KEY,
  allocatable_type VARCHAR(255),  -- 'App\\Models\\Expense' or 'App\\Models\\Landing'
  allocatable_id BIGINT,
  amount DECIMAL(12, 2),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Key Features**:
- Polymorphic: Can allocate payment to Expense or Landing
- Tracks partial payments against expenses/landings
- One payment can have multiple allocations

**Relationships**:
- `belongsTo('payments')`
- `morphTo('allocatable')` -> Expense or Landing
- Payment->allocations: hasManyThrough

---

#### 10. `transactions`
**Purpose**: Audit trail for all financial transactions

```sql
CREATE TABLE transactions (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  type VARCHAR(50),  -- 'Payment', 'Receipt', etc.
  mode VARCHAR(50),
  source VARCHAR(50),
  amount DECIMAL(12, 2),
  boat_id BIGINT NULLABLE,
  landing_id BIGINT NULLABLE,
  cash_source_receipt_id BIGINT NULLABLE,
  transactionable_type VARCHAR(255),
  transactionable_id BIGINT,
  date DATE,
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Polymorphic Types**:
- 'App\\Models\\Payment' - Payment transaction
- 'App\\Models\\Receipt' - Buyer receipt transaction

**Relationships**:
- `belongsTo('users')`
- `morphTo('transactionable')`

---

#### 11. `loans`
**Purpose**: Track loans from various sources and their repayments

```sql
CREATE TABLE loans (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  loan_source_id BIGINT FOREIGN KEY,
  amount DECIMAL(12, 2),
  date DATE,
  due_date DATE NULLABLE,
  status VARCHAR(50),  -- 'Active', 'Repaid'
  interest_rate DECIMAL(5, 2) NULLABLE,
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Relationships**:
- `belongsTo('users')`
- `belongsTo('loan_sources')`
- `hasMany('loan_repayments')`

---

#### 12. `loan_sources`
**Purpose**: Track sources of loans

```sql
CREATE TABLE loan_sources (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  name VARCHAR(255),  -- 'Basheer', 'Personal', 'Bank', etc.
  contact VARCHAR(255) NULLABLE,
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Seeded Loan Sources**:
- Basheer
- Personal
- Bank
- Other

**Relationships**:
- `belongsTo('users')`
- `hasMany('loans')`

---

#### 13. `payment_types`
**Purpose**: Dynamic payment type management

```sql
CREATE TABLE payment_types (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255) UNIQUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Seeded Values**:
- Owner
- Expense
- Loan
- Basheer
- Personal
- Mixed
- Other

**Key Feature**:
- Allows custom payment types to be added dynamically
- Payment::store validation validates against this table

---

#### 14. `expense_types`
**Purpose**: Dynamic expense type management

```sql
CREATE TABLE expense_types (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255) UNIQUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Seeded Values**:
- Diesel
- Ice
- Ration
- Petty Cash Advance
- Unloading
- Toll
- Salary
- Other

---

### Additional Migrations (Recent Updates)

#### A. Add vendor_name to payments (NEW - Latest Session)
```php
// Migration: 2026_04_16_000000_add_vendor_name_to_payments_table.php
Schema::table('payments', function (Blueprint $table) {
    $table->string('vendor_name')->nullable()->after('loan_reference');
});
```
**Purpose**: Support tracking vendor names for "Other" payment types

#### B. Add loan_reference to payments
```php
Schema::table('payments', function (Blueprint $table) {
    $table->string('loan_reference')->nullable()->after('payment_for');
});
```
**Purpose**: Track loan details when recording loan payments

#### C. Add cash_source_receipt_id to transactions
```php
Schema::table('transactions', function (Blueprint $table) {
    $table->unsignedBigInteger('cash_source_receipt_id')->nullable();
});
```
**Purpose**: Link transactions to specific cash receipts

#### D. Make boat_id nullable on payments
```php
Schema::table('payments', function (Blueprint $table) {
    $table->unsignedBigInteger('boat_id')->nullable()->change();
});
```
**Purpose**: Allow loan repayments without specifying a boat

#### E. Update source enum values
```php
// Add 'Basheer' to allowed sources
// Retain: 'Cash', 'Personal', 'Bank', 'Other'
```

---

## Models & Relationships

### User Model (`app/Models/User.php`)
```php
class User extends Authenticatable {
    // Relations
    hasMany('boats')
    hasMany('landings')
    hasMany('expenses')
    hasMany('invoices')
    hasMany('receipts')
    hasMany('payments')
    hasMany('transactions')
    hasMany('loans')
    hasMany('loan_sources')
}
```

---

### Boat Model (`app/Models/Boat.php`)
```php
class Boat extends Model {
    protected $fillable = [
        'user_id', 'name', 'owner_phone'
    ];

    // Relations
    belongsTo('users')
    hasMany('landings')
    hasMany('expenses')
    hasMany('invoices')
    hasMany('receipts')
    hasMany('payments')
    
    // Methods
    getTotalExpensesAttribute()
    getTotalRevenueAttribute()
}
```

---

### Landing Model (`app/Models/Landing.php`)
```php
class Landing extends Model {
    protected $fillable = [
        'user_id', 'boat_id', 'date', 'gross_value', 'status', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'gross_value' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('boats')
    hasMany('expenses')
    hasMany('invoices')
    hasMany('receipts')
    hasMany('payments')
    morphMany('payment_allocations', 'allocatable')
}
```

---

### Expense Model (`app/Models/Expense.php`)
```php
class Expense extends Model {
    protected $fillable = [
        'user_id', 'boat_id', 'landing_id', 'date', 'type', 'vendor_name',
        'amount', 'paid_amount', 'pending_amount', 'payment_status', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('boats')
    belongsTo('landings')
    morphMany('payment_allocations', 'allocatable')
    morphMany('transactions', 'transactionable')
    
    // Key Methods
    getPaymentAttribute()  // Accessor to get related payment via allocation
    getAllocatedAmountAttribute()
    getRelatedRecords()
}
```

**Recent Fix**: Added accessor method `getPaymentAttribute()` to retrieve related payment through PaymentAllocation relationship

---

### Invoice Model (`app/Models/Invoice.php`)
```php
class Invoice extends Model {
    protected $fillable = [
        'user_id', 'buyer_id', 'boat_id', 'landing_id', 'invoice_date',
        'original_amount', 'received_amount', 'pending_amount', 'status', 'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'original_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('buyers')
    belongsTo('boats')
    belongsTo('landings')
    hasMany('receipts')
}
```

---

### Receipt Model (`app/Models/Receipt.php`)
```php
class Receipt extends Model {
    protected $fillable = [
        'user_id', 'buyer_id', 'invoice_id', 'boat_id', 'landing_id',
        'date', 'amount', 'mode', 'source', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('buyers')
    belongsTo('invoices')
    belongsTo('boats')
    belongsTo('landings')
    morphOne('transactions', 'transactionable')
}
```

---

### Payment Model (`app/Models/Payment.php`)
```php
class Payment extends Model {
    protected $fillable = [
        'user_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode',
        'source', 'payment_for', 'loan_reference', 'notes', 'vendor_name'  // NEW
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('boats')
    belongsTo('landings')
    hasMany('payment_allocations')
    morphOne('transactions', 'transactionable')
    
    // Recent Addition: Accessor for related payment
    getPaymentAttribute()
}
```

**Key Changes**:
- Added `vendor_name` field for "Other" payment type
- Maintains `loan_reference` field for loan payments

---

### PaymentType Model (`app/Models/PaymentType.php`)
```php
class PaymentType extends Model {
    protected $fillable = ['name'];
    
    // No relations - Simple lookup table
}
```

---

### Transaction Model (`app/Models/Transaction.php`)
```php
class Transaction extends Model {
    protected $fillable = [
        'user_id', 'type', 'mode', 'source', 'amount', 'boat_id',
        'landing_id', 'cash_source_receipt_id', 'transactionable_type',
        'transactionable_id', 'date', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relations
    belongsTo('users')
    morphTo('transactionable')
}
```

---

### Loan Model (`app/Models/Loan.php`)
```php
class Loan extends Model {
    protected $fillable = [
        'user_id', 'loan_source_id', 'amount', 'date', 'due_date',
        'status', 'interest_rate', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:5,2',
    ];

    // Relations
    belongsTo('users')
    belongsTo('loan_sources')
    hasMany('loan_repayments')
}
```

---

### LoanSource Model (`app/Models/LoanSource.php`)
```php
class LoanSource extends Model {
    protected $fillable = [
        'user_id', 'name', 'contact', 'notes'
    ];

    // Relations
    belongsTo('users')
    hasMany('loans')
}
```

---

## Routes & Controllers

### Main Routes (`routes/web.php`)

```php
// Authentication Routes
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// Dashboard
Route::get('/', 'DashboardController@index')->middleware('auth')->name('dashboard');

// Boat Management
Route::resource('boats', 'BoatController')->middleware('auth');

// Landing Management
Route::resource('landings', 'LandingController')->middleware('auth');

// Expense Management
Route::resource('expenses', 'ExpenseController')->middleware('auth');
Route::post('/expenses/import', 'ExpenseController@import')->name('expenses.import');
Route::post('/expenses/export', 'ExpenseController@export')->name('expenses.export');
Route::get('/expenses/import', 'ExpenseController@showImport')->name('expenses.import.show');

// Invoice Management
Route::resource('invoices', 'InvoiceController')->middleware('auth');
Route::post('/invoices/import', 'InvoiceController@import')->name('invoices.import');

// Buyer Management
Route::resource('buyers', 'BuyerController')->middleware('auth');

// Receipt Management
Route::resource('receipts', 'ReceiptController')->middleware('auth');
Route::get('/cash/utilization', 'ReceiptController@utilization')->name('cash.utilization');
Route::post('/receipts/{receipt}/utilization', 'ReceiptController@updateUtilization')->name('receipts.utilization.update');

// Payment Management
Route::resource('payments', 'PaymentController')->middleware('auth');
Route::post('/payments/import', 'PaymentController@import')->name('payments.import');
Route::get('/payments/landings/{boat}', 'PaymentController@getLandingsByBoat');
Route::get('/payments/expenses/{boat}', 'PaymentController@getExpensesByBoat');
Route::get('/payments/landing/{landing}', 'PaymentController@getLandingExpenses');
Route::post('/payments/types', 'PaymentController@storeType')->name('payments.types');

// Loan Management
Route::resource('loans', 'LoanController')->middleware('auth');

// Bank Management
Route::get('/bank', 'BankController@index')->name('bank.index');
Route::post('/bank/deposits', 'BankController@storeDeposit')->name('bank.deposits.store');

// Reports & Control Panel
Route::get('/reports', 'ReportController@index')->name('reports');

// User Management (Admin Only)
Route::resource('users', 'UserController')->middleware('admin');

// Unlinked Expenses (special handling)
Route::resource('unlinked-expenses', 'UnlinkedExpenseController')->middleware('auth');
```

---

### Controllers & Methods

#### DashboardController
- `index()` - Displays dashboard with financial summaries

#### BoatController
- `index()` - List all boats
- `create()` - Create boat form
- `store(StoreBoatRequest)` - Save new boat
- `edit(Boat)` - Edit form
- `update(UpdateBoatRequest, Boat)` - Update boat
- `destroy(Boat)` - Delete boat

#### LandingController
- `index()` - List landings with filters
- `create()` - Create landing form
- `store(StoreLandingRequest)` - Save landing
- `show(Landing)` - Landing details
- `edit(Landing)` - Edit form
- `update(UpdateLandingRequest, Landing)` - Update landing
- `destroy(Landing)` - Delete landing

#### ExpenseController
- `index()` - List expenses with filters
- `create()` - Create expense form
- `store(StoreExpenseRequest)` - Save expense
- `edit(Expense)` - Edit form
- `update(UpdateExpenseRequest, Expense)` - Update expense
- `destroy(Expense)` - Delete expense
- `import()` - Show import form
- `export(Request)` - Export expenses to CSV
- `handleImport(Request)` - Process CSV import

**Key Features**:
- Dynamic boat selection
- Landing auto-assignment ('next' for next future landing)
- Expense type validation via ExpenseType table
- Payment status auto-calculation
- CSV import/export capability

#### PaymentController
- `index(Request)` - List payments with filters
- `create(Request)` - Create payment form
  - Pre-selects boat from expense_id query parameter
  - Loads available cash receipts and bank balance
- `store(StorePaymentRequest)` - Save payment with allocations
  - Validates balance (cash/bank)
  - Creates PaymentAllocation records
  - Creates transaction record
- `show(Payment)` - Payment details
- `edit(Payment)` - Edit payment form
- `update(UpdatePaymentRequest, Payment)` - Update payment
- `destroy(Payment)` - Delete payment
- `export(Request)` - Export payments to CSV
- `import()` - Show import form
- `getLandingsByBoat(string)` - API endpoint for boat landings
- `getExpensesByBoat(string)` - API endpoint for boat expenses
- `getLandingExpenses(Request)` - API endpoint for landing expenses
- `storeType(Request)` - Add new payment type

**Recent Enhancements**:
- Dynamic `payment_for` validation against PaymentType table
- Support for "Other" payment type with vendor_name field
- Null boat_id support for loan payments

#### InvoiceController
- `index()` - List invoices
- `create()` - Create invoice
- `store(StoreInvoiceRequest)` - Save invoice with validation
- `edit(Invoice)` - Edit form
- `update(UpdateInvoiceRequest, Invoice)` - Update invoice
- `destroy(Invoice)` - Delete invoice
- `import()` - Show import form

**Validation**:
- Invoice amount cannot exceed landing gross_value

#### ReceiptController
- `index()` - List receipts
- `create()` - Create receipt form
- `store(StoreReceiptRequest)` - Save receipt
  - Updates related invoice payment status
  - Creates transaction record
- `edit(Receipt)` - Edit form
- `update(UpdateReceiptRequest, Receipt)` - Update receipt
- `destroy(Receipt)` - Delete receipt
- `utilization()` - Show cash utilization breakdown
- `updateUtilization(Request, Receipt)` - Update cash utilization

#### BuyerController
- `index()` - List buyers with calculated totals
- `create()` - Create buyer
- `store(StoreBuyerRequest)` - Save buyer
- `edit(Buyer)` - Edit form
- `update(UpdateBuyerRequest, Buyer)` - Update buyer
- `destroy(Buyer)` - Delete buyer

#### LoanController
- `index()` - List loans
- `create()` - Create loan form
- `store(StoreLoanRequest)` - Save loan
- `show(Loan)` - Loan details
- `edit(Loan)` - Edit form
- `update(UpdateLoanRequest, Loan)` - Update loan
- `destroy(Loan)` - Delete loan

#### BankController
- `index()` - Show bank management dashboard
- `storeDeposit(Request)` - Record cash-to-bank deposit

#### ReportController
- `index()` - Show reports and control panel

#### UserController (Admin Only)
- `index()` - List users
- `create()` - Create user
- `store(Request)` - Save user
- `edit(User)` - Edit form
- `update(Request, User)` - Update user
- `destroy(User)` - Delete user

---

## Views & Frontend

### Template Structure
```
resources/views/
├── layouts/
│   ├── main.blade.php          # Main layout with sidebar
│   ├── guest.blade.php         # Guest layout (login)
│   └── app.blade.php           # App layout
├── auth/
│   ├── login.blade.php         # Login form
│   └── register.blade.php      # Registration (if enabled)
├── dashboard/
│   ├── index.blade.php         # Dashboard home
│   └── summary.blade.php       # Financial summary
├── boats/
│   ├── index.blade.php         # Boats list
│   ├── create.blade.php        # Create boat
│   ├── edit.blade.php          # Edit boat
│   └── show.blade.php          # Boat details
├── landings/
│   ├── index.blade.php         # Landings list with filters
│   ├── create.blade.php        # Create landing form
│   ├── edit.blade.php          # Edit landing
│   └── show.blade.php          # Landing details & summary
├── expenses/
│   ├── index.blade.php         # Expenses list with filters
│   ├── create.blade.php        # Create/inline expense form
│   ├── edit.blade.php          # Edit expense
│   ├── import.blade.php        # CSV import interface
│   └── show.blade.php          # Expense details
├── payments/
│   ├── index.blade.php         # Payments list with filters
│   ├── create.blade.php        # Create payment with dynamic allocation
│   ├── edit.blade.php          # Edit payment
│   └── show.blade.php          # Payment details
├── invoices/
│   ├── index.blade.php         # Invoices list
│   ├── create.blade.php        # Create invoice
│   ├── edit.blade.php          # Edit invoice
│   └── show.blade.php          # Invoice details
├── receipts/
│   ├── index.blade.php         # Receipts list
│   ├── create.blade.php        # Create receipt
│   └── utilization.blade.php   # Cash utilization dashboard
├── loans/
│   ├── index.blade.php         # Loans list
│   ├── create.blade.php        # Create loan
│   └── show.blade.php          # Loan details
└── reports/
    └── index.blade.php         # Control panel & reports
```

### Frontend Features

#### Payments Create Page (Key Feature)
```blade
1. Boat Selection (Optional)
   - Dropdown with all user boats
   - Auto-loads landings and pending expenses
   
2. Landing Selection (Optional, for Owner Payments)
   - Shows: date, net owner payable, owner pending
   - Displays summary of landing finances
   
3. Landing/Expense Summary Display
   - Shows when landing is selected
   - Displays: Gross Value, Expenses, Net Payable, Amount Paid, Pending
   
4. Amount Input
   - Validates against available cash/bank balance
   - Shows "Remaining After Payment"
   
5. Payment Mode Selection (Cash/GP/Bank)
   - Changes validation rules
   - Shows available balance for selected mode
   
6. Payment For Selection (Dynamic)
   - Populated from PaymentType table
   - Different fields shown based on type:
     - "Loan": Shows loan_reference field
     - "Other": Shows vendor_name field (NEW)
     
7. Payment Allocation (Dynamic)
   - Allows linking to multiple expenses
   - Auto-calculates and displays allocated amounts
   - Shows remaining unallocated amount
```

**New Feature - Payment Type "Other"**:
- When payment_for == "Other", vendor_name field appears
- Vendor name is required when "Other" is selected
- Vendor name is stored and displayed in payment list

#### Expenses Index Page (Recent Update)
- Shows vendor names from:
  1. Payment's vendor_name if payment_for == "Other"
  2. Otherwise, the expense's own vendor_name
  3. Otherwise, "-"

#### Payments Index Page (Recent Update)
- Shows vendor information from:
  1. Loan reference if payment_for == "Loan"
  2. Payment's vendor_name if payment_for == "Other" (NEW)
  3. Expense vendor name from allocated expense
  4. Otherwise, payment_for type name

---

## Service Layer & Business Logic

### PaymentPostingService
**Location**: `app/Services/PaymentPostingService.php`

**Purpose**: Handle payment creation and updates with transaction management

**Key Methods**:

#### `postPayment(array $data): Payment`
1. Creates Payment record with:
   - user_id, boat_id, landing_id, date, amount, mode, source
   - payment_for (validated against PaymentType table)
   - loan_reference (if payment_for == 'Loan')
   - vendor_name (if payment_for == 'Other') **NEW**
   - notes

2. Creates PaymentAllocation records:
   - Links payment to expenses or landings
   - Allocates amount to each target
   - Updates expense paid_amount and pending_amount

3. Creates Transaction record:
   - Type: 'Payment'
   - Mode and source from payment
   - Links to payment via transactionable polymorphic

4. Updates related landing status:
   - Recalculates owner_pending
   - Updates status (Open/Partial/Settled)

**Workflow**:
```
1. Validate payment data
2. Create Payment record (DB transaction wrapper)
3. Create PaymentAllocation(s)
4. Create Transaction
5. Update landing status
6. Return created Payment
```

---

#### `updatePayment(Payment $payment, array $data): Payment`
1. Reverses original payment (removes allocations)
2. Creates new Payment record with updated data
3. Re-creates allocations
4. Updates transaction
5. Updates landing status

---

### ExpenseSettlementService
**Location**: `app/Services/ExpenseSettlementService.php`

**Purpose**: Track and update expense payment status

**Key Methods**:

#### `updateExpenseStatus(Expense $expense): void`
1. Sums PaymentAllocation amounts for expense
2. Updates paid_amount and pending_amount
3. Recalculates payment_status:
   - Pending: paid_amount == 0
   - Partial: 0 < paid_amount < amount
   - Paid: paid_amount >= amount

---

### LandingSummaryService
**Location**: `app/Services/LandingSummaryService.php`

**Purpose**: Calculate landing financial summaries

**Key Methods**:

#### `getSummary(Landing $landing): array`
Returns calculated values:
- `total_expenses`: Sum of expenses
- `total_expenses_paid`: Sum of allocated expense payments
- `net_owner_payable`: gross_value - total_expenses
- `total_owner_paid`: Sum of owner allocation payments
- `owner_pending`: net_owner_payable - total_owner_paid
- `status`: Open/Partial/Settled
- `total_invoices`: Sum of invoice original amounts
- `total_received`: Sum of received amounts
- `total_buyer_pending`: Sum of pending amounts

#### `updateLandingStatus(Landing $landing): void`
1. Retrieves summary
2. Updates landing status based on owner_pending
3. Saves landing

---

### CashSourceTrackingService
**Location**: `app/Services/CashSourceTrackingService.php`

**Purpose**: Track cash sources and availability

**Key Methods**:

#### `getAvailableCashReceipts(): Collection`
Returns receipts with available balance:
```php
foreach(receipt) {
    utilized = getUtilizedAmount(receipt.id)
    deposited = getDepositedAmount(receipt.id)
    balance = receipt.amount - utilized - deposited
}
```

#### `getTotalAvailableCash(): decimal`
Sums available balances from all cash receipts

#### `getBankBalance(): decimal`
Calculates bank balance from transactions:
- Adds receipt deposits (mode == 'Bank')
- Subtracts bank payments (payment_for != 'Owner', mode in ['Bank', 'GP'])

#### `getUtilizedAmount(receipt_id): decimal`
Sums payments that reference this receipt

#### `getDepositedAmount(receipt_id): decimal`
Sums deposited amounts linked to receipt

---

### InvoiceImportService
**Location**: `app/Services/InvoiceImportService.php`

**Purpose**: Parse and import CSV invoices

**Key Methods**:

#### `import(UploadedFile $file): array`
1. Parses CSV file
2. Validates rows (checks boat, buyer existence)
3. Creates Invoice records
4. Returns: success count, error count, detailed results

---

### PaymentPostingService (Additional)
- Handles both Payment creation and updates
- Encapsulates business logic for payment processing
- Manages transaction generation

---

### DashboardSummaryService
**Location**: `app/Services/DashboardSummaryService.php`

**Purpose**: Aggregate data for dashboard display

**Key Methods**:
- `getTotalRevenue()`
- `getTotalExpenses()`
- `getTotalOwnerPayments()`
- `getPendingOwnerPayments()`
- `getCashInHand()`
- `getBankBalance()`
- `getRecentTransactions()`

---

## Features & Workflows

### Core Workflows

#### 1. Landing & Payment Settlement
```
1. Create Landing (date, gross_value, boat)
   ↓
2. Add Expenses against landing
   ↓
3. Receive Invoice(s) from buyer
   ↓
4. Receive Cash Receipt(s) from buyer
   ↓
5. Create Payment to Owner (amount = net_owner_payable)
   - Allocate to landing
   ↓
6. Landing status updates:
   - Open → Partial (when first payment made)
   - Partial → Settled (when owner_pending = 0)
```

#### 2. Expense Payment
```
1. Create Expense (amount, vendor_name)
   ↓
2. Create Payment (payment_for = 'Expense')
   - Allocate to expense(s)
   ↓
3. Expense status updates:
   - Pending → Partial → Paid
```

#### 3. Cash to Bank Deposit
```
1. Receive Cash Receipt from buyer (Cash mode)
   ↓
2. Create Receipt record
   ↓
3. Record Deposit Transaction
   - Links cash receipt to bank
   - Marks receipt as "deposited"
   ↓
4. Bank balance increases
   - getBankBalance() includes deposits
```

#### 4. Loan Management
```
1. Create Loan (source, amount, date, status)
   ↓
2. Create Payment (payment_for = 'Loan')
   - Includes loan_reference
   - Can be null boat_id
   ↓
3. Record repayment amounts
```

#### 5. "Other" Payment Type (NEW)
```
1. Create Payment (payment_for = 'Other')
   ↓
2. Enter vendor_name (party details)
   ↓
3. Payment displays vendor name instead of "Other"
   - Payments index: Shows vendor_name
   - Expenses index: Shows vendor_name (if linked)
```

---

### Key Business Rules

#### Balance Validation
- **Cash Mode**: Total payment amount ≤ total available cash
  - If linked to receipt: payment amount ≤ receipt available balance
  - Else: payment amount ≤ sum of all available receipts
- **Bank Mode**: Payment amount ≤ current bank balance

#### Status Calculations
**Landing Status**:
- Open: owner_pending == net_owner_payable
- Partial: 0 < owner_pending < net_owner_payable
- Settled: owner_pending == 0

**Expense Status**:
- Pending: paid_amount == 0
- Partial: 0 < paid_amount < amount
- Paid: paid_amount >= amount

**Invoice Status**:
- Pending: received_amount == 0
- Partial: 0 < received_amount < original_amount
- Paid: received_amount >= original_amount or pending_amount <= 0

**Receipt Status**:
- Available: amount - utilized - deposited
- Utilized: sum of payments referencing receipt
- Deposited: amount moved to bank

---

## Recent Bug Fixes & Corrections

### 1. **Add "Other" Payment Type Support** (Latest - This Session)
**Issue**: Users needed a way to record payments to parties other than standard types
**Solution**:
- Added `vendor_name` column to payments table
- Added "Other" to PaymentType seeder
- Modified PaymentController create form to show vendor_name field when "Other" selected
- Updated PaymentPostingService to save vendor_name
- Updated both payments and expenses index views to display vendor_name for "Other" type
- Added validation: vendor_name required when payment_for == 'Other'
- Modified Expense model accessor to properly retrieve related payment

**Files Modified**:
- `migrations/2026_04_16_000000_add_vendor_name_to_payments_table.php` (NEW)
- `Models/Payment.php` - Added vendor_name to fillable
- `Models/Expense.php` - Added getPaymentAttribute() accessor, restored paymentAllocations() relationship
- `Http/Controllers/PaymentController.php` - Updated eager loading
- `Http/Requests/StorePaymentRequest.php` - Added vendor_name validation with conditional requirement
- `Http/Requests/UpdatePaymentRequest.php` - Added vendor_name validation with conditional requirement
- `Services/PaymentPostingService.php` - Added vendor_name to create and update methods
- `resources/views/payments/create.blade.php` - Added conditional vendor_name field with toggle
- `resources/views/payments/edit.blade.php` - Added conditional vendor_name field with toggle
- `resources/views/payments/index.blade.php` - Added vendor_name display logic for "Other" type
- `resources/views/payments/show.blade.php` - Display vendor_name when payment_for == 'Other'
- `resources/views/expenses/index.blade.php` - Updated vendor column to show payment vendor_name for "Other" type
- `database/seeders/DatabaseSeeder.php` - Added 'Other' to payment types

---

### 2. **Dynamic Payment Types Validation**
**Issue**: Payment creation was validating against hardcoded enum instead of database records
**Solution**:
- Created PaymentType lookup table
- Seeded with initial types
- Modified StorePaymentRequest to validate against: `PaymentType::pluck('name')->toArray()`
- Same fix applied to UpdatePaymentRequest
- Allows custom payment types via PaymentController::storeType()

**Files Modified**:
- `migrations/2024_01_01_000011_create_payment_types_table.php`
- `Models/PaymentType.php`
- `Http/Requests/StorePaymentRequest.php`
- `Http/Requests/UpdatePaymentRequest.php`
- `Http/Controllers/PaymentController.php` - storeType() method

---

### 3. **Loan Reference Field Addition**
**Issue**: Needed to track loan details (bank name, loan type) for loan payments
**Solution**:
- Added loan_reference column to payments table
- Modified create/edit forms to show field when payment_for == 'Loan'
- Added to PaymentPostingService creation logic
- Displayed in payment show view

**Files Modified**:
- `migrations/2026_03_30_023040_add_loan_reference_to_payments_table.php`
- `Models/Payment.php` - Added to fillable
- `Http/Controllers/PaymentController.php` - create/edit logic
- `resources/views/payments/create.blade.php` - Conditional display
- `resources/views/payments/edit.blade.php` - Conditional display
- `resources/views/payments/show.blade.php` - Display logic

---

### 4. **Nullable boat_id for Loan Payments**
**Issue**: Loan payments and other non-boat-specific payments were forced to have boat_id
**Solution**:
- Made boat_id nullable on payments table
- Updated validation rules to allow null boat_id
- Modified views to make boat selection optional for "Other" and "Loan" payment types

**Files Modified**:
- `migrations/2026_03_30_023723_make_boat_id_nullable_on_payments.php`
- `Models/Payment.php` - Updated relationships
- `Http/Requests/StorePaymentRequest.php` - boat_id validation
- `Http/Requests/UpdatePaymentRequest.php` - boat_id validation
- `resources/views/payments/create.blade.php` - Optional boat selection

---

### 5. **Cash Source Receipt Tracking**
**Issue**: Needed to link transactions to specific cash receipts for traceability
**Solution**:
- Added cash_source_receipt_id to transactions table
- Updated ReceiptController to link payment to source receipt
- CashSourceTrackingService tracks utilized and deposited amounts

**Files Modified**:
- `migrations/2026_03_30_100000_add_cash_source_receipt_id_to_transactions.php`
- `Models/Transaction.php` - Added column to fillable
- `Http/Controllers/PaymentController.php` - Pass receipt_id to transaction
- `Services/CashSourceTrackingService.php` - Tracking logic

---

### 6. **Payment View Null Pointer Exception**
**Issue**: Accessing payment details threw null pointer when payment had no allocations
**Solution**:
- Added null checks in payment display logic
- Updated Expense model to safely retrieve related payment
- Modified views to handle null allocations gracefully

**Files Modified**:
- `resources/views/payments/index.blade.php` - Null checks
- `resources/views/payments/show.blade.php` - Safe access
- `Models/Expense.php` - getPaymentAttribute() accessor with null handling

---

### 7. **Expense Type String Conversion**
**Issue**: Expense type was enum, limiting flexibility
**Solution**:
- Changed expense type from enum to string
- Created expense_types table for managed types
- Modified ExpenseController to validate against table
- Seeded common types (Diesel, Ice, Ration, etc.)

**Files Modified**:
- `migrations/2026_03_29_163401_change_expenses_type_to_string.php`
- `migrations/2024_01_01_000010_create_expense_types_table.php`
- `Models/Expense.php` - Updated type handling
- `Http/Controllers/ExpenseController.php` - Validation updated

---

### 8. **Basheer Source Addition**
**Issue**: Needed to track payments/sources from specific lender "Basheer"
**Solution**:
- Added 'Basheer' to sources enum
- Seeded LoanSource as 'Basheer'
- Updated transaction source tracking

**Files Modified**:
- `migrations/2026_03_31_000002_add_basheer_source.php`
- `database/seeders/DatabaseSeeder.php`

---

## Validation Rules

### StorePaymentRequest / UpdatePaymentRequest
```php
'boat_id' => 'nullable|exists:boats,id',
'landing_id' => 'nullable|exists:landings,id',
'date' => 'required|date',
'amount' => 'required|numeric|min:0.01',
'mode' => 'required|in:Cash,GP,Bank',
'source' => 'required|in:Cash,Bank',
'payment_for' => ['required', Rule::in(PaymentType::pluck('name')->toArray())],
'loan_reference' => 'nullable|string|max:255',
'vendor_name' => 'nullable|string|max:255',  // NEW
'notes' => 'nullable|string',
'allocations' => 'nullable|array',
'allocations.*.type' => 'required_with:allocations|in:expense,landing',
'allocations.*.id' => 'required_with:allocations',
'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',

// Custom validation
- vendor_name required if payment_for == 'Other'
- Total allocation amount ≤ payment amount
- Payment amount ≤ available balance (based on mode)
```

### StoreExpenseRequest / UpdateExpenseRequest
```php
'boat_id' => 'required|exists:boats,id',
'landing_id' => 'nullable|exists:landings,id', // Can be unlinked
'date' => 'required|date',
'type' => ['required', Rule::in(ExpenseType::pluck('name')->toArray())],
'vendor_name' => 'nullable|string|max:255',
'amount' => 'required|numeric|min:0.01',
'notes' => 'nullable|string',
```

### StoreInvoiceRequest
```php
'buyer_id' => 'required|exists:buyers,id',
'boat_id' => 'required|exists:boats,id',
'landing_id' => 'required|exists:landings,id',
'invoice_date' => 'required|date',
'original_amount' => 'required|numeric|min:0.01',
'notes' => 'nullable|string',

// Custom validation
- original_amount ≤ landing.gross_value
```

### StoreReceiptRequest
```php
'buyer_id' => 'required|exists:buyers,id',
'invoice_id' => 'required|exists:invoices,id',
'boat_id' => 'required|exists:boats,id',
'landing_id' => 'required|exists:landings,id',
'date' => 'required|date',
'amount' => 'required|numeric|min:0.01',
'mode' => 'required|in:Cash,GP,Bank',
'source' => 'nullable|in:CashFromSales,PersonalFund,Bank,Other',
'notes' => 'nullable|string',
```

---

## Configuration Files

### .env
```env
APP_NAME="CFH Fund Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_KEY=base64:...

LOG_CHANNEL=stack

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_DRIVER=file
SESSION_DRIVER=cookie
QUEUE_DRIVER=sync

MAIL_MAILER=log
```

### config/app.php
- Timezone: UTC
- Locale: en
- Middleware: Web, Auth

### config/database.php
```php
'default' => env('DB_CONNECTION', 'sqlite'),
'sqlite' => [
    'driver' => 'sqlite',
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
]
```

---

## Testing & Debugging

### Key Testing Scenarios

1. **Payment Creation with Balance Validation**
   - Test cash payment ≤ available cash
   - Test bank payment ≤ bank balance
   - Test receipt-specific cash allocation

2. **Payment Allocation**
   - Test single expense allocation
   - Test multiple expense allocations
   - Test landing allocation
   - Test unallocated payment amount

3. **Landing Settlement**
   - Test status progression (Open → Partial → Settled)
   - Test owner payment calculation
   - Test expense deduction from gross value

4. **Cash Flow Tracking**
   - Test receipt available balance calculation
   - Test cash deposit to bank
   - Test utilized amount tracking

5. **"Other" Payment Type (NEW)**
   - Test vendor_name required validation
   - Test vendor_name display in lists
   - Test vendor_name display in expense allocations

---

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate:fresh --force`
- [ ] Seed data: `php artisan db:seed`
- [ ] Build assets: `npm run build`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Set permissions: `chmod -R 775 storage bootstrap/cache`
- [ ] Enable .htaccess rewrite (if Apache)
- [ ] Test payment creation workflow
- [ ] Test cash balance validation
- [ ] Test landing settlement

---

## API Endpoints (JSON Responses)

### Payment-Related
```
GET /payments/landings/{boat_id}
Response: { landings: [{id, date, net_owner_payable, owner_pending, status}, ...] }

GET /payments/expenses/{boat_id}
Response: { expenses: [{id, date, type, vendor_name, amount, pending_amount, payment_status}, ...] }

GET /payments/landing/{landing_id}
Response: {
  landing: {id, date, gross_value, boat_name, net_owner_payable, owner_pending},
  pending_expenses: [{id, type, vendor_name, pending_amount}, ...]
}
```

---

## Performance Optimization

### Eager Loading
- All list views use `with()` to eager load relationships
- Prevents N+1 query problems
- Example:
  ```php
  Payment::with(['boat', 'landing', 'allocations.allocatable'])
         ->where('user_id', auth()->id())
         ->get()
  ```

### Indexes on Database
- Index on `user_id` (user data isolation)
- Index on `boat_id`, `landing_id` (common filters)
- Index on `date` (sorting)
- Composite indexes on frequently filtered columns

---

## Notes for AI Recreation

### Important Conventions
1. **User Isolation**: ALL queries must filter by `auth()->id()` or `user_id`
2. **Decimal Precision**: Use `decimal:12,2` for all monetary fields
3. **Status Fields**: Use string/enum for status, not boolean combinations
4. **Polymorphic Relations**: PaymentAllocation and Transaction use morphTo/morphMany
5. **Service Layer**: Business logic in services, not controllers
6. **Validation**: Use FormRequest classes for validation
7. **Transactions**: Use DB::transaction() wrapper for multi-step operations

### View Integration
- Use Tailwind CSS (CDN in production, build in development)
- Modal dialogs for forms
- Inline editing where appropriate
- Real-time balance calculations (JavaScript)
- Responsive grid layouts

### Database-First Approach
- Create migrations before models
- Models auto-cast decimal/date fields
- Relationships defined in models
- Use model accessor methods for calculated fields

---

## End of Specification

This comprehensive specification includes all features, recent bugfixes, and corrections made through April 16, 2026. Use this document to recreate an exact copy of the CFH Fund Management application.

**Last Updated**: April 16, 2026  
**Version**: 2.0  
**Total Features**: 50+  
**Models**: 13  
**Controllers**: 18  
**Routes**: 80+  
**Migrations**: 25+  
**Services**: 7
