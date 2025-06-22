<?php

namespace App\Filament\Widgets;

use App\Models\Parcelamento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ParcelamentosStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Filtra todos os dados pelo usuário autenticado
        $usuarioId = Auth::id();
        
        $totalParcelamentos = Parcelamento::where('usuario_id', $usuarioId)->count();
        $parcelamentosAtivos = Parcelamento::where('usuario_id', $usuarioId)->ativos()->count();
        $parcelamentosEmAndamento = Parcelamento::where('usuario_id', $usuarioId)->emAndamento()->count();
        $parcelamentosFinalizados = Parcelamento::where('usuario_id', $usuarioId)->finalizados()->count();
        
        $valorTotalParcelamentos = Parcelamento::where('usuario_id', $usuarioId)->ativos()->sum('valor_total');
        $valorTotalPago = Parcelamento::where('usuario_id', $usuarioId)->ativos()->get()->sum(function ($parcelamento) {
            return $parcelamento->parcelas_pagas * $parcelamento->valor_parcela;
        });
        $valorRestante = $valorTotalParcelamentos - $valorTotalPago;

        return [
            Stat::make('Total de Parcelamentos', $totalParcelamentos)
                ->description('Todos os seus parcelamentos')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make('Parcelamentos Ativos', $parcelamentosAtivos)
                ->description($parcelamentosEmAndamento . ' em andamento')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Parcelamentos Finalizados', $parcelamentosFinalizados)
                ->description('Completamente pagos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),

            Stat::make('Valor Total', 'R$ ' . number_format($valorTotalParcelamentos, 2, ',', '.'))
                ->description('Soma dos seus parcelamentos ativos')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Valor Pago', 'R$ ' . number_format($valorTotalPago, 2, ',', '.'))
                ->description('Total já pago por você')
                ->descriptionIcon('heroicon-m-check')
                ->color('primary'),

            Stat::make('Valor Restante', 'R$ ' . number_format($valorRestante, 2, ',', '.'))
                ->description('Ainda a pagar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}