<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoMeta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacao_metas';

    protected $fillable = [
        'meta_id',
        'transacao_id',
        'valor',
        'tipo',
        'data_movimentacao',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'date',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function meta(): BelongsTo
    {
        return $this->belongsTo(MetaFinanceira::class, 'meta_id');
    }

    public function transacao(): BelongsTo
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }

    // Accessors
    public function getTipoBadgeColorAttribute(): string
    {
        return match ($this->tipo) {
            'deposito' => 'success',
            'retirada' => 'danger',
            default => 'gray',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'deposito' => 'Depósito',
            'retirada' => 'Retirada',
            default => 'N/A',
        };
    }

    public function getValorFormatadoAttribute(): string
    {
        $sinal = $this->tipo === 'deposito' ? '+' : '-';
        return $sinal . ' R$ ' . number_format($this->valor, 2, ',', '.');
    }

    // Scopes
    public function scopeDepositos($query)
    {
        return $query->where('tipo', 'deposito');
    }

    public function scopeRetiradas($query)
    {
        return $query->where('tipo', 'retirada');
    }

    public function scopePorMeta($query, $metaId)
    {
        return $query->where('meta_id', $metaId);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_movimentacao', [$dataInicio, $dataFim]);
    }

    // Boot method para atualizar valor_atual da meta automaticamente
    protected static function boot()
    {
        parent::boot();

        static::created(function ($movimentacao) {
            $movimentacao->atualizarValorMeta('created');
        });

        static::updated(function ($movimentacao) {
            $movimentacao->atualizarValorMeta('updated');
        });

        static::deleted(function ($movimentacao) {
            $movimentacao->atualizarValorMeta('deleted');
        });

        static::restored(function ($movimentacao) {
            $movimentacao->atualizarValorMeta('restored');
        });
    }

    // Método para atualizar o valor atual da meta
    private function atualizarValorMeta(string $acao = 'created'): void
    {
        $meta = $this->meta;
        
        if ($meta) {
            // Para a primeira movimentação, preserva o valor_atual como base
            $totalMovimentacoes = $meta->movimentacoes()->count();
            
            if ($acao === 'created' && $totalMovimentacoes === 1) {
                // Primeira movimentação: soma/subtrai do valor atual existente
                if ($this->tipo === 'deposito') {
                    $novoValor = $meta->valor_atual + $this->valor;
                } else {
                    $novoValor = $meta->valor_atual - $this->valor;
                }
            } else {
                // Recalcula baseado no valor sem esta movimentação + todas as movimentações
                $valorBase = $meta->valor_atual;
                
                // Remove o efeito da movimentação atual do valor base
                if ($acao === 'updated' || $acao === 'deleted') {
                    if ($this->tipo === 'deposito') {
                        $valorBase -= $this->valor;
                    } else {
                        $valorBase += $this->valor;
                    }
                }
                
                $totalDepositos = $meta->movimentacoes()->depositos()->sum('valor');
                $totalRetiradas = $meta->movimentacoes()->retiradas()->sum('valor');
                
                $novoValor = $valorBase + $totalDepositos - $totalRetiradas;
            }
            
            $meta->update(['valor_atual' => max(0, $novoValor)]);
        }
    }
}