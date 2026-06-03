<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('invoice_number')->nullable()->default(0);
            $table->integer('payment_number')->nullable()->default(0);
            $table->integer('quotation_number')->nullable()->default(0);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 25, 4)->nullable();
            $table->string('number', 25)->nullable()->change();
            $table->string('company_number', 25)->nullable();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('subtotal', 25, 4)->nullable();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('number', 25)->nullable();
            $table->decimal('subtotal', 25, 4)->nullable();
            $table->string('company_number', 25)->nullable();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('subtotal', 25, 4)->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('number', 25)->nullable();
            $table->string('company_number', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_number', 'quotation_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['number', 'company_number']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['number', 'company_number']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['number', 'company_number']);
        });
    }
};
