<?php

use App\Enums\AddressTypes;
use Illuminate\Support\Facades\DB;
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

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('tenant_id', 255)->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->enum('address_type', array_column(AddressTypes::cases(), 'value'))->default(AddressTypes::COMPANY->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_tenant_id_foreign');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
            $table->dropColumn('address_type');
        });
    }
};
