# Codebase Structure

**Analysis Date:** 2026-04-22

## Directory Layout

```
debuggingcfhrefinedintercom/
├── app/                    # Application source code
│   ├── Console/           # Artisan commands
│   ├── Http/              # HTTP layer (Controllers, Middleware, Requests)
│   ├── Models/            # Eloquent models
│   ├── Observers/        # Model observers
│   ├── Providers/        # Service providers
│   └── Services/         # Business logic services
├── bootstrap/             # Application bootstrap
├── config/                # Configuration files
├── database/             # Database assets
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   ├── seeders/          # Database seeders
│   └── database.sqlite  # Primary SQLite database
├── public/               # Public entry point
├── resources/            # Views and assets
│   └── views/           # Blade templates
├── routes/               # Route definitions
├── storage/              # Storage (logs, cache, etc.)
├── tests/               # Test files
├── vendor/               # Composer dependencies
├── .env                  # Environment configuration (secrets)
├── .env.example          # Environment template
├── composer.json         # PHP dependencies
├── phpunit.xml          # PHPUnit configuration
└── vite.config.js       # Vite build configuration
```

## Directory Purposes

### `app/` - Application Core
- **Purpose:** All application source code lives here
- **Subdirectories:**
  - `Http/Controllers/` - Request handlers
  - `Http/Middleware/` - Request filtering
  - `Http/Requests/` - Form/input validation
  - `Models/` - Database ORM models
  - `Services/` - Business logic encapsulation
  - `Providers/` - Service registration
  - `Observers/` - Model event handlers
  - `Console/Commands/` - CLI commands

### `database/` - Database Assets
- **Purpose:** Database schema, seed data, and SQLite files
- **Contains:**
  - `migrations/*.php` - Schema migration files (43+ migrations)
  - `seeders/*.php` - Initial data seeders (DatabaseSeeder, AdminUserSeeder)
  - `database.sqlite` - Primary user database
  - `admin.sqlite` - Admin/system database

### `config/` - Configuration Files
- **Purpose:** Laravel configuration
- **Key files:**
  - `app.php` - Application settings (name, env, debug, timezone)
  - `database.php` - Database connections (SQLite, MySQL, PostgreSQL, etc.)
  - `auth.php` - Authentication configuration
  - `session.php` - Session settings
  - `cache.php` - Cache configuration
  - `filesystems.php` - File storage configuration
  - `logging.php` - Logging configuration
  - `queue.php` - Queue configuration
  - `mail.php` - Mail configuration
  - `telescope.php` - Telescope debugging tool config

### `routes/` - Route Definitions
- **Purpose:** Define application routes
- **Key files:**
  - `web.php` - Main web routes (auth, CRUD, reports, cash, loans)
  - `console.php` - Artisan command routes

### `resources/views/` - Blade Templates
- **Purpose:** UI templates rendered by controllers
- **Layout:** Organized by controller/resource name

## View Organization

### Directory Structure (`resources/views/`)

```
resources/views/
├── layouts/
│   └── main.blade.php           # Base layout with sidebar + content area
├── auth/
│   └── login.blade.php          # Login page (extends main but overrides)
├── dashboard/
│   └── index.blade.php          # Dashboard overview
├── boats/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── buyers/
│   ├── index.blade.php
│   └── show.blade.php
├── landings/
│   ├── index.blade.php
│   └── show.blade.php
├── invoices/
│   ├── index.blade.php          # List with filters, inline edit modal
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php           # Shares modal from index
│   ├── import.blade.php
│   └── import-preview.blade.php
├── receipts/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── import.blade.php
│   └── import-preview.blade.php
├── expenses/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── import.blade.php
│   └── import-preview.blade.php
├── payments/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── import.blade.php
│   └── import-preview.blade.php
├── cash/
│   ├── show.blade.php           # Cash utilization overview
│   ├── edit-transaction.blade.php
│   └── deposit.blade.php
├── loans/
│   ├── index.blade.php
│   └── create.blade.php
├── bank-management/
│   └── index.blade.php         # Bank withdrawals
├── reports/
│   ├── index.blade.php         # Reports dashboard
│   ├── cash-report.blade.php
│   ├── bank-report.blade.php
│   └── settlement-pdf.blade.php
├── backups/
│   └── index.blade.php         # Admin-only
├── users/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php          # Admin-only
├── unlinked-expenses/
│   ├── index.blade.php
│   └── edit.blade.php          # Admin-only
├── components/                   # Reusable component templates
│   ├── delete-modal.blade.php
│   ├── delete-confirm.blade.php
│   ├── delete-modal-scripts.blade.php
│   └── paste-import.blade.php
├── landings/
│   ├── index.blade.php
│   └── show.blade.php
└── welcome.blade.php
```

