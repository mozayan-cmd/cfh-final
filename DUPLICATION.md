# CFH Fund Management - App Duplication Prompt

## Overview

Create a Laravel application called "CFH Fund Management System" for managing boat landings, invoices, receipts, expenses, payments, loans, and cash/bank transactions. The system is designed for fish market fund management where multiple boats land fish, buyers purchase through invoices, and various payment modes (Cash, Bank, GP) are used.

## Technology Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Blade templates with Tailwind CSS 4.x
- **Database**: SQLite (for development, configurable via `.env`)
- **Authentication**: Laravel's built-in auth with custom user model
- **Theme**: Custom theme (NOT glassmorphism - specify theme in theme.md)

---

## Database Schema

### Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['admin', 'user'])->default('user');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
});
```

### Boats Table
```php
Schema::create('boats', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Buyers Table
```php
Schema::create('buyers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Landings Table
```php
Schema::create('landings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('boat_id')->constrained()->onDelete('cascade');
    $table->date('date');
    $table->decimal('gross_value', 15, 2);
    $table->enum('status', ['Open', 'Partial', 'Settled'])->default('Open');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Invoices Table
```php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
    $table->foreignId('boat_id')->constrained()->onDelete('cascade');
    $table->foreignId('landing_id')->constrained()->onDelete('cascade');
    $table->date('invoice_date');
    $table->decimal('original_amount', 15, 2);
    $table->decimal('received_amount', 15, 2)->default(0);
    $table->decimal('pending_amount', 15, 2);
    $table->enum('status', ['Pending', 'Partial', 'Paid'])->default('Pending');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Receipts Table
```php
Schema::create('receipts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
    $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
    $table->foreignId('boat_id')->constrained()->onDelete('cascade');
    $table->foreignId('landing_id')->constrained()->onDelete('cascade');
    $table->date('date');
    $table->decimal('amount', 15, 2);
    $table->enum('mode', ['Cash', 'GP', 'Bank']);
    $table->string('source')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Expenses Table
```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('boat_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('landing_id')->nullable()->constrained()->onDelete('cascade');
    $table->date('date');
    $table->string('type'); // Diesel, Ice, Ration, etc.
    $table->string('vendor_name')->nullable();
    $table->decimal('amount', 15, 2);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('pending_amount', 15, 2);
    $table->enum('payment_status', ['Pending', 'Partial', 'Paid'])->default('Pending');
    $table->boolean('is_linked')->default(false);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Payments Table
```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('landing_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('boat_id')->nullable()->constrained()->onDelete('cascade');
    $table->date('date');
    $table->decimal('amount', 15, 2);
    $table->enum('mode', ['Cash', 'GP', 'Bank']);
    $table->enum('payment_for', ['Owner', 'Expense', 'Loan', 'Other']);
    $table->string('source')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Loans Table
```php
Schema::create('loans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('source'); // Basheer, Personal, Others
    $table->date('date');
    $table->decimal('amount', 15, 2);
    $table->decimal('repaid_amount', 15, 2)->default(0);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Loan Sources Table
```php
Schema::create('loan_sources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->timestamps();
});
```

### Transactions Table
```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type'); // Receipt, Payment, etc.
    $table->enum('mode', ['Cash', 'GP', 'Bank']);
    $table->string('source')->nullable();
    $table->decimal('amount', 15, 2);
    $table->foreignId('boat_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('landing_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('buyer_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');
    $table->morphs('transactionable');
    $table->unsignedBigInteger('cash_source_receipt_id')->nullable();
    $table->date('date');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

## Models & Relationships

### User Model
```php
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool { return $this->id === 1 || $this->role === 'admin'; }
    public function isActive(): bool { return $this->is_active === true || $this->is_active === 1 || $this->is_active === '1'; }
    
    public function boats() { return $this->hasMany(Boat::class); }
    public function buyers() { return $this->hasMany(Buyer::class); }
    public function landings() { return $this->hasMany(Landing::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function receipts() { return $this->hasMany(Receipt::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function loans() { return $this->hasMany(Loan::class); }
}
```

### Boat Model
```php
class Boat extends Model
{
    protected $fillable = ['user_id', 'name', 'notes'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function landings() { return $this->hasMany(Landing::class); }
}
```

### Buyer Model
```php
class Buyer extends Model
{
    protected $fillable = ['user_id', 'name', 'notes'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
```

### Landing Model
```php
class Landing extends Model
{
    protected $fillable = ['user_id', 'boat_id', 'date', 'gross_value', 'status', 'notes'];
    protected $casts = ['date' => 'date', 'gross_value' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function receipts() { return $this->hasMany(Receipt::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    
    public function getSummaryAttribute() {
        $expenses = $this->expenses()->sum('amount');
        $receipts = $this->receipts()->sum('amount');
        $payments = $this->payments()->where('payment_for', 'Owner')->sum('amount');
        $netOwnerPayable = $this->gross_value - $expenses;
        $ownerPending = max(0, $netOwnerPayable - $payments);
        
        return [
            'total_expenses' => $expenses,
            'total_receipts' => $receipts,
            'net_owner_payable' => $netOwnerPayable,
            'total_owner_paid' => $payments,
            'owner_pending' => $ownerPending,
        ];
    }
}
```

### Invoice Model
```php
class Invoice extends Model
{
    protected $fillable = ['user_id', 'buyer_id', 'boat_id', 'landing_id', 'invoice_date', 'original_amount', 'received_amount', 'pending_amount', 'status', 'notes'];
    protected $casts = ['invoice_date' => 'date', 'original_amount' => 'decimal:2', 'received_amount' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function buyer() { return $this->belongsTo(Buyer::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
    public function landing() { return $this->belongsTo(Landing::class); }
    public function receipts() { return $this->hasMany(Receipt::class); }
}
```

### Receipt Model
```php
class Receipt extends Model
{
    protected $fillable = ['user_id', 'buyer_id', 'invoice_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode', 'source', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function buyer() { return $this->belongsTo(Buyer::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
    public function landing() { return $this->belongsTo(Landing::class); }
    public function transaction() { return $this->morphOne(Transaction::class, 'transactionable'); }
}
```

### Expense Model
```php
class Expense extends Model
{
    protected $fillable = ['user_id', 'boat_id', 'landing_id', 'date', 'type', 'vendor_name', 'amount', 'paid_amount', 'pending_amount', 'payment_status', 'is_linked', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2', 'is_linked' => 'boolean'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
    public function landing() { return $this->belongsTo(Landing::class); }
}
```

### Payment Model
```php
class Payment extends Model
{
    protected $fillable = ['user_id', 'landing_id', 'boat_id', 'date', 'amount', 'mode', 'payment_for', 'source', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function landing() { return $this->belongsTo(Landing::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
}
```

### Loan Model
```php
class Loan extends Model
{
    protected $fillable = ['user_id', 'source', 'date', 'amount', 'repaid_amount', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    
    public function getRelatedRecords() {
        return [
            'payments' => $this->user->payments()->where('payment_for', 'Loan')->where('source', $this->source)->get(),
        ];
    }
}
```

### Transaction Model
```php
class Transaction extends Model
{
    protected $fillable = ['user_id', 'type', 'mode', 'source', 'amount', 'boat_id', 'landing_id', 'buyer_id', 'invoice_id', 'transactionable_type', 'transactionable_id', 'cash_source_receipt_id', 'date', 'notes'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function boat() { return $this->belongsTo(Boat::class); }
    public function landing() { return $this->belongsTo(Landing::class); }
    public function buyer() { return $this->belongsTo(Buyer::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function transactionable() { return $this->morphTo(); }
}
```

---

## Core Features & Workflow

### 1. Dashboard
- **Route**: `GET /` → `DashboardController@index`
- **Functionality**:
  - Display summary cards: Cash in Hand, Cash at Bank, Buyer Pending, Boat Owner Pending, Expense Pending, Outstanding Loans, Overdue Landings, Unlinked Expenses, Loan Repayments
  - "Overdue Landings" = All unsettled landings (status ≠ 'Settled') for current user
  - Recent Landings (last 5)
  - Recent Receipts (last 5)
  - Recent Payments (last 5)
  - Pending Settlements

### 2. Boat Management
- **Routes**: `GET /boats`, `POST /boats`, `GET /boats/{boat}`, `PUT /boats/{boat}`, `DELETE /boats/{boat}`
- **Functionality**: CRUD for boats assigned to user

### 3. Buyer Management
- **Routes**: `GET /buyers`, `POST /buyers`, `GET /buyers/{buyer}`, `PUT /buyers/{buyer}`, `DELETE /buyers/{buyer}`
- **Functionality**: CRUD for buyers

### 4. Landings (Core Workflow)
- **Routes**: Full resource controller
- **Workflow**:
  1. Create Landing (date, boat, gross value)
   - Option to link unlinked expenses during creation
  2. Landing auto-creates Invoice (original_amount = gross_value)
  3. View Landing Settlement page showing:
     - Summary cards (Gross Sale, Total Expenses, Net Owner Payable, Owner Paid, Owner Pending)
     - Invoices table (buyer-wise)
     - Expenses table
     - Receipts (buyer payments)
     - Payments to Owner
  4. Edit Landing (date, gross value, notes)
  5. Delete Landing (with cascade)

### 5. Invoices
- **Routes**: `GET /invoices`, `POST /invoices`, `GET /invoices/{invoice}`, `PUT /invoices/{invoice}`, `DELETE /invoices/{invoice}`
- **Special Routes**:
  - `GET /invoices/import` - Import page
  - `POST /invoices/preview` - Preview import data
  - `POST /invoices/process-import` - Process import
  - `GET /invoices/export` - Export to CSV
  - `GET /invoices/landings/{boat}` - Get invoices by boat
  - `GET /invoices/pending-landings/{boat}` - Get pending landing dates
- **Functionality**:
  - Invoice status auto-calculated: Paid (pending ≤ 0), Partial (received > 0), Pending
  - Bulk import via CSV or paste data
  - CSV Export

### 6. Receipts (Buyer Payments)
- **Routes**: `GET /receipts`, `POST /receipts`, `GET /receipts/{receipt}`, `PUT /receipts/{receipt}`, `DELETE /receipts/{receipt}`
- **Special Routes**:
  - `GET /receipts/create` - Create receipt form
  - `GET /receipts/export` - Export to CSV
  - `POST /receipts/import` - Import receipts
  - `GET /receipts/invoices/{buyer}` - Get invoices by buyer
  - `GET /receipts/api/buyers` - Get buyers by boat
  - `GET /receipts/api/invoices` - Get invoices by buyer & landing
- **IMPORTANT Business Logic**:
  - When creating receipt with mode 'Cash':
    - Update invoice received_amount & pending_amount
    - Create Transaction record (type='Receipt', mode='Cash')
  - When creating receipt with mode 'Bank' or 'GP':
    - Update invoice received_amount & pending_amount ONLY
    - **DO NOT create Transaction** (bank/GP receipts are direct payments, not cash deposits)
  - On update: if mode changes from Cash to Bank/GP, delete the Transaction
  - On delete: revert invoice amounts, delete Transaction if exists

### 7. Expenses
- **Routes**: Full resource + import/export
- **Functionality**:
  - Link expenses to landings
  - Track payment status (Pending/Partial/Paid)
  - Unlinked expenses can be attached to landings later
  - Expense types: Diesel, Ice, Ration, etc. (user-defined)

### 8. Payments
- **Routes**: `GET /payments`, `POST /payments`, `GET /payments/{payment}`, `PUT /payments/{payment}`, `DELETE /payments/{payment}`
- **Payment For**: Owner, Expense, Loan, Other
- **Modes**: Cash, GP, Bank
- **Special Routes**:
  - `GET /payments/landings/{boat}` - Get landings by boat
  - `GET /payments/expenses/{boat}` - Get expenses by boat

### 9. Loans
- **Routes**: `GET /loans`, `POST /loans`, `POST /loans/{loan}/repay`
- **Functionality**:
  - Track loans from different sources (Basheer, Personal, Others)
  - Record repayments
  - Calculate outstanding per source
  - Loan sources are user-defined

### 10. Cash Management
- **Routes**: 
  - `GET /cash/utilization` - Cash utilization view
  - `GET /cash/deposit` - Deposit cash to bank form
  - `POST /cash/deposit` - Process deposit
  - `GET /cash/report` - Cash report
  - `GET /cash/bank-report` - Bank report
- **Functionality**:
  - Track cash in hand vs bank
  - Deposit cash to bank (creates Transaction with cash_source_receipt_id)

### 11. Bank Management
- **Routes**: `GET /bank`, `POST /bank/withdraw`
- **Functionality**: Manage bank withdrawals

### 12. User Management (Admin Only)
- **Routes**: Full resource + toggle active
- **Functionality**: Admin can create/manage users, activate/deactivate

### 13. Reports (Admin Only)
- **Routes**: `GET /reports`, `GET /reports/generate`
- **Functionality**: Generate various reports

### 14. Backup System (Admin Only)
- **Routes**: `GET /backups`, `POST /backups/create`, `POST /backups/clear`, `GET /backups/download/{filename}`, `POST /backups/restore`, `DELETE /backups/{filename}`
- **Functionality**: Database backup/restore functionality

### 15. Unlinked Expenses (Admin Only)
- **Routes**: `GET /unlinked-expenses`, `PUT /unlinked-expenses/{expense}`
- **Functionality**: Manage expenses not linked to any landing

---

## Service Classes

### DashboardSummaryService
Handles dashboard summary calculations:
- `getCashInHand()`: Cash Receipts + Loan Receipts + Cash Withdrawals - Cash Payments - Deposits to Bank
- `getCashAtBank()`: Bank/GP Receipts + Deposits to Bank + Lumpsum Bank Deposits + Loan Receipts - Bank/GP Payments - Cash Withdrawals
- `getBuyerPending()`: Total pending invoice amounts across all buyers
- `getOwnerPending()`: Total pending owner payments (net payable - owner paid)
- `getExpensePending()`: Total pending expense amounts
- `getOutstandingLoans()`: Total outstanding loan amounts per source
- `getOverdueLandings()`: Count of unsettled landings (status ≠ 'Settled')
- `getUnlinkedExpenses()`: Total unlinked expense amounts
- **Withdrawals**: Stored as Transaction (type='Payment', mode='Cash', source='Cash', notes LIKE '%Withdrawal%')

### InvoicePostingService
Handles receipt posting logic:
- `postReceipt(array $data)`: Creates receipt, updates invoice, creates Transaction ONLY for Cash mode
- `depositCashToBank(array $data)`: Handles cash deposit to bank
- `calculateStatus(float $received, float $pending)`: Returns Paid/Partial/Pending

---

## Middleware & Auth

- **Authentication**: All routes except login require `auth` middleware
- **Admin Routes**: User management, backups, reports use `admin` middleware
- **User Scope**: All queries filter by `auth()->id()` to ensure data isolation

---

## Import/Export Features

### CSV Import Format
**Invoices**: Paste amounts or upload CSV with 'amount' column
**Receipts**: Paste amounts or upload CSV
**Expenses**: Paste amounts or upload CSV
**Payments**: Paste amounts or upload CSV

### Export Format
All exports generate CSV with columns: ID, Date, Buyer, Invoice Date, Boat, Landing Date, Amount, Mode, Source, Notes

---

## Key Implementation Notes

1. **Receipt Transaction Bug Fix**: Only create Transaction records for 'Cash' mode receipts. Bank/GP receipts are direct bank payments.
2. **Landing Summary**: Calculated dynamically via `getSummaryAttribute()`
3. **Invoice Status**: Auto-calculated on every receipt/payment update
4. **User Isolation**: All queries scoped to `auth()->id()`
5. **Soft Deletes**: Not used - use cascade deletes
6. **Database**: SQLite by default (configure via DB_CONNECTION in .env)

---

## Theme Instructions

**DO NOT use glassmorphism design.** Use a clean, solid-color theme. Theme specifications are in `theme.md` file. Apply the theme from `theme.md` after building all functionality.

---

## Sample Data for Testing

Create these users:
1. admin@example.com (admin role)
2. user@example.com (user role)
3. rishad@cfh.com (user role)

Create sample boats, buyers, landings with invoices, receipts, expenses, payments, and loans to test the full workflow.
