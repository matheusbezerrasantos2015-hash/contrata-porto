<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    /** @var array<string, string>|null */
    protected static ?array $legacyColumnMap = null;

    protected $fillable = [
        'nome',
        'email',
        'password',
        'tipo',
        'email_verified_at',
        'verification_token',
        'reset_token',
        'reset_token_expires_at',
        'avatar',
        'telefone',
        'cidade',
        'estado',
        'curriculo',
        'empresa_id',
        'linkedin',
        'portfolio',
        'area',
        'nivel_experiencia',
        'sobre',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'reset_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'reset_token_expires_at' => 'datetime',
            'deleted_at'             => 'datetime',
            'password'               => 'hashed',
        ];
    }

    /**
     * Mapeia nomes lógicos (Laravel) para colunas físicas (legado ou novo schema).
     */
    protected static function physicalColumn(string $logical): string
    {
        if (static::$legacyColumnMap === null) {
            $table = (new static)->getTable();

            static::$legacyColumnMap = [
                'password' => Schema::hasColumn($table, 'password')
                    ? 'password'
                    : (Schema::hasColumn($table, 'senha') ? 'senha' : 'password'),
                'tipo' => Schema::hasColumn($table, 'tipo')
                    ? 'tipo'
                    : (Schema::hasColumn($table, 'role') ? 'role' : 'tipo'),
                'avatar' => Schema::hasColumn($table, 'avatar')
                    ? 'avatar'
                    : (Schema::hasColumn($table, 'avatar_path') ? 'avatar_path' : 'avatar'),
            ];
        }

        return static::$legacyColumnMap[$logical] ?? $logical;
    }

    public function getAttribute($key)
    {
        if ($key === 'password') {
            $physical = static::physicalColumn('password');

            return $this->attributes[$physical] ?? null;
        }

        if ($key === 'tipo') {
            $physical = static::physicalColumn('tipo');

            return isset($this->attributes[$physical])
                ? strtoupper((string) $this->attributes[$physical])
                : null;
        }

        if ($key === 'avatar') {
            $physical = static::physicalColumn('avatar');

            return $this->attributes[$physical] ?? null;
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, ['password', 'tipo', 'avatar'], true)) {
            $physical = static::physicalColumn($key);
            if ($key === 'tipo' && $value !== null) {
                $value = strtoupper((string) $value);
            }

            return parent::setAttribute($physical, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Senha para autenticação (compatível com coluna legada `senha`).
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->password ?? '');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        $cloudName = config('services.cloudinary.cloud_name');

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$this->avatar}";
    }

    public function getCurriculoUrlAttribute(): ?string
    {
        if (! $this->curriculo) {
            return null;
        }

        if (str_starts_with($this->curriculo, 'http')) {
            return $this->curriculo;
        }

        $cloudName = config('services.cloudinary.cloud_name');

        return "https://res.cloudinary.com/{$cloudName}/raw/upload/{$this->curriculo}";
    }

    public function isCandidato(): bool
    {
        return $this->tipo === 'CANDIDATO';
    }

    public function isEmpresa(): bool
    {
        return $this->tipo === 'EMPRESA';
    }

    public function hasVerifiedEmail(): bool
    {
        if (! is_null($this->email_verified_at)) {
            return true;
        }

        if (array_key_exists('email_verified', $this->attributes)) {
            return (bool) $this->attributes['email_verified'];
        }

        return false;
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
