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
        Schema::create('imagenes_preguntas', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('id_pregunta');
            $table->foreign('id_pregunta')->references('id')->on('preguntas')->onDelete('cascade');

            $table->string('url'); // Columna para la URL de la imagen
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
        Schema::dropIfExists('imagenes_preguntas');
    }
};
