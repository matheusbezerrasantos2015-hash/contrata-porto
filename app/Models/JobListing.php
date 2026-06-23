<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Named 'job_listings' to avoid conflict with Laravel's built-in 'jobs' queue table.
     *
     * @var string
     */
    protected $table = 'job_listings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'titulo',
        'cargo',
        'area',
        'descricao',
        'requisitos',
        'beneficios',
        'diferenciais',
        'tipo',
        'modalidade',
        'nivel',
        'experiencia',
        'carga_horaria',
        'salario_min',
        'salario_max',
        'cidade',
        'estado',
        'ativo',
        'encerrada_em',
        'expires_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'tipo_contrato',
        'tipo_vaga',
        'empresa_nome',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salario_min'  => 'decimal:2',
            'salario_max'  => 'decimal:2',
            'ativo'        => 'boolean',
            'encerrada_em' => 'datetime',
            'expires_at'   => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────

    /**
     * Accessor de compatibilidade para tipo_contrato.
     */
    public function getTipoContratoAttribute(): string
    {
        return $this->tipo;
    }

    /**
     * Accessor de compatibilidade para tipo_vaga.
     */
    public function getTipoVagaAttribute(): string
    {
        return $this->modalidade;
    }

    /**
     * Accessor de compatibilidade para empresa_nome.
     */
    public function getEmpresaNomeAttribute(): ?string
    {
        return $this->company?->nome_fantasia;
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    /**
     * Scope para listar apenas vagas ativas e não expiradas.
     */
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativo', true)
                     ->where(function (Builder $q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
    }

    /**
     * Scope para filtrar por tipo de contrato.
     */
    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para filtrar por modalidade.
     */
    public function scopeDeModalidade(Builder $query, string $modalidade): Builder
    {
        return $query->where('modalidade', $modalidade);
    }

    /**
     * Scope para filtrar por nível.
     */
    public function scopeDeNivel(Builder $query, string $nivel): Builder
    {
        return $query->where('nivel', $nivel);
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Empresa que publicou a vaga.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Candidaturas recebidas para esta vaga.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    /**
     * Usuários que favoritaram esta vaga.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'job_id', 'user_id')
                    ->withTimestamps();
    }
}
