<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            // Human friendly identifier printed on the receipt, e.g. KT-2026-000123
            $table->string('reference', 32)->unique();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();

            $table->string('donor_name');
            $table->string('donor_email');
            $table->string('donor_phone', 30)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('method', 30);
            $table->string('transaction_reference', 100)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->text('message')->nullable();
            $table->date('donated_on');

            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // Guards the campaign total against double counting: set when the
            // amount has been added to campaigns.raised_amount.
            $table->boolean('counted_in_campaign')->default(false);

            $table->timestamps();

            $table->index(['status', 'donated_on']);
            $table->index(['campaign_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
