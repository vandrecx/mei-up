<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcelamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parcelamentos';

    protected $fillable = [
        'usuario_id',
        'conta_id',
        'descricao',
        'valor_total',
        'valor_parcela',
        'total_parcelas',
        'parcelas_pagas',
        'data_primeira_parcela',
        'dia_vencimento',
        'ativo',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'data_primeira_parcela' => 'date',
        'ativo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class, 'conta_id');
    }

    // Accessors
    public function getStatusAttribute(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }

        if ($this->parcelas_pagas >= $this->total_parcelas) {
            return 'Finalizado';
        }

        return 'Em andamento';
    }

    public function getProgressoAttribute(): float
    {
        if ($this->total_parcelas == 0) {
            return 0;
        }

        return round(($this->parcelas_pagas / $this->total_parcelas) * 100, 2);
    }

    public function getValorRestanteAttribute(): float
    {
        $parcelasRestantes = $this->total_parcelas - $this->parcelas_pagas;
        return $parcelasRestantes * $this->valor_parcela;
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('ativo', true)
                    ->whereColumn('parcelas_pagas', '<', 'total_parcelas');
    }

    public function scopeFinalizados($query)
    {
        return $query->whereColumn('parcelas_pagas', '>=', 'total_parcelas');
    }
}
