<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jev_memory_compact_turns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('turn_id')->constrained('jev_memory_turns')->cascadeOnDelete();
            $table->string('thread_id');
            $table->text('compact_content');
            $table->string('meaning_type')->nullable();
            $table->json('entities')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->cascadeOnDelete();
            $table->unique('turn_id');
        });

        Schema::create('jev_memory_pending_actions', function (Blueprint $table): void {
            $table->id();
            $table->string('thread_id');
            $table->string('name');
            $table->json('payload')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->cascadeOnDelete();
            $table->index(['thread_id', 'status']);
        });

        Schema::create('jev_memory_memories', function (Blueprint $table): void {
            $table->id();
            $table->string('scope_type');
            $table->string('scope_key');
            $table->string('type');
            $table->text('content');
            $table->json('structured_data')->nullable();
            $table->decimal('importance', 5, 4)->nullable();
            $table->foreignId('source_turn_id')->nullable()->constrained('jev_memory_turns')->nullOnDelete();
            $table->string('thread_id')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('superseded_by')->nullable()->constrained('jev_memory_memories')->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->nullOnDelete();
            $table->index(['scope_type', 'scope_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jev_memory_memories');
        Schema::dropIfExists('jev_memory_pending_actions');
        Schema::dropIfExists('jev_memory_compact_turns');
    }
};
