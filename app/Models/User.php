<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleValue(): string
    {
        $role = $this->attributes['role'] ?? 'annotator';

        return is_string($role) ? $role : UserRole::Annotator->value;
    }

    public function isAdmin(): bool
    {
        return $this->roleValue() === UserRole::Admin->value;
    }

    public function isAnnotator(): bool
    {
        return in_array($this->roleValue(), [UserRole::Annotator->value, UserRole::Admin->value], true);
    }
}
