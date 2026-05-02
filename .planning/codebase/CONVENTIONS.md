# Coding Conventions

**Analysis Date:** 2026-04-22

## Naming Patterns

### Files
- **Models:** PascalCase singular (`Payment.php`, `Invoice.php`, `Expense.php`)
- **Controllers:** PascalCase with `Controller` suffix (`PaymentController.php`, `ExpenseController.php`)
- **Services:** PascalCase with `Service` suffix (`PaymentPostingService.php`, `InvoiceImportService.php`)
- **Requests:** `Store*Request.php`, `Update*Request.php` pattern (`StorePaymentRequest.php`)
- **Views:** snake_case Blade files (`payment_edit.blade.php`, `payments_index.blade.php`)

### Classes and Namespaces
- **Namespace:** `App\Models`, `App\Services`, `App\Http\Controllers`
- **Model Relationships:** camelCase method names (`landings()`, `payments()`, `allocations()`)
- **Relationship Return Types:** PHP 8+ return type declarations (`public function landings(): HasMany`)

### Database Columns
- **Convention:** snake_case (`user_id`, `landing_id`, `cash_source_receipt_id`)
- **Foreign Keys:** `{model_name}_id` pattern

### Variables
- **Local Variables:** camelCase (`$data`, `$payment`, `$landingId`)
- **Request Data:** Often extracted from validated request (`$data = $request->validated()`)

## Code Style

### Formatting
- **Tool:** Laravel Pint (configured via `pint.json` or composer scripts)
- **PHP Version:** 8.2+ (uses constructor property promotion, typed properties)

### Indentation
- 4 spaces (standard PSR-12)

### Braces
- Opening brace on same line for classes/methods
- Alternative syntax NOT used

## Eloquent Conventions

### Model Structure (`app/Models/*.php`)

**Fillable Array Pattern:**
```php
protected $fillable = [
    'user_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode',
    'source', 'payment_for', 'loan_reference', 'notes', 'vendor_name',
];
```

**Casts Pattern:**
```php
protected $casts = [
    'date' => 'date',
    'amount' => 'decimal:2',
];
```

**Relationships:**
- `BelongsTo` for single parent relations (`user()`, `boat()`, `landing()`)
- `HasMany` for child collections (`landings()`, `expenses()`, `invoices()`)
- `MorphOne` / `MorphMany` for polymorphic relations (`transaction()`, `paymentAllocations()`)
- `MorphTo` onTransaction model (`transactionable()`)

### Model Method Patterns

**Computed Attributes:**
```php
public function getTotalExpensesAttribute()
{
    return $this->expenses()->sum('amount');
}
```

**getRelatedRecords() Pattern:**
```php
public function getRelatedRecords(): array
{
    $related = [];
    if ($this->expenses()->exists()) {
        $related[] = [
            'type' => 'Expenses',
            'count' => $this->expenses()->count(),
            'amount' => $this->expenses()->sum('amount'),
        ];
    }
    return $related;
}
```

### User Model Specifics
- Extends `Authenticatable`
- Uses `HasFactory` trait for factories
- Uses `Notifiable` trait
- `casts()` method returns array with explicit types
- `password` in `$hidden` array
- `boot()` method for model events (e.g., auto-admin on first user)

## Service Layer Patterns

### Location
`app/Services/*.php`

### Constructor Injection
```php
class PaymentPostingService
{
    protected ExpenseSettlementService $expenseService;
    protected LandingSummaryService $landingService;

    public function __construct(
        ExpenseSettlementService $expenseService,
        LandingSummaryService $landingService
    ) {
        $this->expenseService = $expenseService;
        $this->landingService = $landingService;
    }
}
```

### Transaction Wrapping
```php
public function postPayment(array $data): Payment
{
    return DB::transaction(function () use ($data) {
        $payment = Payment::create([...]);
        // ... operations
        return $payment;
    });
}
```

### Service Method Patterns
- Return types declared on methods
- Use dependency injection for other services
- Wrap multiple database operations in `DB::transaction()`
- Services receive arrays of data, not request objects

