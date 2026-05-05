<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class FixUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kinanti:fix-password';

    /**
     * The console command description.
     */
    protected $description = 'Convert all plaintext passwords to hashed passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking user passwords...');

        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {

            // Jika belum hash (cek tidak diawali $2y$)
            if (!str_starts_with($user->password, '$2y$')) {

                $this->line("⚠️ Fixing: {$user->kode_barcode}");

                $user->password = Hash::make($user->password);
                $user->save();

                $updated++;
            }
        }

        $this->info("✅ Selesai! Total diperbaiki: {$updated} user");
    }
}