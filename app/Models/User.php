<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'reset_token_expires_at' => 'datetime',
            'password'               => 'hashed',
        ];
    }

    // ─────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────

    /**
     * Retorna a URL completa do avatar Cloudinary.
     * Se o campo já contiver uma URL completa (https://), retorna como está.
     * Caso contrário, monta a URL base do Cloudinary.
     */
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

    /**
     * Retorna a URL completa do currículo (PDF) no Cloudinary.
     */
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

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Verifica se o usuário é candidato.
     */
    public function isCandidato(): bool
    {
        return $this->tipo === 'CANDIDATO';
    }

    /**
     * Verifica se o usuário é empresa.
     */
    public function isEmpresa(): bool
    {
        return $this->tipo === 'EMPRESA';
    }

    /**
     * Verifica se o email foi verificado.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Empresa vinculada ao usuário (quando tipo = 'EMPRESA').
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Candidaturas do usuário (quando tipo = 'CANDIDATO').
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Vagas favoritas do usuário.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
