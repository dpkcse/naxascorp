<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_collections', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('display_order')->default(1);
            $table->timestamps();
        });

        Schema::create('media_assets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('media_collection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk', 20)->default('public');
            $table->string('directory');
            $table->string('filename');
            $table->string('stored_name');
            $table->string('extension', 10);
            $table->string('mime_type', 80);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('aspect_ratio', 12, 6)->nullable();
            $table->string('alt_text', 180)->nullable();
            $table->string('title', 180)->nullable();
            $table->text('caption')->nullable();
            $table->string('credit', 180)->nullable();
            $table->string('original_filename')->nullable();
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->string('media_type', 20)->index();
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('created_at');
            $table->unique(['directory', 'stored_name']);
        });

        Schema::create('media_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->restrictOnDelete();
            $table->string('mediable_type', 60);
            $table->unsignedBigInteger('mediable_id');
            $table->string('slot', 60);
            $table->unsignedSmallInteger('display_order')->default(1);
            $table->timestamps();
            $table->index(['mediable_type', 'mediable_id']);
            $table->index(['media_asset_id', 'slot']);
            $table->unique(['mediable_type', 'mediable_id', 'slot', 'display_order'], 'media_usage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('media_collections');
    }
};
