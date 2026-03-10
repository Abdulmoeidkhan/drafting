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
        // Add status and created_by to participants table if they don't exist
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('checkout');
            }
            if (!Schema::hasColumn('participants', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite has limitations with dropping columns that have constraints
        // For development, simply delete database.sqlite and re-run migrations
        // This migration is additive only and doesn't need a proper rollback for SQLite
    }
};
