# Architecture

**Analysis Date:** 2026-04-22

## Pattern Overview

**Overall:** Service-Oriented Architecture with Transaction-Based Operations

**Key Characteristics:**
- Controller-based request handling with form request validation
- Service layer encapsulating business logic (9 dedicated services)
- Eloquent ORM with polymorphic relationships for flexible data modeling
- User-based multi-tenancy isolation at the database level
- Complete audit trail via Transaction model

## Layers

### Controllers (`app/Http/Controllers/`)
- **Purpose:** Handle HTTP requests, orchestrate between views and services
- **Location:** `app/Http/Controllers/`
- **Contains:** 19 controllers including BoatController, BuyerController, LandingController, InvoiceController, ExpenseController, ReceiptController, PaymentController, CashController, LoanController, DashboardController, UserController, BackupController, ReportController, BankManagementController, CashReportController, UnlinkedExpenseController
- **Depends on:** Services, Models, Form Requests
- **Used by:** Routes (`routes/web.php`)

### Services (`app/Services/`)
- **Purpose:** Encapsulate business logic, transaction management, and complex calculations
- **Location:** `app/Services/`
- **Contains:**
  - `PaymentPostingService.php` - Payment creation with allocations and transaction logging
  - `InvoicePostingService.php` - Receipt recording and invoice updates
  - `ExpenseSettlementService.php` - Expense payment status management
  - `LandingSummaryService.php` - Landing financial calculations and status updates
  - `CashSourceTrackingService.php` - Cash flow tracking, balance calculations
  - `DashboardSummaryService.php` - Aggregated dashboard data
  - `LoanTrackingService.php` - Loan lifecycle management
  - `InvoiceImportService.php` - CSV/batch invoice import parsing
  - `UserDatabaseService.php` - User database provisioning
- **Depends on:** Models, DB facade
- **Used by:** Controllers

### Models (`app/Models/`)
- **Purpose:** Eloquent ORM models representing database entities
- **Location:** `app/Models/`
- **Contains:** 14 models - User, Boat, Buyer, Landing, Expense, Invoice, Receipt, Payment, PaymentAllocation, Transaction, Loan, ExpenseType, PaymentType, LoanSource
- **Depends on:** Database
- **Used by:** Controllers, Services

### Form Requests (`app/Http/Requests/`)
- **Purpose:** Input validation and authorization for controller actions
- **Location:** `app/Http/Requests/`
- **Contains:** StoreBoatRequest, UpdateBoatRequest, StoreBuyerRequest, UpdateBuyerRequest, StoreLandingRequest, UpdateLandingRequest, StoreExpenseRequest, UpdateExpenseRequest, StoreInvoiceRequest, UpdateInvoiceRequest, StoreReceiptRequest, StorePaymentRequest, UpdatePaymentRequest
- **Depends on:** Laravel ValidatesRequests
- **Used by:** Controllers

### Middleware (`app/Http/Middleware/`)
- **Purpose:** Request filtering and authorization
- **Location:** `app/Http/Middleware/`
- **Contains:** AdminMiddleware
- **Used by:** Routes (admin-protected routes)

### Providers (`app/Providers/`)
- **Purpose:** Service registration and application bootstrapping
- **Location:** `app/Providers/`
- **Contains:**
  - `AppServiceProvider.php` - Application service bindings
  - `MultiTenantServiceProvider.php` - User database switching logic
  - `TelescopeServiceProvider.php` - Debugging tool configuration

## Styling Architecture

### Tailwind Integration
- **CDN Source:** `https://cdn.tailwindcss.com` (loaded in `resources/views/layouts/main.blade.php`)
- **Custom Configuration:** Inline Tailwind config block defines project-specific color palette
- **Approach:** Tailwind utilities used directly in Blade templates via `class="..."` attributes

### Custom Tailwind Colors (defined in main.blade.php)
```javascript
colors: {
    void: '#000000',           // Pure black background
    deepTeal: '#02090A',       // Darkest teal-black
    darkForest: '#061A1C',      // Dark forest green
    forest: '#102620',          // Forest green
    darkCardBorder: '#1E2C31', // Card border color
    neon: '#36F4A4',            // Neon green accent (primary brand)
    aloe: '#C1FBD4',           // Light green
    pistachio: '#D4F9E0',      // Pale green
    shade30: '#D4D4D8',        // Light gray
    muted: '#A1A1AA',          // Muted gray
    shade50: '#71717A',        // Medium gray
    shade60: '#52525B',        // Dark gray
    shade70: '#3F3F46',        // Darker gray
}
```

