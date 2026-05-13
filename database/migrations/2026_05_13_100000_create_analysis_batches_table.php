<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_batches', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('processing');
            $table->unsignedInteger('total_documents')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_batches');
    }
};
