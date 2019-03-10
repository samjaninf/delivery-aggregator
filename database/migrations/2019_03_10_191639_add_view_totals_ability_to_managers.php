<?php

use Illuminate\Database\Migrations\Migration;

class AddViewTotalsAbilityToManagers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('manager')->to([
            'view totals',
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
