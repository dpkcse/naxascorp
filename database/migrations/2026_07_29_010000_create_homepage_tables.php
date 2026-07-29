<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->string('eyebrow', 120)->nullable();
            $table->string('title', 180);
            $table->string('highlighted_text', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('primary_cta_label', 80)->nullable();
            $table->string('primary_cta_url', 2048)->nullable();
            $table->string('secondary_cta_label', 80)->nullable();
            $table->string('secondary_cta_url', 2048)->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 40)->unique();
            $table->unsignedTinyInteger('display_order');
            $table->boolean('is_enabled')->default(false)->index();
            $table->string('eyebrow', 120)->nullable();
            $table->string('heading', 180)->nullable();
            $table->text('description')->nullable();
            $table->text('secondary_description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt', 180)->nullable();
            $table->string('primary_cta_label', 80)->nullable();
            $table->string('primary_cta_url', 2048)->nullable();
            $table->string('secondary_cta_label', 80)->nullable();
            $table->string('secondary_cta_url', 2048)->nullable();
            $table->string('background_style', 20)->default('default');
            $table->string('content_width', 20)->default('standard');
            $table->timestamps();
            $table->index(['display_order', 'is_enabled']);
        });

        Schema::create('homepage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 40)->index();
            $table->string('title', 180);
            $table->string('eyebrow', 120)->nullable();
            $table->text('description')->nullable();
            $table->text('secondary_text')->nullable();
            $table->string('highlighted_text', 120)->nullable();
            $table->string('icon', 40)->nullable();
            $table->string('badge', 60)->nullable();
            $table->string('image_path')->nullable();
            $table->string('mobile_image_path')->nullable();
            $table->string('image_alt', 180)->nullable();
            $table->string('primary_cta_label', 80)->nullable();
            $table->string('primary_cta_url', 2048)->nullable();
            $table->string('secondary_cta_label', 80)->nullable();
            $table->string('secondary_cta_url', 2048)->nullable();
            $table->string('organization', 160)->nullable();
            $table->string('value', 80)->nullable();
            $table->string('prefix', 20)->nullable();
            $table->string('suffix', 20)->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->date('published_on')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['homepage_section_id', 'is_active', 'display_order'], 'homepage_items_public_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_items');
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('homepage_settings');
    }
};
