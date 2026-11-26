<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InitializeInvestmentOwner
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function create(array $attributes): User
    {
        if (User::query()->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Akun pemilik sudah dibuat. Pembuatan akun pertama telah ditutup.',
            ]);
        }

        return DB::transaction(function () use ($attributes): User {
            if (User::query()->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Akun pemilik sudah dibuat. Pembuatan akun pertama telah ditutup.',
                ]);
            }

            $owner = User::create($attributes);
            $owner->forceFill(['is_investment_owner' => true])->save();

            DB::table('sahams')
                ->whereNull('user_id')
                ->update(['user_id' => $owner->id]);

            return $owner;
        });
    }
}
