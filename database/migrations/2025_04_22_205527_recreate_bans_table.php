<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bans');
        
        Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_permanent')->default(false);
            $table->morphs('banned');
            $table->morphs('banned_by');
            $table->text('reason');
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};