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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('primer_nombre', 15);
            $table->string('segundo_nombre' , 15)->nullable();
            $table->string('primer_apellido', 15);
            $table->string('segundo_apellido', 15)->nullable();
            $table->string('numero_documento', 20);
            $table->date('fecha_nacimiento');
            $table->string('email', 30)->unique()->nullable();
            $table->string('password');
            $table->enum('estado', ['activo', 'inactivo', 'graduado', 'expulsado'])->default('activo');
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
        Schema::dropIfExists('users');
    }
};
