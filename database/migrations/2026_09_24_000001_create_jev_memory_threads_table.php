<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jev_memory_threads', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('external_id')->nullable()->index();
            $table->string('user_key')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->string('workflow')->nullable()->index();
            $table->string('stage')->nullable()->index();
            $table->unsignedInteger('version')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unique(['user_key', 'external_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('jev_memory_threads'); }
};
