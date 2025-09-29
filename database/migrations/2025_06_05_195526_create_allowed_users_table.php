<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowed_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('is_superadmin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('allowed_users');
    }
};
