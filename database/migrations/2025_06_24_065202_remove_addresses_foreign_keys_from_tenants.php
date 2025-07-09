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
            $table->dropForeign('tenants_company_address_id_foreign');
            $table->dropForeign('tenants_invoice_address_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('company_address_id')->references('id')->on('addresses')->cascadeOnDelete();
            $table->foreign('invoice_address_id')->references('id')->on('addresses')->cascadeOnDelete();
        });
    }
};
