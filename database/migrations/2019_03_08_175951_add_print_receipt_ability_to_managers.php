<?php

use Illuminate\Database\Migrations\Migration;

class AddPrintReceiptAbilityToManagers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('manager')->to([
            'print receipts',
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
