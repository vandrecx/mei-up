<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Checkin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'data_checkin',
        'humor_financeiro',
        'observacoes',
        'objetivos_alcancados',
        'dificuldades',
        'proximos_passos',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
