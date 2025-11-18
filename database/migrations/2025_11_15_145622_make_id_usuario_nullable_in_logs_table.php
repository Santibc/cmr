<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eliminar la clave foránea
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
        });

        // Usar SQL directo para modificar la columna
        DB::statement('ALTER TABLE `logs` MODIFY COLUMN `id_usuario` BIGINT UNSIGNED NULL');

        // Volver a agregar la clave foránea
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar la clave foránea
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
        });

        // Revertir a NOT NULL usando SQL directo
        DB::statement('ALTER TABLE `logs` MODIFY COLUMN `id_usuario` BIGINT UNSIGNED NOT NULL');

        // Volver a agregar la clave foránea
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
