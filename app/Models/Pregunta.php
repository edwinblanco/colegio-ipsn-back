<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Opcion;

class Pregunta extends Model
{
    use HasFactory;

    protected $fillable = [
        'examen_id',
        'contenido',
        'valor',
    ];

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    // Relación con el modelo Opcion (una pregunta tiene muchas opciones)
    public function opciones()
    {
        return $this->hasMany(Opcion::class);
    }
}
