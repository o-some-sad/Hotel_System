<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->boolean('is_permanent')->default(false)->after('id'); // You can adjust the position
        });
    }

    public function down(): void
    {
        Schema::table('bans', function (Blueprint $table) {
            $table->dropColumn('is_permanent');
        });
    }
};
