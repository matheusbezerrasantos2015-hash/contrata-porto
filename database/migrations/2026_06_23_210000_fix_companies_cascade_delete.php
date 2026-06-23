<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recria a FK de companies.user_id com CASCADE DELETE.
     * Quando um usuário EMPRESA for soft-deletado fisicamente,
     * a empresa vinculada é removida automaticamente pelo banco.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Remove a FK existente (sem cascade)
            $table->dropForeign(['user_id']);

            // Recria com onDelete('cascade')
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverte para FK sem cascade.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};
