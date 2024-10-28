<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Examen;

class Grado extends Model
{
    use HasFactory;

    protected $table = 'grados';

    // Especifica los campos que se pueden asignar de manera masiva
    protected $fillable = [
        'grado',
        'salon',
    ];

    /**
     * Relación uno a muchos con el modelo User.
     */
    public function estudiantes()
    {
        return $this->hasMany(User::class, 'grado_id');
    }
    public function examenes()
    {
        return $this->belongsToMany(Examen::class, 'examen_grado', 'grado_id', 'examen_id');
    }
}
