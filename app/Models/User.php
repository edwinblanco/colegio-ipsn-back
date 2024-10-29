<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tipo_documento;
use App\Models\Grado;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = "users";

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'numero_documento',
        'fecha_nacimiento',
        'email',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password'
    ];

    // relacion con materias
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_profesor', 'profesor_id', 'materia_id');
    }

    // Relación con exámenes
    public function examenes()
    {
        return $this->hasMany(Examen::class, 'profesor_id');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'estudiante_id');
    }
    
    // Relación con grados
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    // relacion con tipo de documento
    public function tipo_documento()
    {
        return $this->belongsTo(Tipo_documento::class, 'tipo_documento_id');
    }


}
