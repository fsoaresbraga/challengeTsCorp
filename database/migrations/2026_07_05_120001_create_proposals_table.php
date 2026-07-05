<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('product');
            $table->decimal('monthly_value', 12, 2);
            $table->string('status', 20);
            $table->string('origin', 10);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status', 'deleted_at']);
            $table->index('status');
            $table->index('product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