## Validation Approach

### Form Request Pattern (`app/Http/Requests/`)

```php
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'boat_id' => 'nullable|exists:boats,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:Cash,GP,Bank',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom cross-field validation
        });
    }
}
```

### Validation Rules Used
- `required`, `nullable`
- `exists:table,column`
- `numeric`, `string`, `array`
- `min`, `max`
- `in:val1,val2,val3` with `Rule::in()` for dynamic lists
- `file`, `mimes:csv,txt`, `max:10240` for file uploads
- `date`, `unique:table,column`

### Controller Validation
```php
public function store(StorePaymentRequest $request): RedirectResponse
{
    $data = $request->validated();
    // use $data
}
```

## Error Handling

### Controller Patterns

**Authorization Check:**
```php
public function show(Payment $payment): View
{
    if ($payment->user_id !== auth()->id()) {
        abort(403, 'Access denied.');
    }
    // ...
}
```

**Form Validation with Back:**
```php
if ($data['amount'] > $balance) {
    return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds available balance.']);
}
```

**Redirect with Messages:**
```php
return redirect()->route('payments.index')
    ->with('success', "Payment of {$data['amount']} recorded successfully.");
```

### Service Error Handling
- Services typically don't catch exceptions (let them bubble up)
- Try-catch used sparingly in import/parsing logic:
```php
try {
    $invoice = Invoice::create([...]);
} catch (\Exception $e) {
    $errors[] = ['line' => $row['line'], 'reason' => $e->getMessage()];
}
```

### Transaction Rollback
- Database transactions auto-rollback on exception
- No explicit rollback calls needed when using `DB::transaction()`

## Import/Export Patterns

### CSV Import Pattern (InvoiceImportService)
```php
public function parseFile($file): array
{
    $content = file_get_contents($file->getRealPath());
    $lines = array_filter(explode("\n", $content), fn ($line) => trim($line) !== '');
    // parse with regex patterns
}

public function importInvoices(array $rows, int $boatId, int $landingId, string $invoiceDate, int $userId): array
{
    // returns ['imported' => [], 'skipped' => [], 'errors' => []]
}
```

### CSV Export Pattern (PaymentController)
```php
public function export(Request $request)
{
    $payments = $query->orderBy('date', 'desc')->get();
    $lines = ['ID,Date,Boat,Amount,Mode,Source'];
    foreach ($payments as $payment) {
        $lines[] = sprintf('%d,%s,%s,...', $payment->id, ...);
    }
    $content = implode("\n", $lines);
    return response($content, 200, ['Content-Type' => 'text/csv', ...]);
}
```

## Controller Patterns

### Base Controller (`app/Http/Controllers/Controller.php`)
```php
abstract class Controller
{
    protected function getRelatedRecords($model): JsonResponse
    {
        if (! method_exists($model, 'getRelatedRecords')) {
            return response()->json([]);
        }
        return response()->json($model->getRelatedRecords());
    }
}
```

### Resource Controller Methods
- `index(Request $request): View` - list with filters
- `create(Request $request): View` - show creation form
- `store(Store*Request $request): RedirectResponse` - handle create
- `show(Model $model): View` - show single record
- `edit(Model $model): View` - show edit form
- `update(Update*Request $request, Model $model): RedirectResponse` - handle update
- `destroy(Model $model): RedirectResponse` - delete record

### Query Building Pattern
```php
$query = Payment::with(['boat', 'landing', 'allocations'])
    ->where('user_id', auth()->id())
    ->orderBy('date', 'desc');

if ($request->filled('boat_id')) {
    $query->where('boat_id', $request->boat_id);
}
$payments = $query->get();
```

## Multi-Tenancy Pattern

### User-Scoped Data
- All queries include `where('user_id', auth()->id())` or equivalent scope
- Controllers check ownership before operations
- Models have `user_id` in fillable array

## Styling Conventions

### Theme System Overview

