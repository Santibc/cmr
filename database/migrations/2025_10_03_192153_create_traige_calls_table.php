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
        Schema::create('traige_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Traige user
            $table->foreignId('parent_call_id')->nullable()->constrained('traige_calls')->onDelete('set null'); // Para reprogramaciones
            $table->datetime('scheduled_date');
            $table->string('call_link');
            $table->text('notes')->nullable();
            $table->enum('status', ['pendiente', 'realizada', 'no_realizada', 'reprogramada'])->default('pendiente');
            $table->text('comments')->nullable(); // Comentarios post-llamada
            $table->boolean('email_sent')->default(false);
            $table->datetime('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traige_calls');
    }
};
