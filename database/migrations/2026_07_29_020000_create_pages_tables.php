<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->restrictOnDelete();
            $table->string('title', 160); $table->string('slug', 160)->unique();
            $table->string('eyebrow', 120)->nullable(); $table->string('summary', 500)->nullable(); $table->text('body')->nullable();
            $table->string('template', 40)->default('standard'); $table->string('status', 20)->default('draft')->index();
            $table->string('featured_image_path')->nullable(); $table->string('featured_image_alt', 160)->nullable();
            $table->boolean('show_breadcrumb')->default(true); $table->boolean('show_title')->default(true); $table->unsignedSmallInteger('display_order')->default(1);
            $table->string('meta_title', 70)->nullable(); $table->string('meta_description', 170)->nullable(); $table->string('canonical_url', 2048)->nullable();
            $table->string('og_title', 70)->nullable(); $table->string('og_description', 200)->nullable(); $table->string('og_image_path')->nullable();
            $table->boolean('robots_index')->default(true); $table->boolean('robots_follow')->default(true);
            $table->timestamp('published_at')->nullable()->index(); $table->timestamp('scheduled_for')->nullable()->index(); $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->index(['parent_id', 'display_order']);
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id(); $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('section_type', 30); $table->string('heading', 160)->nullable(); $table->string('eyebrow', 120)->nullable(); $table->text('body')->nullable();
            $table->string('image_path')->nullable(); $table->string('image_alt', 160)->nullable();
            $table->string('primary_cta_label', 80)->nullable(); $table->string('primary_cta_url', 2048)->nullable();
            $table->string('secondary_cta_label', 80)->nullable(); $table->string('secondary_cta_url', 2048)->nullable();
            $table->string('background_style', 20)->default('default'); $table->string('content_width', 20)->default('standard');
            $table->unsignedSmallInteger('display_order')->default(1); $table->boolean('is_enabled')->default(true); $table->timestamps();
            $table->index(['page_id', 'display_order']);
        });

        Schema::table('navigation_items', function (Blueprint $table) {
            $table->foreignId('page_id')->nullable()->after('route_name')->constrained('pages')->restrictOnDelete();
            $table->index(['link_type', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::table('navigation_items', function (Blueprint $table) { $table->dropConstrainedForeignId('page_id'); });
        Schema::dropIfExists('page_sections'); Schema::dropIfExists('pages');
    }
};
