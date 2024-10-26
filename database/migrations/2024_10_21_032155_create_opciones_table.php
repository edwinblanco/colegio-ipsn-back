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
        Schema::create('opciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pregunta_id')->nullable();
            // Definir la relación con preguntas (usando el id por defecto)
            $table->foreign('pregunta_id')
                  ->references('id')
                  ->on('preguntas')
                  ->onDelete('cascade');

            $table->text('contenido'); // Texto de la opción
            $table->boolean('correcta'); // Si la opción es correcta
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
        Schema::dropIfExists('opciones');
    }
};
