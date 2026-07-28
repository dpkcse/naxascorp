<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_states', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug')->unique();
            $table->string('license_type', 40);
            $table->uuid('installation_uuid');
            $table->string('normalized_domain', 253);
            $table->string('environment', 20);
            $table->uuid('request_id')->nullable()->unique();
            $table->text('encrypted_request_token')->nullable();
            $table->timestamp('request_expires_at')->nullable();
            $table->string('activation_status', 40)->default('not_requested')->index();
            $table->longText('encrypted_signed_entitlement')->nullable();
            $table->char('entitlement_fingerprint', 64)->nullable();
            $table->string('license_status', 40)->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('last_failure_code')->nullable();
            $table->text('last_safe_message')->nullable();
            $table->string('portal_url', 2048)->nullable();
            $table->timestamps();
            $table->index(['installation_uuid', 'normalized_domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_states');
    }
};
