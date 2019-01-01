<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersIsAdminToUsersRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('role')->after('is_admin')->default(1);
        });

        DB::table('users')->where('is_admin', 1)->update(['role' => \App\User::ADMIN]);
        DB::table('users')->where('is_admin', 0)->update(['role' => \App\User::USER]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->after('role')->default(1);
        });

        DB::table('users')->where('role', \App\User::ADMIN)->update(['is_admin' => 1]);
        DB::table('users')->where('role', \App\User::USER)->update(['is_admin' => 0]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}
