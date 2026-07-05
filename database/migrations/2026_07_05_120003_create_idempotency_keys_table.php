<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('operation', 50);
            $table->string('idempotency_key', 255);
            $table->string('response_hash', 64);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['operation', 'idempotency_key']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
