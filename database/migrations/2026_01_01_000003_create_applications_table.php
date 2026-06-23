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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_listings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('curriculo_path')->nullable()->comment('Path local ou URL Cloudinary do currículo');
            $table->string('curriculo_url', 500)->nullable()->comment('URL pública Cloudinary');
            $table->string('curriculo_public_id', 200)->nullable()->comment('Public ID no Cloudinary para remoção');
            $table->enum('status', ['pendente', 'em_analise', 'aprovado', 'recusado'])->default('pendente');
            $table->text('mensagem')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
