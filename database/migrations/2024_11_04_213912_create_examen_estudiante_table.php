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
        Schema::create('examen_estudiante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade'); // asume que estudiantes están en la tabla 'users'
            $table->foreignId('examen_id')->constrained('examenes')->onDelete('cascade');
            $table->dateTime('fecha_presentacion')->nullable(); // La fecha de presentación, si es nula, el estudiante no ha presentado el examen
            $table->integer('puntaje')->nullable(); // Puntaje del examen
            $table->enum('estado', ['completado', 'pendiente', 'en proceso'])->default('pendiente'); // Estado del examen

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
        Schema::dropIfExists('examen_estudiante');
    }
};
