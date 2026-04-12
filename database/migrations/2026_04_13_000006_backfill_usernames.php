<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Backfill usernames for all existing users.
     *
     * @return void
     */
    public function up()
    {
        $users = DB::table('users')->whereNull('username')->get();

        foreach ($users as $user) {
            $baseSlug = Str::slug($user->name, '.');

            // Ensure it's not empty (e.g. non-latin names)
            if (empty($baseSlug)) {
                $baseSlug = 'user';
            }

            $username = $baseSlug;

            // Check if this username already exists
            $exists = DB::table('users')
                ->where('username', $username)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                $username = $baseSlug . '.' . $user->id;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed – usernames will remain
    }
};
