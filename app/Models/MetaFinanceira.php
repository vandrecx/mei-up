<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaFinanceira extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meta_financeiras';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descricao',
        'valor_objetivo',
        'valor_atual',
        'data_inicio',
        'data_objetivo',
        'categoria',
        'status',
    ];

    protected $casts = [
        'valor_objetivo' => 'decimal:2',
        'valor_atual' => 'decimal:2',
        'data_inicio' => 'date',
        'data_objetivo' => 'date',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoMeta::class, 'meta_id');
    }

    // Accessors
    public function getProgressoAttribute(): float
    {
        if ($this->valor_objetivo <= 0) {
            return 0;
        }

        return round(($this->valor_atual / $this->valor_objetivo) * 100, 2);
    }

    public function getProgressoStatusAttribute(): string
    {
        $progresso = $this->progresso;

        return match (true) {
            $progresso >= 100 => 'Concluída',
            $progresso >= 75 => 'Quase lá',
            $progresso >= 50 => 'No meio do caminho',
            $progresso >= 25 => 'Progredindo',
            default => 'Começando',
        };
    }

    public function getValorRestanteAttribute(): float
    {
        return max(0, $this->valor_objetivo - $this->valor_atual);
    }

    public function getDiasRestantesAttribute(): int
    {
        if ($this->data_objetivo->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->data_objetivo);
    }

    public function getDiasDecorridosAttribute(): int
    {
        return $this->data_inicio->diffInDays(now());
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'ativo' => 'success',
            'pausado' => 'warning',
            'concluido' => 'primary',
            'cancelado' => 'danger',
            default => 'gray',
        };
    }

    public function getCategoriaBadgeColorAttribute(): string
    {
        return match ($this->categoria) {
            'emergencia' => 'danger',
            'investimento' => 'success',
            'compra' => 'primary',
            'viagem' => 'warning',
            'outros' => 'gray',
            default => 'gray',
        };
    }

    public function getCategoriaLabelAttribute(): string
    {
        return match ($this->categoria) {
            'emergencia' => 'Emergência',
            'investimento' => 'Investimento',
            'compra' => 'Compra',
            'viagem' => 'Viagem',
            'outros' => 'Outros',
            default => 'N/A',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'ativo' => 'Ativo',
            'pausado' => 'Pausado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            default => 'N/A',
        };
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeVencendo($query, $dias = 30)
    {
        return $query->where('data_objetivo', '<=', now()->addDays($dias))
                    ->where('status', 'ativo');
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_objetivo', '<', now())
                    ->where('status', 'ativo');
    }
}