<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermissionsToAllowedUsersTable extends Migration
{
    public function up()
    {
        Schema::table('allowed_users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('allowed_users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
}
