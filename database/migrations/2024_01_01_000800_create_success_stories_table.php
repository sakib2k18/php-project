<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('beneficiary_name')->nullable();
            $table->string('beneficiary_description', 500)->nullable();
            $table->longText('story');
            $table->string('location')->nullable();
            $table->date('story_date')->index();
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->boolean('featured')->default(false)->index();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