### Custom CSS Classes (in main.blade.php style block)

#### Glassmorphism Card - `.glass-card`
- **Purpose:** Main content container with glassmorphism effect
- **Styles:** `background: #02090A`, `backdrop-filter: blur(10px)`, `border: 1px solid #1E2C31`
- **Box Shadow:** Multi-layer shadow with inset highlight
- **Usage:** Wraps dashboard cards, forms, tables, and modals
- **Files:** All content views use this class on primary containers

#### Button Classes

**`.btn-primary`** - Filled white button for primary actions
- White background (#FFFFFF), black text (#000000)
- Pill shape (`border-radius: 9999px`)
- Padding: 12px 26px 12px 16px
- Hover: opacity 0.9
- Focus: 2px neon (#36F4A4) outline with 2px offset

**`.btn-secondary`** - Outlined button for secondary actions
- Transparent background, white text
- 2px white border, pill shape
- Hover: inverts to white bg, black text
- Focus: 2px neon outline with 2px offset

#### Sidebar Navigation - `.sidebar-link`
- **Default:** Transparent bg, gray-300 text, 3px padding
- **Hover:** `background: rgba(255, 255, 255, 0.1)`
- **Active:** `background: rgba(54, 244, 164, 0.15)`, `border-left: 3px solid #36F4A4`
- **Transition:** `all 0.3s ease`

#### Scrollbar Styling
- **Track:** `#061A1C` (darkForest)
- **Thumb:** `rgba(54, 244, 164, 0.4)` (neon at 40% opacity)
- **Thumb Hover:** `rgba(54, 244, 164, 0.6)` (neon at 60% opacity)
- **Applies to:** All scrollable elements via global `*` selector
- **Style:** `scrollbar-width: thin` for Firefox compatibility

#### Form Input Styling
- **Background:** `#061A1C` (darkForest)
- **Border:** `1px solid #3F3F46` (shade70)
- **Border Radius:** 8px
- **Padding:** 12px 16px
- **Text Color:** White (#FFFFFF)
- **Placeholder:** `#71717A` (shade50)
- **Focus State:** `border-color: #36F4A4`, 2px neon outline, no offset
- **Transition:** `border-color 200ms ease`

### Template Styling Patterns

#### Dashboard Cards (`resources/views/dashboard/index.blade.php`)
- **Container:** `glass-card rounded-xl p-6`
- **Grid:** `grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6`
- **Status Colors:** `text-green-400`, `text-blue-400`, `text-yellow-400`, `text-red-400`, `text-orange-400`, `text-cyan-400`, `text-pink-400`
- **Hover Effect:** `hover:bg-white/10 transition-colors`
- **Icon Backgrounds:** `bg-{color}-500/20` with rounded-full icon container

#### Table Styling (`resources/views/invoices/index.blade.php`)
- **Container:** `glass-card rounded-xl overflow-x-auto table-container` with `max-height: 750px; overflow-y: auto`
- **Table Wrapper Class:** `.table-container` with `-webkit-overflow-scrolling: touch`
- **Header:** `bg-gray-800/50` with `text-gray-400 text-sm`
- **Rows:** `border-t border-gray-700/50 hover:bg-white/5`
- **Status Badges:** `bg-{color}-500/20 text-{color}-400` with `rounded text-xs`

#### Form Styling (create/edit views)
- **Form Container:** `glass-card rounded-xl p-6 max-w-{size}`
- **Input Classes:** `w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white`
- **Focus Override:** `focus:border-blue-500 focus:outline-none` (note: overrides global neon focus)
- **Labels:** `block text-sm text-gray-400 mb-1`
- **Submit Button:** `bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg`
- **Cancel Link:** `px-4 py-2 text-gray-400 hover:text-white`

### Component Templates

#### Delete Modal (`resources/views/components/delete-modal.blade.php`)
- **Backdrop:** `fixed inset-0 bg-black/50`
- **Container:** `glass-card rounded-xl p-6 w-full max-w-lg mx-4`
- **Warning Box:** `bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4`
- **Confirm Box:** `bg-red-500/20 border border-red-500/50 rounded-lg p-4`
- **Close Button:** X icon with `text-gray-400 hover:text-white`
- **Delete Button:** `bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg`

#### Paste Import (`resources/views/components/paste-import.blade.php`)
- **Container:** `glass-card rounded-xl p-6`
- **Textarea:** `w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-sm`
- **Preview Button:** `bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg`

### Theme Transition Notes (Glassmorphism → Intercom-inspired)

**Current Theme:** Dark glassmorphism with neon green (#36F4A4) accents on black backgrounds

**Target Theme:** Warm editorial design
- Warm cream: `#faf9f6`
- Off-black: `#111111`
- Fin Orange: `#ff5600`

**Styling Changes Required:**
1. Update Tailwind config colors in `main.blade.php`
2. Replace `.glass-card` background from `#02090A` to warm tones
3. Update scrollbar thumb color from neon to Fin Orange
4. Update `.sidebar-link.active` highlight color
5. Update form input focus colors
6. Update button styles for new color scheme
7. Update status badge colors for warmer palette

## Data Flow

### Payment Flow:
1. User submits payment via `PaymentController@store`
2. `StorePaymentRequest` validates input (cash/bank balance checks)
3. `PaymentPostingService@postPayment` executes within `DB::transaction()`
4. Payment record created
5. `PaymentAllocation` records created for each expense/landing
6. `ExpenseSettlementService` updates expense statuses
7. `LandingSummaryService` updates landing status
8. `Transaction` audit record created
9. Response returned to user

### Invoice/Receipt Flow:
1. Buyer pays invoice → Receipt form submitted
2. `ReceiptController@store` validates via `StoreReceiptRequest`
3. `InvoicePostingService@postReceipt` executes transactionally
4. Receipt created, Invoice updated (received_amount, pending_amount, status)
5. Transaction audit record created

### Cash-to-Bank Deposit Flow:
1. User creates deposit via `CashController@storeDeposit`
2. `CashSourceTrackingService@depositCashToBank` validates receipt balance
3. Transaction created with `cash_source_receipt_id` linkage

## Key Abstractions

### Polymorphic Relationships
- **PaymentAllocation:** Links payments to either Expense or Landing via `allocatable_type`/`allocatable_id`
- **Transaction:** Linked to Receipt, Payment, or Loan via `transactionable_type`/`transactionable_id`
- **Location:** `app/Models/PaymentAllocation.php`, `app/Models/Transaction.php`

### Calculated Attributes
- **Landing:** `total_expenses`, `net_owner_payable`, `owner_pending`, `total_invoices`, `total_received`
- **Buyer:** `total_purchased`, `total_received`, `total_pending`
- **Loan:** `outstanding_amount`
- **Implementation:** Laravel accessors with `getXAttribute` naming

### Multi-Tenant Isolation
- **Pattern:** User-based isolation using separate SQLite database files per user
- **Implementation:** `MultiTenantServiceProvider` switches `DB::setDefaultConnection('tenant')`
- **Database path:** `database/user_{userId}.sqlite`
- **Fallback:** `database/admin.sqlite` for unauthenticated requests

## Entry Points

### Web Routes (`routes/web.php`)
- **Location:** `routes/web.php`
- **Triggers:** HTTP requests matching defined routes
- **Responsibilities:** Route definition, middleware assignment, controller binding

### Authentication (`app/Http/Controllers/Auth/LoginController.php`)
- **Location:** `app/Http/Controllers/Auth/LoginController.php`
- **Triggers:** GET /login, POST /login, POST /logout
- **Responsibilities:** User authentication, session management

## Error Handling

**Strategy:** Laravel default exception handling with custom admin middleware

**Patterns:**
- Form Request validation with redirect back on failure
- Service layer throws `\InvalidArgumentException` for business rule violations
- Controllers wrap operations in try-catch for user-friendly error messages
- `DB::transaction()` ensures atomicity for financial operations

## Cross-Cutting Concerns

**Logging:** Laravel Telescope for development debugging (`app/Providers/TelescopeServiceProvider.php`)

**Validation:** Form Requests (`app/Http/Requests/`) with Laravel validation rules

**Authentication:** Laravel's authenticatable User model with `role` and `is_active` fields

**Authorization:** Admin middleware for protected routes (user management, backups, unlinked expenses)

**Styling:** Global styles in `main.blade.php` with component-level Tailwind utilities

---

*Architecture analysis: 2026-04-22*
