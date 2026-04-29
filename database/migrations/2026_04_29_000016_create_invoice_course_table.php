<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_course', function (Blueprint $table): void {
            $table->uuid('invoice_id');
            $table->uuid('course_id');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->primary(['invoice_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_course');
    }
};
