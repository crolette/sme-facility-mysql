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
        Schema::table('maintainables', function (Blueprint $table) {
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 9, 2)->nullable();
            $table->boolean('under_warranty')->nullable();
            $table->date('end_warranty_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainables', function (Blueprint $table) {
            $table->dropColumn('purchase_date');
            $table->dropColumn('purchase_cost');
            $table->dropColumn('under_warranty');
            $table->dropColumn('end_warranty_date');
        });
    }
};
