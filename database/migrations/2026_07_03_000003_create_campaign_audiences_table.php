<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('campaign_preset', 50);
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->timestamp('last_launched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'campaign_preset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_audiences');
    }
};
