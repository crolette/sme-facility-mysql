<?php

use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('type');
            $table->string('internal_reference')->nullable();
            $table->string('provider_reference')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('renewal_type')->default(ContractRenewalTypesEnum::AUTOMATIC);
            $table->string('status', 10)->default(ContractStatusEnum::ACTIVE);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
