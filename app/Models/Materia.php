<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'bg_color'
    ];

    public function profesores()
    {
        return $this->belongsToMany(User::class, 'materia_profesor');
    }


    // Relación con exámenes
    public function examenes()
    {
        return $this->hasMany(Examen::class, 'materia_id');
    }

}
