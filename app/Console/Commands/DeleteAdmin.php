<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteAdmin extends Command
{
    protected $signature = 'admin:delete {--email=}';

    protected $description = 'Delete an administrator account.';

    public function handle(): int
    {
        $email = $this->option('email');

        if (! $email) {
            if (! $this->isInteractiveTerminal()) {
                $this->error('Δώστε email με --email=...');

                return self::FAILURE;
            }

            $email = $this->ask('Email');
        }

        try {
            $this->validateInput($email);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        if ($this->isInteractiveTerminal() && ! $this->confirm("Να διαγραφεί ο διαχειριστής {$email};")) {
            $this->info('Η διαγραφή ακυρώθηκε.');

            return self::SUCCESS;
        }

        return DB::transaction(function () use ($email): int {
            $users = User::query()
                ->lockForUpdate()
                ->get();

            if ($users->count() <= 1) {
                $this->error('Δεν μπορεί να διαγραφεί ο τελευταίος διαχειριστής.');

                return self::FAILURE;
            }

            $user = $users->firstWhere('email', $email);

            if (! $user instanceof User) {
                $this->error('Δεν βρέθηκε διαχειριστής με αυτό το email.');

                return self::FAILURE;
            }

            $user->delete();

            $this->info("Ο διαχειριστής {$email} διαγράφηκε.");

            return self::SUCCESS;
        });
    }

    private function validateInput(mixed $email): void
    {
        Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email']],
            [
                'email.required' => 'Το email είναι υποχρεωτικό.',
                'email.email' => 'Το email δεν είναι έγκυρο.',
            ],
        )->validate();
    }

    private function isInteractiveTerminal(): bool
    {
        return $this->input->isInteractive()
            && \defined('STDIN')
            && \function_exists('stream_isatty')
            && @stream_isatty(STDIN);
    }
}