## Key File Locations

### Entry Points
- `public/index.php` - Web entry point
- `artisan` - CLI entry point
- `routes/web.php` - Route definitions

### Configuration
- `config/app.php` - Application config
- `config/database.php` - Database config
- `.env` - Environment variables

### Core Logic
- `app/Services/` - All 9 business service classes
- `app/Models/` - All 14 Eloquent models
- `app/Http/Controllers/` - All 19 controllers

### Styling
- `resources/views/layouts/main.blade.php` - Base layout with all global styles

### Testing
- `tests/` - Test files (PHPUnit)
- `phpunit.xml` - PHPUnit configuration

## Layout System

### Base Layout (`resources/views/layouts/main.blade.php`)

**Purpose:** Single base layout providing sidebar navigation and content wrapper

**Structure:**
```html
<!DOCTYPE html>
<html>
<head>
    <!-- Tailwind CDN + custom config -->
    <!-- Global styles in <style> block -->
</head>
<body class="text-gray-100">
    <div class="flex h-screen overflow-hidden">
        <aside class="fixed left-0 top-0 h-screen w-64 glass-card">
            <!-- Sidebar with logo, nav links, user info -->
        </aside>
        <main class="ml-64 flex-1 p-8 h-screen overflow-y-auto">
            <!-- Flash messages -->
            <!-- @yield('content') -->
        </main>
    </div>
    <!-- Global modals (delete-modal) -->
</body>
</html>
```

**Key Features:**
- Fixed sidebar (w-64) with main content offset (ml-64)
- Tailwind CDN with custom color configuration
- Custom CSS classes for glassmorphism effects
- Global scrollbar styling with neon accent
- Session flash messages (success/error)
- Validation error display
- Delete modal components included globally
- `@yield('content')` for page-specific content

### Child View Inheritance Pattern

All content pages use:
```blade
@extends('layouts.main')

@section('content')
    <!-- Page content -->
@endsection
```

**Styles Cascade:**
1. Base styles from `main.blade.php` `<head>` apply globally
2. Tailwind utilities applied via `class="..."` attributes
3. Component templates (`delete-modal`, `paste-import`) use parent styles
4. Inline modals in list views inherit same glass-card styling

## Component Templates

### Delete Modal Components

**Files:**
- `resources/views/components/delete-modal.blade.php` - Basic modal with fetch-based related records
- `resources/views/components/delete-confirm.blade.php` - Props-based modal with related data passed in
- `resources/views/components/delete-modal-scripts.blade.php` - JavaScript for delete-modal

**Usage:**
```blade
@include('components.delete-modal-scripts')
@include('components.delete-modal')
```

**Script API:**
- `openDeleteModal(formAction, modelType, recordId)` - Opens modal and fetches related records
- `closeDeleteModal()` - Closes modal
- Keyboard: Escape key closes modal

### Paste Import Component

**File:** `resources/views/components/paste-import.blade.php`

**Props:** `$action`, `$placeholder`, `$formatHint`, `$example`, `$cancelUrl`

**Usage:**
```blade
<x-paste-import
    :action="route('invoices.import')"
    placeholder="Paste invoice data..."
    formatHint="Format: date,amount,buyer"
    example="2024-01-15,5000,John Doe"
    :cancelUrl="route('invoices.index')"
>
    <!-- Additional form fields -->
</x-paste-import>
```

## Route Organization

Routes are defined in `routes/web.php` with the following groupings:

### Authentication Routes
- `/login`, `/logout` - LoginController

### Resource Routes (auth protected)
- `boats`, `buyers`, `landings` - Standard REST resources
- `invoices`, `expenses`, `receipts`, `payments` - Extended with import/export

### Feature Routes (auth protected)
- `/cash/*` - Cash management and reports
- `/loans/*` - Loan management
- `/bank/*` - Bank withdrawals
- `/reports/*` - Report generation
- `/backups/*` - Admin-only backup management

