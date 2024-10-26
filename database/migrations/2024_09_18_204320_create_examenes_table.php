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
        Schema::create('examenes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('materia_id')->nullable();
            $table->unsignedBigInteger('profesor_id')->nullable();

            // Definir la relación con materias
            $table->foreign('materia_id')
                  ->references('id')
                  ->on('materias')
                  ->onDelete('set null');

            // Definir la relación con profesores (usando el id por defecto)
            $table->foreign('profesor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->string('titulo');
            $table->text('descripcion');
            $table->dateTime('fecha_limite');
            $table->enum('estado', ['activo', 'cerrado']); // Enum para estado
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
        Schema::dropIfExists('examenes');
    }
};
