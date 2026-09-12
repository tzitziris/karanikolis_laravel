<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListAdmins extends Command
{
    protected $signature = 'admin:list';

    protected $description = 'List administrator accounts.';

    public function handle(): int
    {
        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'email', 'created_at']);

        if ($users->isEmpty()) {
            $this->info('Δεν υπάρχουν διαχειριστές.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Email', 'Δημιουργήθηκε'],
            $users->map(fn (User $user): array => [
                'ID' => $user->id,
                'Email' => $user->email,
                'Δημιουργήθηκε' => optional($user->created_at)->toDateTimeString(),
            ])->all(),
        );

        return self::SUCCESS;
    }
}
