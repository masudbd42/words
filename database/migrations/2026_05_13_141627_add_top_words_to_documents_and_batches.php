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
        Schema::table('documents', function (Blueprint $table) {
            $table->json('top_words')->nullable()->after('status');
        });

        Schema::table('analysis_batches', function (Blueprint $table) {
            $table->json('top_words')->nullable()->after('total_documents');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('top_words');
        });

        Schema::table('analysis_batches', function (Blueprint $table) {
            $table->dropColumn('top_words');
        });
    }
};