### Admin-Only Routes
- `/users/*` - User management
- `/unlinked-expenses` - Unlinked expense management

## Service Classes

| Service | Location | Purpose |
|---------|----------|---------|
| PaymentPostingService | `app/Services/PaymentPostingService.php` | Payment creation with allocations |
| InvoicePostingService | `app/Services/InvoicePostingService.php` | Receipt recording |
| ExpenseSettlementService | `app/Services/ExpenseSettlementService.php` | Expense status updates |
| LandingSummaryService | `app/Services/LandingSummaryService.php` | Landing financial summaries |
| CashSourceTrackingService | `app/Services/CashSourceTrackingService.php` | Cash balance tracking |
| DashboardSummaryService | `app/Services/DashboardSummaryService.php` | Dashboard aggregates |
| LoanTrackingService | `app/Services/LoanTrackingService.php` | Loan management |
| InvoiceImportService | `app/Services/InvoiceImportService.php` | CSV import parsing |
| UserDatabaseService | `app/Services/UserDatabaseService.php` | User DB provisioning |

## Model Classes

| Model | Location | Purpose |
|-------|----------|---------|
| User | `app/Models/User.php` | Authentication, hasMany relationships |
| Boat | `app/Models/Boat.php` | Fishing vessel entity |
| Buyer | `app/Models/Buyer.php` | Fish buyer with calculated totals |
| Landing | `app/Models/Landing.php` | Fishing trip with financial status |
| Expense | `app/Models/Expense.php` | Operational expenses |
| Invoice | `app/Models/Invoice.php` | Buyer invoice for fish sales |
| Receipt | `app/Models/Receipt.php` | Payment receipt from buyer |
| Payment | `app/Models/Payment.php` | Payment made out |
| PaymentAllocation | `app/Models/PaymentAllocation.php` | Polymorphic payment linkage |
| Transaction | `app/Models/Transaction.php` | Complete audit log |
| Loan | `app/Models/Loan.php` | Borrowed funds tracking |
| ExpenseType | `app/Models/ExpenseType.php` | Expense category lookup |
| PaymentType | `app/Models/PaymentType.php` | Payment category lookup |
| LoanSource | `app/Models/LoanSource.php` | Loan source lookup |

## Controller Classes

| Controller | Location | Purpose |
|------------|----------|---------|
| Auth/LoginController | `app/Http/Controllers/Auth/LoginController.php` | Authentication |
| DashboardController | `app/Http/Controllers/DashboardController.php` | Dashboard view |
| BoatController | `app/Http/Controllers/BoatController.php` | Boat CRUD |
| BuyerController | `app/Http/Controllers/BuyerController.php` | Buyer CRUD |
| LandingController | `app/Http/Controllers/LandingController.php` | Landing CRUD + attach expenses |
| InvoiceController | `app/Http/Controllers/InvoiceController.php` | Invoice CRUD + import/export |
| ExpenseController | `app/Http/Controllers/ExpenseController.php` | Expense CRUD + import |
| ReceiptController | `app/Http/Controllers/ReceiptController.php` | Receipt CRUD + import |
| PaymentController | `app/Http/Controllers/PaymentController.php` | Payment CRUD + allocations |
| CashController | `app/Http/Controllers/CashController.php` | Cash utilization + deposits |
| CashReportController | `app/Http/Controllers/CashReportController.php` | Cash/bank reports |
| LoanController | `app/Http/Controllers/LoanController.php` | Loan CRUD + repayments |
| BankManagementController | `app/Http/Controllers/BankManagementController.php` | Bank withdrawals |
| ReportController | `app/Http/Controllers/ReportController.php` | Report generation |
| UserController | `app/Http/Controllers/UserController.php` | User management (admin) |
| BackupController | `app/Http/Controllers/BackupController.php` | Backup management (admin) |
| UnlinkedExpenseController | `app/Http/Controllers/UnlinkedExpenseController.php` | Unlinked expenses (admin) |

## Middleware

| Middleware | Location | Purpose |
|------------|----------|---------|
| AdminMiddleware | `app/Http/Middleware/AdminMiddleware.php` | Admin-only route protection |

## Form Request Classes

