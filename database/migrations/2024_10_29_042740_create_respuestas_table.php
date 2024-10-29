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
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('pregunta_id');
            $table->foreign('pregunta_id')->references('id')->on('preguntas')->onDelete('cascade');

            $table->unsignedBigInteger('examen_id');
            $table->foreign('examen_id')->references('id')->on('examenes')->onDelete('cascade');

            $table->unsignedBigInteger('respuesta_id');
            $table->foreign('respuesta_id')->references('id')->on('opciones')->onDelete('cascade');

            $table->integer('calificacion')->nullable(); // Columna para la calificación

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
        Schema::dropIfExists('respuestas');
    }
};