**Current Theme:** Glassmorphism (dark theme with neon green #36F4A4 accent)
**Theme Change In Progress:** Transitioning to Intercom-inspired warm editorial design
- New palette: warm cream #faf9f6, off-black #111111, Fin Orange #ff5600

**CSS Architecture:**
- Uses Tailwind CSS v4 via Vite (`@tailwindcss/vite`)
- Primary stylesheet: `resources/css/app.css`
- Inline styles defined in `resources/views/layouts/main.blade.php`
- Tailwind config via CDN script in main layout for runtime configuration

### Color Variables

**Defined in `resources/views/layouts/main.blade.php` (lines 10-27) and `resources/css/app.css` (lines 11-24):**

| Variable | Value | Usage |
|----------|-------|-------|
| `--color-void` | `#000000` | Primary background |
| `--color-deepTeal` | `#02090A` | Card backgrounds, dark surfaces |
| `--color-darkForest` | `#061A1C` | Sidebar, scrollbar track |
| `--color-forest` | `#102620` | Elevated surfaces |
| `--color-darkCardBorder` | `#1E2C31` | Card borders |
| `--color-neon` | `#36F4A4` | Primary accent, active states, focus rings |
| `--color-aloe` | `#C1FBD4` | Success tint |
| `--color-pistachio` | `#D4F9E0` | Success backgrounds |
| `--color-shade30` | `#D4D4D8` | Light borders |
| `--color-muted` | `#A1A1AA` | Muted text |
| `--color-shade50` | `#71717A` | Secondary text |
| `--color-shade60` | `#52525B` | Disabled states |
| `--color-shade70` | `#3F3F46` | Form borders |

**New Theme Variables (for Intercom-inspired warm editorial):**
| Variable | Value | Usage |
|----------|-------|-------|
| `--color-cream` | `#faf9f6` | Primary background |
| `--color-offblack` | `#111111` | Primary text, dark surfaces |
| `--color-fin-orange` | `#ff5600` | Primary accent (CTA buttons, links) |

### Glass Card Pattern

**Class:** `.glass-card`

**Defined in `resources/views/layouts/main.blade.php` (lines 37-42):**

```css
.glass-card {
    background: #02090A;
    backdrop-filter: blur(10px);
    border: 1px solid #1E2C31;
    box-shadow: rgba(0,0,0,0.1) 0px 0px 0px 1px, rgba(0,0,0,0.1) 0px 2px 2px,
                rgba(0,0,0,0.1) 0px 4px 4px, rgba(0,0,0,0.1) 0px 8px 8px,
                rgba(255,255,255,0.03) 0px 1px 0px inset;
}
```

**Usage:** Applied to containers, modals, cards throughout the application.

**Example usage in views:**
```blade
<div class="glass-card p-8 w-full max-w-md">
    <!-- content -->
</div>
```

### Button Variants

**Primary Button (.btn-primary):** `resources/views/layouts/main.blade.php` (lines 82-96)

```css
.btn-primary {
    background: #FFFFFF;
    color: #000000;
    border-radius: 9999px;
    padding: 12px 26px 12px 16px;
    font-weight: 500;
    transition: all 200ms ease;
}
.btn-primary:hover {
    opacity: 0.9;
}
.btn-primary:focus {
    outline: 2px solid #36F4A4;
    outline-offset: 2px;
}
```

**Secondary Button (.btn-secondary):** `resources/views/layouts/main.blade.php` (lines 97-113)

```css
.btn-secondary {
    background: transparent;
    color: #FFFFFF;
    border: 2px solid #FFFFFF;
    border-radius: 9999px;
    padding: 12px 26px 12px 16px;
    font-weight: 500;
    transition: all 200ms ease;
}
.btn-secondary:hover {
    background: #FFFFFF;
    color: #000000;
}
.btn-secondary:focus {
    outline: 2px solid #36F4A4;
    outline-offset: 2px;
}
```

**Alternative Button Styles (inline in views):**
```blade
<!-- Inline primary style -->
<button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">

<!-- Inline danger style -->
<button class="bg-red-500/20 text-red-400 hover:bg-red-500/30 px-4 py-2 rounded-lg">
```

### Sidebar Navigation

**Class:** `.sidebar-link`

**Defined in `resources/views/layouts/main.blade.php` (lines 43-52):**

```css
.sidebar-link {
    transition: all 0.3s ease;
}
.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.1);
}
.sidebar-link.active {
    background: rgba(54, 244, 164, 0.15);
    border-left: 3px solid #36F4A4;
}
```

**Usage Pattern:**
```blade
<a href="{{ route('dashboard') }}"
   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300
          {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <!-- icon -->
    </svg>
    Dashboard
</a>
```

### Form Input Styling

**Defined in `resources/views/layouts/main.blade.php` (lines 114-129):**

```css
input, select, textarea {
    background: #061A1C;
    border: 1px solid #3F3F46;
    color: #FFFFFF;
    border-radius: 8px;
    padding: 12px 16px;
    transition: border-color 200ms ease;
}
input:focus, select:focus, textarea:focus {
    border-color: #36F4A4;
    outline: 2px solid #36F4A4;
    outline-offset: 0;
}
input::placeholder {
    color: #71717A;
}
```

**Inline Input Variants (used in specific views):**
```blade
<!-- Login page variant -->
<input type="email" name="email"
   class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg
          text-white placeholder-slate-400 focus:outline-none focus:ring-2
          focus:ring-neon focus:border-transparent">

<!-- Payment filter variant -->
<select name="boat_id"
   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2
          text-white focus:border-blue-500 focus:outline-none">
```

### Scrollbar Styling

**Defined in `resources/views/layouts/main.blade.php` (lines 60-81):**

```css
/* Webkit scrollbar */
*::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}
*::-webkit-scrollbar-track {
    background: #061A1C;
}
*::-webkit-scrollbar-thumb {
    background: rgba(54, 244, 164, 0.4);
    border-radius: 9999px;
    border: 2px solid #061A1C;
}
*::-webkit-scrollbar-thumb:hover {
    background: rgba(54, 244, 164, 0.6);
}
*::-webkit-scrollbar-corner {
    background: #061A1C;
}

/* Firefox scrollbar */
* {
    scrollbar-width: thin;
    scrollbar-color: rgba(54, 244, 164, 0.4) #061A1C;
}
```

### Status Badge Patterns

```blade
<!-- Success -->
<span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Settled</span>

<!-- Warning -->
<span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-400">Partial</span>

<!-- Danger -->
<span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400">Pending</span>

<!-- Info -->
<span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-400">Bank</span>
```

### Table Styling

**Container Class:** `.table-container`

```css
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-container table {
    min-width: 600px;
}
```

### Alert/Message Styling

```blade
<!-- Success alert -->
<div class="glass-card p-4 mb-6 border-l-4 border-neon text-neon">
    {{ session('success') }}
</div>

<!-- Error alert -->
<div class="glass-card p-4 mb-6 border-l-4 border-red-500 text-red-400">
    {{ session('error') }}
</div>
```

### Theme Migration Notes

When updating from glassmorphism to Intercom-inspired warm editorial:

1. **Background colors:** Change `#000000` / `#02090A` to `#faf9f6` (cream)
2. **Text colors:** Change `#FFFFFF` to `#111111` (off-black)
3. **Accent color:** Change `#36F4A4` (neon green) to `#ff5600` (Fin Orange)
4. **Card backgrounds:** Update `.glass-card` background from dark to cream
5. **Scrollbars:** Update track/thumb colors to match new theme
6. **Borders:** Update from gray-700 to appropriate warm tones
7. **Sidebar:** Update from dark background to cream with dark text

**Files to update for theme change:**
- `resources/views/layouts/main.blade.php` - inline styles (lines 32-130)
- `resources/css/app.css` - Tailwind theme variables (lines 8-24)

---

*Convention analysis: 2026-04-22*
