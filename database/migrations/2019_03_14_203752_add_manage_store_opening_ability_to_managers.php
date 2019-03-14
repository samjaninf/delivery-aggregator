<?php

use App\Store;
use Illuminate\Database\Migrations\Migration;

class AddManageStoreOpeningAbilityToManagers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('manager')->toOwn(Store::class)->to([
            'manage store opening',
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
