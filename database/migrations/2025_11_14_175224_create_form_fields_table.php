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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('label');
            $table->string('field_type')->comment('text, textarea, email, number, date, select, radio, checkbox, scale, rating');
            $table->string('field_name');
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->json('options')->nullable()->comment('Options for select, radio, checkbox fields');
            $table->json('validations')->nullable()->comment('Validation rules: required, min, max, email, numeric');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable()->comment('Help text to display below field');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_fields');
    }
};
