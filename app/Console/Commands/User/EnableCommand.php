<?php
namespace App\Console\Commands\User;

use App\Console\Command;
use App\Models\User;

class EnableCommand extends Command {


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:enable {email : Email address of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a user';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $email = $this->argument('email');

        User::where('email', $email)->update(['disabled' => false])
            ? $this->info(sprintf('User identified by %s has been enabled', $email))
            : $this->error(sprintf('Failed enabling user identified by %s', $email), null, true, true);
    }
}
