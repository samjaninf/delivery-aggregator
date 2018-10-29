<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirebaseIntegrationToUsersAndStoresTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fb_device_id')->nullable()->after('is_admin');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->string('fb_notification_key')->nullable()->after('consumer_secret');
        });
        Schema::table('store_user', function (Blueprint $table) {
            $table->boolean('fb_registered')->default(false)->after('user_id');
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
            $table->dropColumn('fb_device_id');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('fb_notification_key');
        });
        Schema::table('store_user', function (Blueprint $table) {
            $table->dropColumn('fb_registered');
        });
    }
}
