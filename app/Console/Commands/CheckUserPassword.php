<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class CheckUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:password {userid} {plainPassword?}';

    public function handle()
    {
        $userid = $this->argument('userid');
        $plainPassword = $this->argument('plainPassword');

        $user = User::where('userid', $userid)->first();

        if (!$user) {
            $this->error("User {$userid} not found!");
            return;
        }

        $pwd = $user->password;
        $this->info("Password Length: " . strlen($pwd));
        
        $info = password_get_info($pwd);
        $this->info("Current Algo: " . $info['algoName'] . " (" . $info['algo'] . ")");

        if (strlen($pwd) === 32 && ctype_xdigit($pwd)) {
             $this->info("Potential format: MD5 (32 chars hex)");
        } elseif (substr($pwd, 0, 4) === '$2y$') {
             $this->info("Potential format: Bcrypt");
        } else {
             $this->info("Format: Custom/Unknown");
             $this->info("First 5 chars: " . substr($pwd, 0, 5));
        }

        $this->info("Current Hashing Driver: " . config('hashing.driver'));

        if ($plainPassword) {
            $check = Hash::check($plainPassword, $pwd);
            if ($check) {
                $this->info("SUCCESS: Password matches!");
            } else {
                $this->error("FAILED: Password does NOT match.");
                $this->line("Debug MD5: " . md5($plainPassword));
            }
        }
    }
}
