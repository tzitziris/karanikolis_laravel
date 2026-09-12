<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {--email=} {--password-file=}';

    protected $description = 'Create or update an administrator account.';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->askForEmail();
        $password = $this->readPassword();

        if ($email === null || $password === null) {
            return self::FAILURE;
        }

        try {
            $this->validateInput($email, $password);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => Str::lower($email)],
            [
                'name' => Str::before($email, '@'),
                'password' => Hash::make($password),
            ],
        );

        $this->info("Ο διαχειριστής {$user->email} είναι έτοιμος.");

        return self::SUCCESS;
    }

    private function askForEmail(): ?string
    {
        if (! $this->isInteractiveTerminal()) {
            $this->error('Δώστε email με --email=...');

            return null;
        }

        return $this->ask('Email');
    }

    private function readPassword(): ?string
    {
        $passwordFile = $this->option('password-file');

        if ($passwordFile) {
            return $this->readPasswordFile($passwordFile);
        }

        if (! $this->isInteractiveTerminal()) {
            $this->error('Δώστε αρχείο κωδικού με --password-file=... Ο κωδικός δεν πρέπει να μπει στη γραμμή της εντολής.');

            return null;
        }

        return $this->secret('Κωδικός');
    }

    private function readPasswordFile(string $path): ?string
    {
        if (! File::isFile($path) || ! File::isReadable($path)) {
            $this->error('Το αρχείο κωδικού δεν βρέθηκε ή δεν διαβάζεται.');

            return null;
        }

        $password = rtrim(File::get($path), "\r\n");
        File::delete($path);

        return $password;
    }

    private function validateInput(string $email, string $password): void
    {
        Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:10'],
            ],
            [
                'email.required' => 'Το email είναι υποχρεωτικό.',
                'email.email' => 'Το email δεν είναι έγκυρο.',
                'password.required' => 'Ο κωδικός είναι υποχρεωτικός.',
                'password.min' => 'Ο κωδικός πρέπει να έχει τουλάχιστον 10 χαρακτήρες.',
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
