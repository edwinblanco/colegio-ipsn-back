<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'descripcion',
    ];

    // Relación estudiantes
    public function users()
    {
        return $this->hasMany(User::class, 'id_sede');
    }

}
