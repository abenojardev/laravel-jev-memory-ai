<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jev_memory_thread_states', function (Blueprint $table): void {
            $table->string('thread_id')->primary();
            $table->string('workflow')->nullable();
            $table->string('stage')->nullable();
            $table->string('pending_action')->nullable();
            $table->json('state')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->timestamp('updated_at')->useCurrent();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->cascadeOnDelete();
        });

        Schema::create('jev_memory_state_transitions', function (Blueprint $table): void {
            $table->id();
            $table->string('thread_id');
            $table->unsignedInteger('from_version');
            $table->unsignedInteger('to_version');
            $table->string('event');
            $table->json('before')->nullable();
            $table->json('delta')->nullable();
            $table->json('after')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('thread_id')->references('id')->on('jev_memory_threads')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jev_memory_state_transitions');
        Schema::dropIfExists('jev_memory_thread_states');
    }
};
