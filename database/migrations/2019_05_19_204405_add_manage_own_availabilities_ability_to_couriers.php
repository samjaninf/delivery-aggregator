<?php

use Illuminate\Database\Migrations\Migration;

class AddManageOwnAvailabilitiesAbilityToCouriers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('courier')->to([
            'manage own availabilities',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
