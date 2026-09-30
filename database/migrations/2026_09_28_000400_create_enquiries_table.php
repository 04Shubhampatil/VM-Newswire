<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            // Snapshots keep historical enquiries accurate after packages change.
            $table->string('package_name_snapshot')->nullable();
            $table->decimal('package_price_snapshot', 10, 2)->nullable();
            $table->char('package_currency_snapshot', 3)->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 64);
            $table->string('company')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('release_count', 32)->nullable();
            $table->text('message');
            $table->string('source_page')->nullable();
            $table->string('status', 16)->default('new')->index();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->string('email_type', 32);
            $table->string('recipient');
            $table->string('delivery_status', 16)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('enquiries');
    }
};
