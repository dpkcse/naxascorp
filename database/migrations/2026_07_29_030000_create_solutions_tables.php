<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solution_categories', function (Blueprint $table) {
            $table->id(); $table->string('name', 120); $table->string('slug', 120)->unique(); $table->text('description')->nullable(); $table->unsignedSmallInteger('display_order')->default(1); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('solutions', function (Blueprint $table) {
            $table->id(); $table->foreignId('solution_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title', 160); $table->string('slug', 160)->unique(); $table->string('eyebrow', 120)->nullable(); $table->string('short_description', 500); $table->text('overview')->nullable(); $table->text('challenge_text')->nullable(); $table->text('approach_text')->nullable(); $table->text('outcome_text')->nullable();
            $table->string('icon', 40)->nullable(); $table->string('featured_image_path')->nullable(); $table->string('featured_image_alt', 160)->nullable(); $table->string('hero_style', 30)->default('standard'); $table->string('template', 30)->default('standard');
            $table->string('status', 20)->default('draft')->index(); $table->boolean('is_featured')->default(false)->index(); $table->unsignedSmallInteger('display_order')->default(1);
            $table->timestamp('published_at')->nullable()->index(); $table->timestamp('scheduled_for')->nullable()->index(); $table->timestamp('archived_at')->nullable()->index();
            $table->string('primary_cta_label', 80)->nullable(); $table->string('primary_cta_url', 2048)->nullable(); $table->string('secondary_cta_label', 80)->nullable(); $table->string('secondary_cta_url', 2048)->nullable();
            $table->string('meta_title', 70)->nullable(); $table->string('meta_description', 170)->nullable(); $table->string('canonical_url', 2048)->nullable(); $table->string('og_title', 70)->nullable(); $table->string('og_description', 200)->nullable(); $table->string('og_image_path')->nullable(); $table->boolean('robots_index')->default(true); $table->boolean('robots_follow')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['solution_category_id', 'display_order']);
        });
        foreach (['solution_features', 'solution_benefits', 'solution_capabilities', 'solution_process_steps'] as $name) {
            Schema::create($name, function (Blueprint $table) { $table->id(); $table->foreignId('solution_id')->constrained()->cascadeOnDelete(); $table->string('title', 160); $table->text('description')->nullable(); $table->string('icon', 40)->nullable(); $table->unsignedSmallInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['solution_id', 'display_order']); });
        }
        Schema::create('solution_use_cases', function (Blueprint $table) { $table->id(); $table->foreignId('solution_id')->constrained()->cascadeOnDelete(); $table->string('title', 160); $table->text('description'); $table->string('sector', 120)->nullable(); $table->string('icon', 40)->nullable(); $table->unsignedSmallInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['solution_id', 'display_order']); });
        Schema::create('solution_relations', function (Blueprint $table) { $table->id(); $table->foreignId('solution_id')->constrained()->cascadeOnDelete(); $table->foreignId('related_solution_id')->constrained('solutions')->restrictOnDelete(); $table->unsignedSmallInteger('display_order')->default(1); $table->timestamps(); $table->unique(['solution_id', 'related_solution_id']); });
        Schema::create('solution_page_relations', function (Blueprint $table) { $table->id(); $table->foreignId('solution_id')->constrained()->cascadeOnDelete(); $table->foreignId('page_id')->constrained()->restrictOnDelete(); $table->unsignedSmallInteger('display_order')->default(1); $table->timestamps(); $table->unique(['solution_id', 'page_id']); });
        Schema::table('homepage_items', function (Blueprint $table) { $table->foreignId('solution_id')->nullable()->constrained()->restrictOnDelete(); });
        Schema::table('navigation_items', function (Blueprint $table) { $table->foreignId('solution_id')->nullable()->constrained()->restrictOnDelete(); $table->index(['link_type', 'solution_id']); });
    }
    public function down(): void
    {
        Schema::table('navigation_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('solution_id')); Schema::table('homepage_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('solution_id'));
        Schema::dropIfExists('solution_page_relations'); Schema::dropIfExists('solution_relations'); Schema::dropIfExists('solution_use_cases'); Schema::dropIfExists('solution_process_steps'); Schema::dropIfExists('solution_capabilities'); Schema::dropIfExists('solution_benefits'); Schema::dropIfExists('solution_features'); Schema::dropIfExists('solutions'); Schema::dropIfExists('solution_categories');
    }
};
