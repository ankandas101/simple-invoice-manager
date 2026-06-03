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
            $table->boolean('show_address')->nullable();
            $table->boolean('allow_transfer')->nullable();
            $table->string('bank_account_details')->nullable();
            $table->string('qrcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['show_address', 'allow_transfer', 'bank_account_details', 'qrcode']);
        });
    }
};
