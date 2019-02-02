<?php

use App\Store;
use Illuminate\Database\Migrations\Migration;

class AddSetPreparedAndSetSeenAbilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bouncer::allow('manager')->toOwn(Store::class)->to([
            'set seen',
            'set prepared',
        ]);

        Bouncer::forbid('admin')->to('set seen');
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
