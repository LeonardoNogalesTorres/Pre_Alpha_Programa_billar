<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    // Asegúrate de que 'tipo_pago' esté en este array
    protected $fillable = ['numero', 'estado', 'tarifa_hora', 'hora_inicio', 'tipo_pago'];

    public $timestamps = false; // Como vimos antes para evitar el error de updated_at
}