<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('category');
            $table->string('status')->default('draft');
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('current_approver_id')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            // растёт при каждом изменении, ловим параллельные решения
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('current_approver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
