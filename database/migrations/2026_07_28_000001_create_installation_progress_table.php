<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_progress', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->timestamp('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_progress');
    }
};
