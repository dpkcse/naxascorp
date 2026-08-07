<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_settings', function (Blueprint $table) {
            $table->id(); $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->string('site_logo_path')->nullable(); $table->string('site_logo_dark_path')->nullable(); $table->string('favicon_path')->nullable();
            $table->string('logo_alt_text', 160)->nullable(); $table->string('brand_mark_text', 12)->nullable();
            $table->char('primary_color', 7)->nullable(); $table->char('secondary_color', 7)->nullable(); $table->char('accent_color', 7)->nullable();
            $table->boolean('show_product_attribution')->default(true); $table->timestamps();
        });
        Schema::create('header_settings', function (Blueprint $table) {
            $table->id(); $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->boolean('show_top_bar')->default(true); $table->boolean('sticky_header')->default(true); $table->boolean('show_site_name')->default(true);
            $table->boolean('show_language_switcher')->default(false); $table->boolean('show_search')->default(false); $table->boolean('show_primary_cta')->default(false);
            $table->string('primary_cta_label', 80)->nullable(); $table->string('primary_cta_url', 2048)->nullable(); $table->string('primary_cta_target', 10)->default('_self'); $table->string('primary_cta_style', 20)->default('primary');
            $table->string('top_bar_message', 240)->nullable(); $table->string('support_label', 80)->nullable(); $table->string('support_url', 2048)->nullable(); $table->string('careers_label', 80)->nullable(); $table->string('careers_url', 2048)->nullable(); $table->timestamps();
        });
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id(); $table->string('name', 120); $table->string('location', 40)->unique(); $table->string('description', 240)->nullable(); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('navigation_menu_id')->constrained()->restrictOnDelete(); $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->restrictOnDelete();
            $table->string('label', 120); $table->string('link_type', 20); $table->string('route_name', 120)->nullable(); $table->string('url', 2048)->nullable(); $table->string('target', 10)->default('_self');
            $table->string('icon', 40)->nullable(); $table->string('description', 240)->nullable(); $table->string('badge_text', 40)->nullable(); $table->unsignedSmallInteger('display_order')->default(0); $table->unsignedTinyInteger('depth')->default(1);
            $table->boolean('is_active')->default(true)->index(); $table->boolean('is_featured')->default(false); $table->boolean('opens_mega_menu')->default(false); $table->timestamps();
            $table->index(['navigation_menu_id', 'parent_id', 'display_order'], 'nav_items_menu_parent_order_idx');
        });
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id(); $table->unsignedTinyInteger('singleton_key')->default(1)->unique(); $table->string('short_description', 500)->nullable();
            $table->boolean('show_contact_details')->default(true); $table->boolean('show_social_links')->default(true); $table->boolean('show_newsletter')->default(false);
            $table->string('newsletter_heading', 120)->nullable(); $table->string('newsletter_description', 300)->nullable(); $table->string('copyright_text', 240)->nullable();
            $table->string('developed_by_text', 160)->default('Developed by Naxas Innovations Limited'); $table->string('developed_by_url', 2048)->nullable(); $table->boolean('show_legal_links')->default(true); $table->timestamps();
        });
        Schema::create('footer_columns', function (Blueprint $table) {
            $table->id(); $table->foreignId('footer_setting_id')->constrained()->restrictOnDelete(); $table->string('title', 120); $table->unsignedSmallInteger('display_order')->default(0); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id(); $table->foreignId('footer_column_id')->constrained()->restrictOnDelete(); $table->string('label', 120); $table->string('link_type', 20); $table->string('route_name', 120)->nullable(); $table->string('url', 2048)->nullable(); $table->string('target', 10)->default('_self'); $table->unsignedSmallInteger('display_order')->default(0); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('social_links', function (Blueprint $table) {
            $table->id(); $table->foreignId('footer_setting_id')->constrained()->restrictOnDelete(); $table->string('platform', 30); $table->string('label', 120); $table->string('url', 2048); $table->string('icon', 30); $table->unsignedSmallInteger('display_order')->default(0); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links'); Schema::dropIfExists('footer_links'); Schema::dropIfExists('footer_columns'); Schema::dropIfExists('footer_settings');
        Schema::dropIfExists('navigation_items'); Schema::dropIfExists('navigation_menus'); Schema::dropIfExists('header_settings'); Schema::dropIfExists('branding_settings');
    }
};
