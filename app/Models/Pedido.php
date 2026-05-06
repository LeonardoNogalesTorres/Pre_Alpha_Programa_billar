<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = ['mesa_id', 'producto_id', 'cantidad', 'precio_unitario'];

    // Esto evita el error de "Column not found: updated_at"
    public $timestamps = false; 

    public function producto() {
        return $this->belongsTo(Producto::class);
    }
}