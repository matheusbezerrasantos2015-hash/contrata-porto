<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_id',
        'user_id',
        'curriculo_path',
        'curriculo_url',
        'curriculo_public_id',
        'status',
        'mensagem',
        'linkedin',
        'portfolio',
        'telefone',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────

    /**
     * Retorna a URL pública do currículo.
     * Prioriza curriculo_url (Cloudinary), cai de volta para curriculo_path.
     */
    public function getCurriculoPublicoAttribute(): ?string
    {
        if ($this->curriculo_url) {
            return $this->curriculo_url;
        }

        if ($this->curriculo_path && str_starts_with($this->curriculo_path, 'http')) {
            return $this->curriculo_path;
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /**
     * Vaga à qual pertence a candidatura.
     */
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    /**
     * Candidato (usuário) que se candidatou.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
