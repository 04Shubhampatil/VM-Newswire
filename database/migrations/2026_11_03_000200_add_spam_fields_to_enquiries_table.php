<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spam scoring for enquiries (App\Services\SpamDetector). Additive only: no existing column or row changes.
 * Spam itself is a value of the existing `status` column ("spam").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->unsignedSmallInteger('spam_score')->nullable()->after('status');
            // [{"rule": "seo_guest_post", "points": 4, "detail": "guest post"}, ...]
            $table->json('spam_reasons')->nullable()->after('spam_score');
            // Who/what changed the status to spam: "auto", "admin" or "cleanup".
            $table->string('spam_marked_by', 16)->nullable()->after('spam_reasons');
            $table->timestamp('spam_marked_at')->nullable()->after('spam_marked_by');
            // Submitter's IP (for duplicate / flood detection and admin review).
            $table->string('ip_address', 45)->nullable()->after('source_page');

            $table->index(['status', 'created_at']);
            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['email', 'created_at']);
            $table->dropColumn(['spam_score', 'spam_reasons', 'spam_marked_by', 'spam_marked_at', 'ip_address']);
        });
    }
};
