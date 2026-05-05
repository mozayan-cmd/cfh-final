<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BankManagementController;
use App\Http\Controllers\BoatController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CashReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('boats', BoatController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::resource('buyers', BuyerController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('buyers');

    Route::resource('landings', LandingController::class);
    Route::post('landings/{landing}/attach-expenses', [LandingController::class, 'attachExpenses'])
        ->name('landings.attach-expenses');

    Route::get('invoices/import', [InvoiceController::class, 'import'])->name('invoices.import');
    Route::post('invoices/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::post('invoices/process-import', [InvoiceController::class, 'processImport'])->name('invoices.process-import');
    Route::get('invoices/landings/{boat}', [InvoiceController::class, 'getLandingsByBoat'])->name('invoices.landings-by-boat');
    Route::get('invoices/pending-landings/{boat}', [InvoiceController::class, 'getLandingDatesWithPendingInvoices'])->name('invoices.pending-landings');
    Route::get('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'store', 'show', 'update', 'create', 'destroy']);

    Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::get('expenses/import', [ExpenseController::class, 'import'])->name('expenses.import');
    Route::post('expenses/import/preview', [ExpenseController::class, 'previewImport'])->name('expenses.import.preview');
    Route::post('expenses/import/process', [ExpenseController::class, 'processImport'])->name('expenses.import.process');
    Route::post('expenses/types', [ExpenseController::class, 'storeType'])->name('expenses.store-type');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
    Route::post('receipts', [ReceiptController::class, 'store'])->name('receipts.store');
    Route::get('receipts/export', [ReceiptController::class, 'export'])->name('receipts.export');
    Route::get('receipts/import', [ReceiptController::class, 'import'])->name('receipts.import');
    Route::post('receipts/import/preview', [ReceiptController::class, 'previewImport'])->name('receipts.import.preview');
    Route::post('receipts/import/process', [ReceiptController::class, 'processImport'])->name('receipts.import.process');
    Route::get('receipts/invoices/{buyer}', [ReceiptController::class, 'getInvoicesByBuyer'])
        ->name('receipts.invoices');
    Route::get('receipts/api/buyers', [ReceiptController::class, 'getBuyersByBoat'])
        ->name('receipts.buyers-by-boat');
    Route::get('receipts/api/invoices', [ReceiptController::class, 'getInvoicesByBuyerAndLanding'])
        ->name('receipts.invoices-by-buyer-landing');
    Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('receipts/{receipt}/edit', [ReceiptController::class, 'edit'])->name('receipts.edit');
    Route::put('receipts/{receipt}', [ReceiptController::class, 'update'])->name('receipts.update');
    Route::delete('receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('payments/types', [PaymentController::class, 'storeType'])->name('payments.types');
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/import', [PaymentController::class, 'import'])->name('payments.import');
    Route::post('payments/import/preview', [PaymentController::class, 'previewImport'])->name('payments.import.preview');
    Route::post('payments/import/process', [PaymentController::class, 'processImport'])->name('payments.import.process');
    Route::get('payments/landings/{boat}', [PaymentController::class, 'getLandingsByBoat'])
        ->name('payments.landings-by-boat');
    Route::get('payments/landing/{landing}', [PaymentController::class, 'getLandingExpenses'])
        ->name('payments.landing');
    Route::get('payments/expenses/{boat}', [PaymentController::class, 'getExpensesByBoat'])
        ->name('payments.expenses-by-boat');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::prefix('cash')->name('cash.')->group(function () {
        Route::get('utilization', [CashController::class, 'utilization'])->name('utilization');
        Route::get('deposit', [CashController::class, 'createDeposit'])->name('deposit');
        Route::post('deposit', [CashController::class, 'storeDeposit'])->name('deposit.store');
        Route::get('transaction/{transaction}/edit', [CashController::class, 'editTransaction'])->name('transaction.edit');
        Route::put('transaction/{transaction}', [CashController::class, 'updateTransaction'])->name('transaction.update');
        Route::delete('transaction/{transaction}', [CashController::class, 'destroyTransaction'])->name('transaction.destroy');
        Route::get('receipt/{receipt}', [CashController::class, 'show'])->name('show');
        Route::get('api/available-receipts', [CashController::class, 'getAvailableReceipts'])->name('api.available');
        Route::get('report', [CashReportController::class, 'cashReport'])->name('report');
        Route::get('bank-report', [CashReportController::class, 'bankReport'])->name('bank-report');
    });

    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::get('/create', [LoanController::class, 'create'])->name('create');
        Route::post('/', [LoanController::class, 'store'])->name('store');
        Route::post('/types', [LoanController::class, 'storeType'])->name('store-type');
        Route::post('/{loan}/repay', [LoanController::class, 'repay'])->name('repay');
    });

    Route::get('bank', [BankManagementController::class, 'index'])->name('bank.index');
    Route::post('bank/withdraw', [BankManagementController::class, 'withdraw'])->name('bank.withdraw');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/generate', [ReportController::class, 'generateReport'])->name('generate');
        Route::get('/fund-flow', [ReportController::class, 'fundFlow'])->name('fund-flow');
        Route::get('/fund-flow/pdf', [ReportController::class, 'fundFlowPdf'])->name('fund-flow.pdf');
        Route::get('/fund-flow/excel', [ReportController::class, 'fundFlowExcel'])->name('fund-flow.excel');
    });

    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::post('/clear', [BackupController::class, 'clear'])->name('clear');
        Route::post('/clear-user/{user}', [BackupController::class, 'clear'])->name('clear-user');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    })->middleware(['admin']);

    Route::middleware(['admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::get('/unlinked-expenses', 'App\Http\Controllers\UnlinkedExpenseController@index')->name('unlinked-expenses.index');
        Route::get('/unlinked-expenses/{expense}', 'App\Http\Controllers\UnlinkedExpenseController@show')->name('unlinked-expenses.show');
        Route::get('/unlinked-expenses/{expense}/edit', 'App\Http\Controllers\UnlinkedExpenseController@edit')->name('unlinked-expenses.edit');
        Route::put('/unlinked-expenses/{expense}', 'App\Http\Controllers\UnlinkedExpenseController@update')->name('unlinked-expenses.update');
        Route::delete('/unlinked-expenses/{expense}', 'App\Http\Controllers\UnlinkedExpenseController@destroy')->name('unlinked-expenses.destroy');
    });
});

Route::get('/api/related-records/{model}/{id}', function ($model, $id) {
    $class = match ($model) {
        'invoice' => Invoice::class,
        'receipt' => Receipt::class,
        'expense' => Expense::class,
        'payment' => Payment::class,
        'landing' => Landing::class,
        'boat' => Boat::class,
        'buyer' => Buyer::class,
        default => null,
    };

    if (! $class || ! $record = $class::find($id)) {
        return response()->json(['error' => 'Not found'], 404);
    }

    if (! method_exists($record, 'getRelatedRecords')) {
        return response()->json([]);
    }

    return response()->json($record->getRelatedRecords());
})->name('api.related-records');

Route::get('/seed', function () {
    \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder']);
    return response()->json(['message' => 'Admin user seeded']);
});
