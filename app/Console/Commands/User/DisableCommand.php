<?php
namespace App\Console\Commands\User;

use App\Console\Command;
use App\Models\User;

class DisableCommand extends Command {


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:disable {email : Email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a user';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $email = $this->argument('email');

        User::where('email', $email)->update(['disabled' => true])
            ? $this->info(sprintf('User identified by %s has been disabled', $email))
            : $this->error(sprintf('Failed disabling user identified by %s', $email), null, true, true);
    }
}
