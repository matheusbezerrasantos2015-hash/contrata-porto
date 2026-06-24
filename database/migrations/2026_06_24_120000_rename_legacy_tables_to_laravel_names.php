<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinha tabelas legadas (vagas/empresas) com os nomes esperados pelo Laravel.
     */
    public function up(): void
    {
        if (Schema::hasTable('vagas') && ! Schema::hasTable('job_listings')) {
            Schema::rename('vagas', 'job_listings');
        }

        if (Schema::hasTable('empresas') && ! Schema::hasTable('companies')) {
            Schema::rename('empresas', 'companies');
        }

        if (Schema::hasTable('job_listings')
            && Schema::hasColumn('job_listings', 'empresa_id')
            && ! Schema::hasColumn('job_listings', 'company_id')
        ) {
            $column = DB::selectOne("SHOW COLUMNS FROM job_listings WHERE Field = 'empresa_id'");
            if ($column) {
                $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
                DB::statement("ALTER TABLE job_listings CHANGE empresa_id company_id {$column->Type} {$null}");
            }
        }

        $this->ensureJobListingsColumns();
    }

    /**
     * Garante colunas adicionais usadas pela API Laravel.
     */
    private function ensureJobListingsColumns(): void
    {
        if (! Schema::hasTable('job_listings')) {
            return;
        }

        Schema::table('job_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('job_listings', 'cargo')) {
                $table->string('cargo')->nullable()->after('titulo');
            }
            if (! Schema::hasColumn('job_listings', 'area')) {
                $table->string('area', 100)->nullable()->after('cargo');
            }
            if (! Schema::hasColumn('job_listings', 'experiencia')) {
                $table->string('experiencia')->nullable()->after('nivel');
            }
            if (! Schema::hasColumn('job_listings', 'carga_horaria')) {
                $table->string('carga_horaria')->nullable()->after('experiencia');
            }
            if (! Schema::hasColumn('job_listings', 'beneficios')) {
                $table->text('beneficios')->nullable()->after('requisitos');
            }
            if (! Schema::hasColumn('job_listings', 'diferenciais')) {
                $table->text('diferenciais')->nullable()->after('beneficios');
            }
            if (! Schema::hasColumn('job_listings', 'ativo')) {
                $table->boolean('ativo')->default(true);
            }
            if (! Schema::hasColumn('job_listings', 'encerrada_em')) {
                $table->timestamp('encerrada_em')->nullable();
            }
            if (! Schema::hasColumn('job_listings', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('job_listings') && ! Schema::hasTable('vagas')) {
            Schema::rename('job_listings', 'vagas');
        }

        if (Schema::hasTable('companies') && ! Schema::hasTable('empresas')) {
            Schema::rename('companies', 'empresas');
        }

        if (Schema::hasTable('vagas')
            && Schema::hasColumn('vagas', 'company_id')
            && ! Schema::hasColumn('vagas', 'empresa_id')
        ) {
            $column = DB::selectOne("SHOW COLUMNS FROM vagas WHERE Field = 'company_id'");
            if ($column) {
                $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
                DB::statement("ALTER TABLE vagas CHANGE company_id empresa_id {$column->Type} {$null}");
            }
        }
    }
};
