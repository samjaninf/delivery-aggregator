<?php

use App\Store;
use App\User;
use Illuminate\Database\Migrations\Migration;

class MoveFromRoleSystemToBouncer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creating roles
        $admin = Bouncer::role()->firstOrCreate([
            'name' => 'admin',
            'title' => 'Amministratore',
        ]);
        $manager = Bouncer::role()->firstOrCreate([
            'name' => 'manager',
            'title' => 'Gestore',
        ]);
        $courier = Bouncer::role()->firstOrCreate([
            'name' => 'courier',
            'title' => 'Corriere',
        ]);

        // Assigning abilities
        Bouncer::allow($admin)->everything();
        Bouncer::allow($manager)->toOwn(Store::class)->to([
            'view orders',
            'set out for delivery',
            'manage products',
        ]);
        Bouncer::allow($courier)->toOwn(Store::class)->to([
            'view orders',
            'set delivered',
        ]);

        // Update user roles
        $oldAdmins = User::where('role', 30)->get();
        foreach ($oldAdmins as $u) {
            Bouncer::sync($u)->roles([$admin]);
        }
        $oldManagers = User::where('role', 20)->get();
        foreach ($oldManagers as $u) {
            Bouncer::sync($u)->roles([$manager]);
        }
        $oldUsers = User::where('role', 10)->get();
        foreach ($oldUsers as $u) {
            Bouncer::sync($u)->roles([$courier]);
        }

        // Delete old role column
        Schema::table('users', function ($table) {
            $table->dropColumn('role');
        });
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
