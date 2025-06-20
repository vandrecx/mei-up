<?php

namespace App\Observers;

use App\Models\Parcelamento;
use Illuminate\Support\Facades\Log;

class ParcelamentoObserver
{
    /**
     * Handle the Parcelamento "creating" event.
     */
    public function creating(Parcelamento $parcelamento): void
    {
        // Recalcula o valor da parcela baseado no valor total e número de parcelas
        if ($parcelamento->valor_total && $parcelamento->total_parcelas) {
            $parcelamento->valor_parcela = $parcelamento->valor_total / $parcelamento->total_parcelas;
        }

        // Garante que parcelas_pagas não seja maior que total_parcelas
        if ($parcelamento->parcelas_pagas > $parcelamento->total_parcelas) {
            $parcelamento->parcelas_pagas = $parcelamento->total_parcelas;
        }
    }

    /**
     * Handle the Parcelamento "created" event.
     */
    public function created(Parcelamento $parcelamento): void
    {
        Log::info('Parcelamento criado', [
            'id' => $parcelamento->id,
            'usuario_id' => $parcelamento->usuario_id,
            'descricao' => $parcelamento->descricao,
            'valor_total' => $parcelamento->valor_total,
        ]);
    }

    /**
     * Handle the Parcelamento "updating" event.
     */
    public function updating(Parcelamento $parcelamento): void
    {
        // Recalcula o valor da parcela se valor total ou número de parcelas mudaram
        if ($parcelamento->isDirty(['valor_total', 'total_parcelas'])) {
            if ($parcelamento->valor_total && $parcelamento->total_parcelas) {
                $parcelamento->valor_parcela = $parcelamento->valor_total / $parcelamento->total_parcelas;
            }
        }

        // Garante que parcelas_pagas não seja maior que total_parcelas
        if ($parcelamento->parcelas_pagas > $parcelamento->total_parcelas) {
            $parcelamento->parcelas_pagas = $parcelamento->total_parcelas;
        }

        // Se todas as parcelas foram pagas, pode inativar automaticamente (opcional)
        if ($parcelamento->parcelas_pagas >= $parcelamento->total_parcelas) {
            // Uncomment a linha abaixo se quiser inativar automaticamente quando finalizado
            // $parcelamento->ativo = false;
        }
    }

    /**
     * Handle the Parcelamento "updated" event.
     */
    public function updated(Parcelamento $parcelamento): void
    {
        // Log das mudanças importantes
        if ($parcelamento->wasChanged('parcelas_pagas')) {
            Log::info('Parcelas pagas atualizadas', [
                'id' => $parcelamento->id,
                'parcelas_pagas_anterior' => $parcelamento->getOriginal('parcelas_pagas'),
                'parcelas_pagas_atual' => $parcelamento->parcelas_pagas,
                'total_parcelas' => $parcelamento->total_parcelas,
            ]);
        }

        if ($parcelamento->wasChanged('ativo')) {
            $status = $parcelamento->ativo ? 'ativado' : 'inativado';
            Log::info("Parcelamento {$status}", [
                'id' => $parcelamento->id,
                'descricao' => $parcelamento->descricao,
            ]);
        }
    }

    /**
     * Handle the Parcelamento "deleted" event.
     */
    public function deleted(Parcelamento $parcelamento): void
    {
        Log::info('Parcelamento excluído (soft delete)', [
            'id' => $parcelamento->id,
            'descricao' => $parcelamento->descricao,
            'valor_total' => $parcelamento->valor_total,
        ]);
    }

    /**
     * Handle the Parcelamento "restored" event.
     */
    public function restored(Parcelamento $parcelamento): void
    {
        Log::info('Parcelamento restaurado', [
            'id' => $parcelamento->id,
            'descricao' => $parcelamento->descricao,
        ]);
    }

    /**
     * Handle the Parcelamento "force deleted" event.
     */
    public function forceDeleted(Parcelamento $parcelamento): void
    {
        Log::info('Parcelamento excluído permanentemente', [
            'id' => $parcelamento->id,
            'descricao' => $parcelamento->descricao,
            'valor_total' => $parcelamento->valor_total,
        ]);
    }
}