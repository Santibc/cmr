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
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('passed_to_closer')->default(false)->after('pipeline_status_id');
            $table->timestamp('passed_to_closer_at')->nullable()->after('passed_to_closer');
            $table->foreignId('passed_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('passed_to_closer_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['passed_by_user_id']);
            $table->dropColumn(['passed_to_closer', 'passed_to_closer_at', 'passed_by_user_id']);
        });
    }
};
