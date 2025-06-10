<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GastoFixo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'conta_id',
        'descricao',
        'valor',
        'categoria',
        'dia_vencimento',
        'ativo',
    ];

    // Se quiser que o Laravel trate automaticamente o deleted_at como data:
    protected $dates = ['deleted_at'];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }
}
