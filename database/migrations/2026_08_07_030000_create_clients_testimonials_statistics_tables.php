<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160); $table->string('slug', 160)->unique(); $table->string('client_type', 24)->index();
            $table->text('short_description')->nullable(); $table->string('logo_path')->nullable(); $table->string('logo_alt', 180)->nullable(); $table->string('website_url', 2048)->nullable(); $table->char('country_code', 2)->nullable();
            $table->foreignId('industry_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index(); $table->boolean('is_featured')->default(false)->index(); $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamp('published_at')->nullable(); $table->timestamp('scheduled_for')->nullable(); $table->timestamp('archived_at')->nullable();
            $table->string('meta_title', 70)->nullable(); $table->string('meta_description', 170)->nullable(); $table->string('canonical_url', 2048)->nullable(); $table->boolean('robots_index')->default(true); $table->boolean('robots_follow')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->index(['status', 'archived_at', 'display_order'], 'clients_public_index');
        });

        foreach (['solution', 'product', 'capability', 'page'] as $relation) {
            Schema::create("client_{$relation}_relations", function (Blueprint $table) use ($relation) {
                $table->foreignId('client_id')->constrained()->cascadeOnDelete(); $table->foreignId("{$relation}_id")->constrained("{$relation}s")->restrictOnDelete(); $table->unsignedTinyInteger('display_order')->default(0); $table->primary(['client_id', "{$relation}_id"]);
            });
        }

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id(); $table->foreignId('client_id')->nullable()->constrained()->restrictOnDelete(); $table->string('person_name', 160); $table->string('designation', 160)->nullable(); $table->string('organization_name', 160)->nullable(); $table->text('quote');
            $table->string('image_path')->nullable(); $table->string('image_alt', 180)->nullable(); $table->unsignedTinyInteger('rating')->nullable(); $table->string('source_url', 2048)->nullable();
            $table->string('status', 20)->default('draft')->index(); $table->boolean('is_featured')->default(false)->index(); $table->unsignedSmallInteger('display_order')->default(0); $table->timestamp('published_at')->nullable(); $table->timestamp('scheduled_for')->nullable(); $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['status', 'archived_at', 'display_order'], 'testimonials_public_index');
        });

        Schema::create('statistics', function (Blueprint $table) {
            $table->id(); $table->string('label', 160); $table->string('value', 120); $table->string('prefix', 20)->nullable(); $table->string('suffix', 20)->nullable(); $table->text('description')->nullable(); $table->string('statistic_group', 24)->index(); $table->string('icon', 40)->nullable(); $table->text('source_note')->nullable(); $table->string('source_url', 2048)->nullable(); $table->date('as_of_date')->nullable();
            $table->string('status', 20)->default('draft')->index(); $table->boolean('is_featured')->default(false)->index(); $table->unsignedSmallInteger('display_order')->default(0); $table->timestamp('published_at')->nullable(); $table->timestamp('scheduled_for')->nullable(); $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['status', 'archived_at', 'display_order'], 'statistics_public_index');
        });

        Schema::table('homepage_items', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('capability_id')->constrained()->restrictOnDelete(); $table->foreignId('testimonial_id')->nullable()->after('client_id')->constrained()->restrictOnDelete(); $table->foreignId('statistic_id')->nullable()->after('testimonial_id')->constrained()->restrictOnDelete();
        });
        Schema::table('navigation_items', function (Blueprint $table) { $table->foreignId('client_id')->nullable()->after('work_process_id')->constrained()->restrictOnDelete(); });
    }

    public function down(): void
    {
        Schema::table('navigation_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('client_id'));
        Schema::table('homepage_items', function (Blueprint $table) { $table->dropConstrainedForeignId('statistic_id'); $table->dropConstrainedForeignId('testimonial_id'); $table->dropConstrainedForeignId('client_id'); });
        Schema::dropIfExists('statistics'); Schema::dropIfExists('testimonials');
        foreach (['page', 'capability', 'product', 'solution'] as $relation) { Schema::dropIfExists("client_{$relation}_relations"); }
        Schema::dropIfExists('clients');
    }
};
