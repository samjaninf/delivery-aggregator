<?php

use App\Store;
use Illuminate\Database\Migrations\Migration;

class RemoveManageDeliverySlotsAbilityFromManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::disallow('manager')->toOwn(Store::class)->to([
            'manage delivery slots',
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
