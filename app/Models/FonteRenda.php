<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class FonteRenda extends Model
{
    use SoftDeletes;

    protected $table = 'fonte_rendas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'usuario_id',
        'descricao',
        'valor',
        'tipo',
        'data_recebimento',
        'ativo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'data_recebimento' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Relationship: Fonte de renda pertence a um usuário.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
