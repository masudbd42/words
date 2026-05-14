<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analysis_batches', function (Blueprint $table) {
            $table->integer('word_limit')->default(20)->after('total_documents');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_batches', function (Blueprint $table) {
            $table->dropColumn('word_limit');
        });
    }
};
