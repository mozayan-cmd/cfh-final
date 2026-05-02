# Testing Patterns

**Analysis Date:** 2026-04-22

## Test Framework

**Runner:** PHPUnit 11.x (PHPUnit 11.5.50 per composer.json)

**Assertion Library:** PHPUnit built-in assertions

**Additional Testing Tools:**
- FakerPHP/Faker 1.23+ (`fakerphp/faker`)
- Mockery 1.6+ (`mockery/mockery`)

**Run Commands:**
```bash
php artisan test              # Run all tests
php artisan test --unit      # Unit tests only
php artisan test --feature  # Feature tests only
composer test               # Config clear + artisan test
```

**Configuration File:** `phpunit.xml`

## Test File Organization

**Location:** Standard Laravel structure
```
tests/
├── Unit/
│   ├── ExampleTest.php
│   └── ...
└── Feature/
    ├── ExampleTest.php
    └── ...
```

**Namespace:**
- Unit tests: `Tests\Unit`
- Feature tests: `Tests\Feature`

**TestCase Base Class:** `tests/TestCase.php`
```php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```

## Test Structure

### PHPUnit Configuration (`phpunit.xml`)

**Testsuites:**
```xml
<testsuite name="Unit">
    <directory>tests/Unit</directory>
</testsuite>
<testsuite name="Feature">
    <directory>tests/Feature</directory>
</testsuite>
```

**Source Coverage:**
```xml
<source>
    <include>
        <directory>app</directory>
    </include>
</source>
```