| Request | Location | Purpose |
|---------|----------|---------|
| StoreBoatRequest | `app/Http/Requests/StoreBoatRequest.php` | Boat creation validation |
| UpdateBoatRequest | `app/Http/Requests/UpdateBoatRequest.php` | Boat update validation |
| StoreBuyerRequest | `app/Http/Requests/StoreBuyerRequest.php` | Buyer creation validation |
| UpdateBuyerRequest | `app/Http/Requests/UpdateBuyerRequest.php` | Buyer update validation |
| StoreLandingRequest | `app/Http/Requests/StoreLandingRequest.php` | Landing creation validation |
| UpdateLandingRequest | `app/Http/Requests/UpdateLandingRequest.php` | Landing update validation |
| StoreExpenseRequest | `app/Http/Requests/StoreExpenseRequest.php` | Expense creation validation |
| UpdateExpenseRequest | `app/Http/Requests/UpdateExpenseRequest.php` | Expense update validation |
| StoreInvoiceRequest | `app/Http/Requests/StoreInvoiceRequest.php` | Invoice creation validation |
| UpdateInvoiceRequest | `app/Http/Requests/UpdateInvoiceRequest.php` | Invoice update validation |
| StoreReceiptRequest | `app/Http/Requests/StoreReceiptRequest.php` | Receipt creation validation |
| StorePaymentRequest | `app/Http/Requests/StorePaymentRequest.php` | Payment creation validation |
| UpdatePaymentRequest | `app/Http/Requests/UpdatePaymentRequest.php` | Payment update validation |

## Where to Add New Code

### New View/File
- **Primary location:** `resources/views/{resource}/`
- **Layout:** Create in appropriate resource folder, extend `layouts.main`
- **Section:** Use `@section('content')` for main content

### New Component Template
- **Location:** `resources/views/components/`
- **Naming:** `{component-name}.blade.php`
- **Include:** Use `@include('components.component-name')` or `<x-component-name>`

### New Styling
- **Global styles:** Add to `resources/views/layouts/main.blade.php` `<style>` block
- **Component styles:** Create `resources/sass/components/{component}.scss` or use inline Tailwind
- **Tailwind config:** Update inline config in main.blade.php head

### New Model
- Model file: `app/Models/{ModelName}.php`
- Migration: `database/migrations/YYYY_MM_DD_*.php`
- Seeder (if needed): `database/seeders/{ModelName}Seeder.php`
- Controller: `app/Http/Controllers/{ModelName}Controller.php`
- Views: `resources/views/{model-name}/`

### New Service
- Location: `app/Services/{ServiceName}Service.php`
- Register in `app/Providers/AppServiceProvider.php` if dependency injection needed

### New Feature Module
- Controller: `app/Http/Controllers/{Feature}Controller.php`
- Service: `app/Services/{Feature}Service.php` (if business logic needed)
- Form Requests: `app/Http/Requests/{Action}{Model}Request.php`
- Routes: Add to `routes/web.php` in appropriate group
- Views: `resources/views/{feature}/`

### New API Endpoint
- Route: Add to `routes/web.php` or create `routes/api.php`
- Controller method or dedicated API controller

## Special Styling Notes

### Form Input Pattern (Inconsistent Focus Colors)
**Issue:** Global form styling uses neon focus (`border-color: #36F4A4`), but form views override to blue focus (`focus:border-blue-500`)

**Files affected:**
- `resources/views/payments/create.blade.php`
- `resources/views/users/create.blade.php`
- `resources/views/invoices/create.blade.php`
- `resources/views/invoices/index.blade.php`

**Recommendation:** Standardize focus colors across all forms when updating theme.

### Table Container Pattern
Tables use `.table-container` class for horizontal scroll:
```html
<div class="glass-card rounded-xl overflow-x-auto table-container" style="max-height: 750px; overflow-y: auto;">
    <table class="w-full min-w-[800px]">
        <!-- Table content -->
    </table>
</div>
```

### Status Badge Pattern
Inline conditional styling for status badges:
```blade
<span class="px-2 py-1 rounded text-xs 
    @if($invoice->status === 'Paid') bg-green-500/20 text-green-400
    @elseif($invoice->status === 'Partial') bg-yellow-500/20 text-yellow-400
    @else bg-gray-500/20 text-gray-400 @endif">
    {{ $invoice->status }}
</span>
```

---

*Structure analysis: 2026-04-22*
