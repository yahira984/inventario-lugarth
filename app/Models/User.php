<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'approved_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function aprobado(): bool
    {
        return $this->approved_at !== null;
    }

    public function esAdministrador(): bool
    {
        return $this->role === 'administrador';
    }

    public function esAlmacenista(): bool
    {
        return $this->role === 'almacenista';
    }

    public function esConsultor(): bool
    {
        return $this->role === 'consultor';
    }

    public function puedeMoverStock(): bool
    {
        return in_array($this->role, ['administrador', 'almacenista'], true);
    }

    public function puedeAdministrarCatalogo(): bool
    {
        return $this->esAdministrador();
    }
}
