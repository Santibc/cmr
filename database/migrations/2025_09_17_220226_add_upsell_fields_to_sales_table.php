<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('upsell', ['pendiente', 'aprobado'])->nullable()->after('tipo_contrato');
            $table->string('upsell_comprobante_path')->nullable()->after('upsell');
            $table->text('upsell_comentarios')->nullable()->after('upsell_comprobante_path');
            $table->timestamp('upsell_fecha_pendiente')->nullable()->after('upsell_comentarios');
            $table->timestamp('upsell_fecha_aprobado')->nullable()->after('upsell_fecha_pendiente');
            $table->foreignId('upsell_user_pendiente')->nullable()->constrained('users')->onDelete('set null')->after('upsell_fecha_aprobado');
            $table->foreignId('upsell_user_aprobado')->nullable()->constrained('users')->onDelete('set null')->after('upsell_user_pendiente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['upsell_user_pendiente']);
            $table->dropForeign(['upsell_user_aprobado']);
            $table->dropColumn([
                'upsell',
                'upsell_comprobante_path',
                'upsell_comentarios',
                'upsell_fecha_pendiente',
                'upsell_fecha_aprobado',
                'upsell_user_pendiente',
                'upsell_user_aprobado'
            ]);
        });
    }
};
