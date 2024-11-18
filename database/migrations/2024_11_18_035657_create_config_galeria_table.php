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
        Schema::create('config_galeria', function (Blueprint $table) {
            $table->id();
            $table->string('titulo'); // Columna 'titulo' para el título del anuncio
            $table->text('descripcion'); // Columna 'descripcion' para la descripción del anuncio
            $table->date('fecha_publicacion'); // Columna 'fechapublicacion' para la fecha de publicación
            $table->enum('tipo', ['noticia', 'evento', 'anuncio']); // Columna 'tipo' para el tipo de anuncio
            $table->boolean('activo')->default(true); // Columna 'activo' para determinar si el anuncio está activo
            $table->string('url_imagen')->nullable(); // Columna 'url_imagen' para la URL de la imagen (puede ser nula)
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
        Schema::dropIfExists('config_galeria');
    }
};
