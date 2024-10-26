<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Tipo_documento extends Model
{
    use HasFactory;

    protected $table = 'tipo_documento';

    protected $fillable = ['nombre', 'descripcion'];

    public function users()
    {
        return $this->hasMany(User::class, 'tipo_documento_id');
    }

}
