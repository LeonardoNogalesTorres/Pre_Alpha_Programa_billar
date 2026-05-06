<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = ['nombre', 'precio_venta', 'categoria'];
    public $timestamps = false; // Como la creamos manual, evitamos errores de fecha
}