**Test Environment:**
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
</php>
```

## Unit Test Pattern

**Location:** `tests/Unit/*.php`

**Structure:** Simple PHPUnit TestCase, no Laravel bootstrap

```php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
```

**Characteristics:**
- No database access
- No HTTP requests
- No Laravel application bootstrap
- Fast execution
- For testing pure PHP logic

## Feature Test Pattern

**Location:** `tests/Feature/*.php`

**Structure:** Laravel TestCase with RefreshDatabase

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```

**Characteristics:**
- Full Laravel application bootstrap
- Database transactions that roll back after each test
- Can use `route()`, `get()`, `post()`, `actingAs()` helpers
- Can assert against HTTP responses, views, database state

## Test Traits Used

**RefreshDatabase:**
- Auto-migrates database before each test
- Wraps each test in a transaction that rolls back
- Uses SQLite in-memory database (`:memory:`)
- Ensures tests don't pollute each other

## Available Test Helpers

### HTTP Methods
- `$this->get($uri)` - GET request
- `$this->post($uri, $data)` - POST request
- `$this->put($uri, $data)` - PUT request
- `$this->delete($uri)` - DELETE request
- `$this->patch($uri, $data)` - PATCH request

### Assertions
- `$response->assertStatus(200)`
- `$response->assertViewHas('key')`
- `$response->assertRedirect('/route')`
- `$this->assertDatabaseHas('table', ['column' => 'value'])`
- `$this->assertAuthenticated()`

### Authentication
- `actingAs($user)` - Simulate authenticated user
- `actingAs($user, 'guard')` - With specific guard

### Session
- `$this->withSession([])` - Set session data
- `$this->withSessionErrors([])` - Set session errors for view tests

## Current Test Coverage

**Actual Test Files:**
- `tests/Unit/ExampleTest.php` - Basic placeholder test
- `tests/Feature/ExampleTest.php` - Basic HTTP test

**Total Test Count:** 2 example tests

**Coverage Areas:**
- No actual application tests present
- Only framework-provided example tests
- All business logic (Services, Models, Controllers) is UNTESTED

**Critical Gaps:**
- No tests for Services (`PaymentPostingService`, `ExpenseSettlementService`, etc.)
- No tests for Models (Eloquent relationships, accessors, mutators)
- No tests for Controllers (CRUD operations, authorization)
- No tests for Form Requests (validation rules)
- No tests for Import/Export functionality

## Test Data and Factories

**Factory Pattern:** Laravel factories in `database/factories/`

**Available Factories:**
- UserFactory (referenced in User model via `HasFactory` trait)

**Example Factory Usage Pattern:**
```php
use Database\Factories\UserFactory;

public function test_something()
{
    $user = User::factory()->create();
    // or with specific attributes:
    $user = User::factory()->create(['name' => 'Test User']);
}
```

## Mocking

**Mockery Integration:** Available via `mockery/mockery`

**Basic Mock Pattern:**
```php
use Mockery::mock;

public function test_service_method()
{
    $mock = mock(ServiceClass::class, function ($mock) {
        $mock->shouldReceive('method')->once()->andReturn($expected);
    });
}
```

**Partial Mock:**
```php
$service = spy(ServiceClass::class);
$service->shouldHaveReceived('method')->with($arg);
```

## CI/CD Testing

**Laravel Sail Test Runner:** `laravel/sail` dev dependency for testing

**Test Scripts (composer.json):**
```json
"scripts": {
    "test": [
        "@php artisan config:clear --ansi",
        "@php artisan test"
    ]
}
```

## Styling/UI Testing

### Testing Theme Changes

The codebase does not currently have automated visual regression tests. When making theme changes (e.g., glassmorphism to Intercom-inspired warm editorial), follow these verification steps:

**Manual Verification Checklist:**
1. **Layout Integrity:** Verify all pages extend `layouts.main` and render correctly
2. **Sidebar Navigation:** Verify `.sidebar-link` active states highlight correctly
3. **Card Components:** Verify `.glass-card` renders on all pages using it
4. **Form Inputs:** Verify input focus states use correct accent color
5. **Button States:** Verify hover/focus states on all button variants
6. **Scrollbar Styling:** Verify scrollbars render with theme colors
7. **Responsive Design:** Verify sidebar collapses properly on mobile

**Pages to Verify After Theme Changes:**
- `resources/views/auth/login.blade.php` - Login page (standalone, not extended)
- `resources/views/layouts/main.blade.php` - Base layout
- `resources/views/dashboard/index.blade.php` - Dashboard with stat cards
- `resources/views/payments/index.blade.php` - List with filters
- `resources/views/payments/create.blade.php` - Form page

### Testing CSS Changes

**Build Commands:**
```bash
npm run build   # Production build
npm run dev     # Development with hot reload
```

**Verification Steps:**
1. Run `npm run build` to ensure CSS compiles without errors
2. Verify Vite build completes successfully
3. Check browser console for any CSS loading errors

### Testing Layout Rendering

**Feature Test Pattern for Layout Verification:**
```php
public function test_layout_renders_with_navigation()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('CFH'); // Sidebar brand
    $response->assertSee('Dashboard'); // Navigation link
}
```

**Testing View Components:**
```php
public function test_dashboard_displays_stat_cards()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Cash in Hand');
    $response->assertSee('Cash at Bank');
    $response->assertSee('Buyer Pending');
}
```

### Verifying Style Classes

**Asserting CSS Classes in Responses:**
```php
public function test_glass_card_class_present()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    // Verify the response contains glass-card class
    $this->assertStringContainsString('glass-card', $response->getContent());
}
```

### Browser-Based Visual Testing

**Note:** The codebase currently has no automated browser testing tools (e.g., Playwright, Dusk). For significant theme changes:

1. **Manual QA Required:** Open application in browser and visually verify
2. **Cross-Browser Testing:** Test in Chrome, Firefox, Safari
3. **Responsive Testing:** Test at 320px, 768px, 1024px, 1440px widths

**Dusk Setup (Recommended for Future):**
```bash
composer require --dev laravel/dusk
php artisan dusk:install
php artisan dusk
```

### Testing Flash Messages

```php
public function test_success_message_displayed()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('payments.store'), [
        // valid payment data
    ]);

    $response->assertRedirect(route('payments.index'));
    $response->assertSessionHas('success');
}
```

### Testing Form Validation Display

```php
public function test_validation_errors_displayed()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('payments.store'), []);

    $response->assertSessionHasErrors();
    $response->assertStatus(302);
}
```

## Best Practices (Observed in Codebase)

**NOT OBSERVED** - The codebase currently lacks comprehensive tests. This section documents patterns that SHOULD be used:

### Model Testing
```php
public function test_model_has_correct_fillable_fields()
{
    $model = new Payment();
    $this->assertEquals(['user_id', 'boat_id', ...], $model->getFillable());
}

public function test_model_casts_dates_correctly()
{
    $model = new Payment();
    $casts = $model->getCasts();
    $this->assertEquals('date', $casts['date']);
}

public function test_model_relationships()
{
    $payment = Payment::factory()->create();
    $this->assertInstanceOf(User::class, $payment->user);
}
```

### Service Testing
```php
public function test_payment_posting_service_creates_payment()
{
    $service = app(PaymentPostingService::class);
    $payment = $service->postPayment([...]);
    
    $this->assertInstanceOf(Payment::class, $payment);
    $this->assertDatabaseHas('payments', [...]);
}
```

### Controller Testing
```php
public function test_index_requires_authentication()
{
    $response = $this->get('/payments');
    $response->assertRedirect('/login');
}

public function test_index_returns_payments_for_authenticated_user()
{
    $user = User::factory()->create();
    $payment = Payment::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)->get('/payments');
    $response->assertStatus(200);
    $response->assertViewHas('payments');
}
```

### Form Request Testing
```php
public function test_store_payment_request_validates_amount()
{
    $request = new StorePaymentRequest();
    $rules = $request->rules();
    
    $this->assertArrayHasKey('amount', $rules);
    $this->assertStringContainsString('numeric', $rules['amount']);
}
```

---

*Testing analysis: 2026-04-22*
