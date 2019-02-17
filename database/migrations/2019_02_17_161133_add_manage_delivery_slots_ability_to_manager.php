<?php

use App\Store;
use Illuminate\Database\Migrations\Migration;

class AddManageDeliverySlotsAbilityToManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('manager')->toOwn(Store::class)->to([
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
