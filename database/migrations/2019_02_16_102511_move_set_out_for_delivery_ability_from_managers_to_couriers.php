<?php

use App\Store;
use Illuminate\Database\Migrations\Migration;

class MoveSetOutForDeliveryAbilityFromManagersToCouriers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::disallow('manager')->toOwn(Store::class)->to([
            'set out for delivery',
        ]);

        Bouncer::allow('courier')->toOwn(Store::class)->to([
            'set out for delivery',
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
