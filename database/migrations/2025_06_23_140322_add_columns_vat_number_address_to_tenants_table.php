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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('vat_number', 20)->unique()->nullable();
            $table->string('company_code', 4)->unique()->nullable();
            $table->foreignId('company_address_id')->nullable()->constrained('addresses', 'id')->cascadeOnDelete();
            $table->foreignId('invoice_address_id')->nullable()->constrained('addresses', 'id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign('tenants_company_address_id_foreign');
            $table->dropForeign('tenants_invoice_address_id_foreign');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('vat_number');
            $table->dropColumn('company_code');
            $table->dropColumn('company_address_id');
            $table->dropColumn('invoice_address_id');
        });
    }
};
