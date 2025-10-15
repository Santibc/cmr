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
        $traigeStatuses = [
            'Llamadas agendadas',
            'Asistencias',
            'Canceladas',
            'Calificadas',
            'Tasa de asistencia',
            'Tasa de calificación'
        ];

        foreach ($traigeStatuses as $status) {
            DB::table('pipeline_statuses')->insert([
                'name' => $status,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $traigeStatuses = [
            'Llamadas agendadas',
            'Asistencias',
            'Canceladas',
            'Calificadas',
            'Tasa de asistencia',
            'Tasa de calificación'
        ];

        DB::table('pipeline_statuses')->whereIn('name', $traigeStatuses)->delete();
    }
};
