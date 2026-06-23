<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao');
            $table->text('requisitos')->nullable();
            $table->enum('tipo', ['CLT', 'PJ', 'Estágio', 'Freelance', 'Temporário'])->default('CLT');
            $table->enum('modalidade', ['Presencial', 'Remoto', 'Híbrido'])->default('Presencial');
            $table->enum('nivel', ['Júnior', 'Pleno', 'Sênior', 'Estágio', 'Sem experiência'])->default('Pleno');
            $table->decimal('salario_min', 10, 2)->nullable();
            $table->decimal('salario_max', 10, 2)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('encerrada_em')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['ativo', 'expires_at']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
