<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adequa a tabela users legada (PHP puro) ao schema esperado pelo Laravel.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $this->renameColumnIfExists('users', 'senha', 'password');
        $this->renameColumnIfExists('users', 'role', 'tipo');
        $this->renameColumnIfExists('users', 'avatar_path', 'avatar');

        if (! Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }

        if (Schema::hasColumn('users', 'email_verified')) {
            DB::statement('
                UPDATE users
                SET email_verified_at = COALESCE(email_verified_at, created_at)
                WHERE email_verified = 1
                  AND email_verified_at IS NULL
            ');
        }

        DB::statement("
            UPDATE users
            SET tipo = UPPER(tipo)
            WHERE tipo IS NOT NULL
              AND tipo <> UPPER(tipo)
        ");

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->nullable();
            }
            if (! Schema::hasColumn('users', 'verification_token')) {
                $table->string('verification_token')->nullable();
            }
            if (! Schema::hasColumn('users', 'reset_token')) {
                $table->string('reset_token')->nullable();
            }
            if (! Schema::hasColumn('users', 'reset_token_expires_at')) {
                $table->timestamp('reset_token_expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'estado')) {
                $table->string('estado', 2)->nullable();
            }
            if (! Schema::hasColumn('users', 'curriculo')) {
                $table->string('curriculo')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['curriculo', 'estado', 'reset_token_expires_at', 'reset_token', 'verification_token', 'remember_token'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }

        $this->renameColumnIfExists('users', 'password', 'senha');
        $this->renameColumnIfExists('users', 'tipo', 'role');
        $this->renameColumnIfExists('users', 'avatar', 'avatar_path');
    }

    private function renameColumnIfExists(string $table, string $from, string $to): void
    {
        if (! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$from]);

        if (! $column) {
            return;
        }

        $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
        $default = '';

        if ($column->Default !== null) {
            $default = 'DEFAULT ' . (is_numeric($column->Default) ? $column->Default : DB::getPdo()->quote($column->Default));
        } elseif ($column->Null === 'YES') {
            $default = 'DEFAULT NULL';
        }

        DB::statement("ALTER TABLE {$table} CHANGE `{$from}` `{$to}` {$column->Type} {$null} {$default}");
    }
};
