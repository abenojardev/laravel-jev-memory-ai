<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jev_memory_turns', function (Blueprint $table): void {
            $table->id();
            $table->string('thread_id');
            $table->unsignedInteger('sequence');
            $table->string('role');
            $table->text('raw_content');
            $table->string('idempotency_key')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->cascadeOnDelete();
            $table->unique(['thread_id', 'sequence']);
            $table->unique(['thread_id', 'idempotency_key']);
        });
    }

    public function down(): void { Schema::dropIfExists('jev_memory_turns'); }
};
