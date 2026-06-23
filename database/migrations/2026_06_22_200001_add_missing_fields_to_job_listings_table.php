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
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('titulo');
            $table->string('area', 100)->nullable()->after('cargo');
            $table->string('experiencia')->nullable()->after('nivel');
            $table->string('carga_horaria')->nullable()->after('experiencia');
            $table->text('beneficios')->nullable()->after('requisitos');
            $table->text('diferenciais')->nullable()->after('beneficios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn([
                'cargo',
                'area',
                'experiencia',
                'carga_horaria',
                'beneficios',
                'diferenciais',
            ]);
        });
    }
};
