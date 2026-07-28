<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->string('site_name', 120);
            $table->string('legal_name', 180);
            $table->string('tagline', 240)->nullable();
            $table->string('primary_email', 254);
            $table->string('primary_phone', 40)->nullable();
            $table->char('country_code', 2);
            $table->string('timezone', 64);
            $table->string('default_locale', 10)->default('en');
            $table->string('site_url', 2048);
            $table->timestamps();
        });

        Schema::create('installation_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->boolean('demo_content_selected');
            $table->string('demo_content_status', 30);
            $table->timestamp('demo_content_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_choices');
        Schema::dropIfExists('website_settings');
    }
};
