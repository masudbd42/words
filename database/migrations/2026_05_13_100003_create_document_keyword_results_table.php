<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_keyword_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('frequency_count')->default(0);
            $table->timestamps();

            $table->unique(['document_id', 'keyword_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_keyword_results');
    }
};
