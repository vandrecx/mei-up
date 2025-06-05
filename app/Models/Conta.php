<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nome',
        'tipo',
        'banco',
        'numero_conta',
        'saldo_atual',
        'limite',
        'data_fechamento',
        'data_vencimento',
        'ativo',
    ];

    protected $casts = [
        'saldo_atual' => 'decimal:2',
        'limite' => 'decimal:2',
        'ativo' => 'boolean',
        'data_fechamento' => 'integer',
        'data_vencimento' => 'integer',
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Enums
    public static function getTipos(): array
    {
        return [
            'conta_corrente' => 'Conta Corrente',
            'poupanca' => 'Poupança',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
        ];
    }

    // Accessors
    public function getTipoLabelAttribute(): string
    {
        return self::getTipos()[$this->tipo] ?? $this->tipo;
    }

    public function getStatusAttribute(): string
    {
        return $this->ativo ? 'Ativo' : 'Inativo';
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeCartoesCredito($query)
    {
        return $query->where('tipo', 'cartao_credito');
    }

    public function scopeContasBancarias($query)
    {
        return $query->whereIn('tipo', ['conta_corrente', 'poupanca']);
    }
}