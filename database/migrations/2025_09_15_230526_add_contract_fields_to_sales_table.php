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
            $table->foreignId('contract_template_id')->nullable()->constrained('contract_templates')->onDelete('set null');
            $table->boolean('contract_approved')->default(false);
            $table->json('contract_data')->nullable();
            $table->string('contract_token')->nullable()->unique();
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
            $table->dropForeign(['contract_template_id']);
            $table->dropColumn(['contract_template_id', 'contract_approved', 'contract_data', 'contract_token']);
        });
    }
};
