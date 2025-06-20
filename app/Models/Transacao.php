<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transacao extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 
     *
     * @var string
     */
    protected $table = 'transacaos';

    /**
     * 
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario_id',
        'conta_id',
        'descricao',
        'valor',
        'tipo',
        'categoria',
        'forma_pagamento',
        'data_transacao',
        'observacoes',
    ];

    /**
     * 
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_transacao' => 'date', 
        'valor' => 'decimal:2',     
    ];

    /**
     * 
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * 
     */
    public function conta()
    {
        return $this->belongsTo(Conta::class, 'conta_id');
    }
}
