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
    Schema::table('users', function ($table) {
        $table->timestamp('last_synced_at')->nullable()->after('calendly_uri');
    });
}

public function down()
{
    Schema::table('users', function ($table) {
        $table->dropColumn('last_synced_at');
    });
}
};
