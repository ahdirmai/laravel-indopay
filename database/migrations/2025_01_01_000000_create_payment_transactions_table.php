<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('billable_type');
            $table->unsignedBigInteger('billable_id');
            $table->string('gateway');
            $table->string('reference_id');
            $table->unsignedBigInteger('amount'); // Minor units
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('gateway_event_id')->nullable();
            $table->timestamp('last_webhook_at')->nullable();
            $table->timestamps();

            $table->index(['billable_type', 'billable_id']);
            $table->unique(['gateway', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
