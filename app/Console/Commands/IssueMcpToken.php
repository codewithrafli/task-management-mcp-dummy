<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mcp:token {email : User email} {--name=mcp : Token label} {--admin : Make the user an admin if created}')]
#[Description('Issue a Passport personal access token for the MCP HTTP endpoint')]
class IssueMcpToken extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => str($email)->before('@')->toString(),
                'password' => bcrypt(str()->random(32)),
                'is_admin' => (bool) $this->option('admin'),
            ],
        );

        $token = $user->createToken($this->option('name'))->accessToken;

        $this->info("Token for {$user->email} (admin: ".($user->is_admin ? 'yes' : 'no').'):');
        $this->line($token);
        $this->newLine();
        $this->comment('Use it as header: Authorization: Bearer '.$token);

        return self::SUCCESS;
    }
}
