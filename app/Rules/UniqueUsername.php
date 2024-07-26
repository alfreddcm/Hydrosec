<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UniqueUsername implements Rule
{
    protected $tables;

    public function __construct(array $tables)
    {
        $this->tables = $tables;
    }

    public function passes($attribute, $value)
    {
        // Encrypt the input username to match the encryption format in the database
        $encryptedValue = Crypt::encryptString($value);

        foreach ($this->tables as $table) {
            $users = DB::table($table)->get();

            foreach ($users as $user) {
                try {
                    $decryptedUsername = Crypt::decryptString($user->username);
                    if ($encryptedValue === $user->username) {
                        return false;
                    }
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    // Log error if decryption fails
                    Log::error('Failed to decrypt username in table ' . $table, ['error' => $e->getMessage()]);
                }
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute has already been taken in one of the tables.';
    }
}
