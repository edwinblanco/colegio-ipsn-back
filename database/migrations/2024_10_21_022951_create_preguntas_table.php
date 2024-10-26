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
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('examen_id')->nullable();
            // Definir la relación con examenes (usando el id por defecto)
            $table->foreign('examen_id')
                  ->references('id')
                  ->on('examenes')
                  ->onDelete('cascade');

            $table->text('contenido'); // Enunciado de la pregunta
            $table->integer('valor');  // Porcentaje de validez de la pregunta
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
        Schema::dropIfExists('preguntas');
    }
};
