<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('file_size_bytes')->nullable()->after('stored_path');
            $table->string('mime_type', 120)->nullable()->after('file_size_bytes');
            $table->string('checksum_sha256', 64)->nullable()->after('mime_type');
            $table->unsignedInteger('page_count')->nullable()->after('checksum_sha256');
            $table->json('metadata')->nullable()->after('page_count');
            $table->timestamp('analyzed_at')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'file_size_bytes',
                'mime_type',
                'checksum_sha256',
                'page_count',
                'metadata',
                'analyzed_at',
            ]);
        });
    }
};