<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'installed',
    'web',
    'language',
    'inertia',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::redirect('/home', '/');
    Route::redirect('/dashboard', '/');
    Route::get('/', [Controllers\HomeController::class, 'index'])->name('dashboard');
    Route::get('/language/{language}', [Controllers\AjaxController::class, 'language']);
    Route::get('/language/{language}', [Controllers\AjaxController::class, 'language']);
    Route::get('/activity', [Controllers\HomeController::class, 'activity'])->name('activity');
    Route::get('/settings', [Controllers\SettingController::class, 'index'])->name('settings');
    Route::post('/read/excel', [Controllers\ExcelController::class, 'read'])->name('read.excel');
    Route::post('settings', [Controllers\SettingController::class, 'store'])->name('settings.store');

    Route::extendedResources([
        'products'   => Controllers\ProductController::class,
        'roles'      => Controllers\RoleController::class,
        'users'      => Controllers\UserController::class,
        'invoices'   => Controllers\InvoiceController::class,
        'quotations' => Controllers\QuotationController::class,
        'customers'  => Controllers\CustomerController::class,
        'tax_rates'  => Controllers\TaxRateController::class,
        'companies'  => Controllers\CompanyController::class,
        'notes'      => Controllers\NoteController::class,
        'fields'     => Controllers\FieldController::class,
        'payments'   => Controllers\PaymentController::class,
    ]);

    Route::get('/billing', [Controllers\CustomerUserController::class, 'editCustomer'])->name('customer.edit');
    Route::post('/billing', [Controllers\CustomerUserController::class, 'updateCustomer'])->name('customer.update');
    Route::post('/customers/{customer}/users/', [Controllers\CustomerUserController::class, 'store'])->name('customer.users.store');
    Route::put('/customers/{customer}/users/{user}/', [Controllers\CustomerUserController::class, 'update'])->name('customer.users.update');
    Route::delete('/customers/{customer}/users/{user}', [Controllers\CustomerUserController::class, 'destroy'])->name('customer.users.destroy');
    Route::get('/customers/{customer}/transactions/', [Controllers\CustomerTransactionController::class, 'index'])->name('customer.transactions');
    Route::post('/invoices/{invoice}/receipt/cancel', [Controllers\InvoiceController::class, 'cancelReceipt'])->name('invoices.receipt.cancel');

    Route::portResources([
        'products'  => Controllers\ProductPortController::class,
        'customers' => Controllers\CustomerPortController::class,
    ]);

    Route::prefix('/search')->group(function () {
        Route::post('notes', [Controllers\SearchController::class, 'notes'])->name('search.notes');
        Route::post('products', [Controllers\SearchController::class, 'products'])->name('search.products');
        Route::post('customers', [Controllers\SearchController::class, 'customers'])->name('search.customers');
    });

    Route::prefix('/reports')->group(function () {
        Route::get('invoices', [Controllers\ReportController::class, 'invoices'])->name('reports.invoices');
        Route::get('payments', [Controllers\ReportController::class, 'payments'])->name('reports.payments');
        Route::get('daily_invoices/{month?}/{year?}', [Controllers\ReportController::class, 'dailyInvoices'])->name('reports.daily_invoices');
        Route::get('monthly_invoices/{year?}', [Controllers\ReportController::class, 'monthlyInvoices'])->name('reports.monthly_invoices');

        Route::get('invoices/port', [Controllers\ReportPortController::class, 'invoices'])->name('report.port.invoices');
        Route::get('payments/port', [Controllers\ReportPortController::class, 'payments'])->name('report.port.payments');
    });

    Route::get('/impersonate/stop', [Controllers\ImpersonateController::class, 'stop'])->name('impersonate.stop');
    Route::get('/impersonate/as/{user}', [Controllers\ImpersonateController::class, 'start'])->name('impersonate');

    Route::prefix('/email')->group(function () {
        Route::post('invoice/{invoice}', [Controllers\NotificationController::class, 'invoiceNotification'])->name('invoice.notification');
        Route::post('payment/{payment}', [Controllers\NotificationController::class, 'paymentNotification'])->name('payment.notification');
        Route::post('quotation/{quotation}', [Controllers\NotificationController::class, 'quotationNotification'])->name('quotation.notification');
    });

    Route::delete('/attachments/{attachment?}', [Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');
});

Route::get('/attachments/{attachment:uuid?}', [Controllers\AttachmentController::class, 'download'])
    ->middleware(['installed', 'web', 'language', 'inertia'])->name('attachments.download');

// Payment routes
Route::prefix('/paypal')->group(function () {
    Route::post('notify', [Controllers\AjaxController::class, 'PayPalIPN'])->name('paypal.notify');
    Route::post('charge/{id}/{hash}', [Controllers\AjaxController::class, 'charge'])->name('paypal.charge');
});

// View routes with signed URLs
Route::prefix('/view')->group(function () {
    Route::get('invoice/{id}/{hash}', [Controllers\NotificationController::class, 'invoice'])->name('invoice.view');
    Route::get('payment/{id}/{hash}', [Controllers\NotificationController::class, 'payment'])->name('payment.view');
    Route::get('quotation/{id}/{hash}', [Controllers\NotificationController::class, 'quotation'])->name('quotation.view');
});

// View routes for public
Route::prefix('views')->middleware(['installed', 'inertia', 'language'])->group(function () {
    Route::get('/invoices/{id}/{hash}', [Controllers\PublicController::class, 'invoice'])->name('views.invoice');
    Route::get('/payments/{id}/{hash}', [Controllers\PublicController::class, 'payment'])->name('views.payment');
    Route::get('/quotations/{id}/{hash}', [Controllers\PublicController::class, 'quotation'])->name('views.quotation');

    // Invoice payment transfer routes
    Route::post('/invoices/{id}/{hash}/receipt', [Controllers\PublicController::class, 'uploadReceipt'])->name('invoices.receipt');
});

Route::prefix('/commands')->middleware('role:admin')->group(function () {
    Route::view('/message', 'message')->name('message');
    Route::get('/storage_link', function () {
        Illuminate\Support\Facades\Artisan::call('storage:link --force');

        return to_route('message')->with('message', Illuminate\Support\Facades\Artisan::output());
    });

    Route::get('/migrate', function () {
        Illuminate\Support\Facades\Artisan::call('migrate --force');

        return to_route('message')->with('message', Illuminate\Support\Facades\Artisan::output());
    });

    Route::get('/generate_invoices', function () {
        Illuminate\Support\Facades\Artisan::call('app:generate-invoices');

        return to_route('message')->with('message', Illuminate\Support\Facades\Artisan::output());
    });

    Route::get('/send_overdue_invoices', function () {
        Illuminate\Support\Facades\Artisan::call('app:send-overdue-invoices');

        return to_route('message')->with('message', Illuminate\Support\Facades\Artisan::output());
    });
});
