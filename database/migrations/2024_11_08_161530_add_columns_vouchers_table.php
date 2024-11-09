<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('document_series');
            $table->string('document_number');
            $table->string('document_voucher_type');
            $table->string('document_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('document_series');
            $table->dropColumn('document_number');
            $table->dropColumn('document_voucher_type');
            $table->dropColumn('document_currency');
        });
    }
};
