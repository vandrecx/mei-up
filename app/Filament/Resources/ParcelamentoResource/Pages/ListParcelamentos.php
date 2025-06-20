<?php

namespace App\Filament\Resources\ParcelamentoResource\Pages;

use App\Filament\Resources\ParcelamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListParcelamentos extends ListRecords
{
    protected static string $resource = ParcelamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            
            'ativos' => Tab::make('Ativos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ativo', true)),
            
            'em_andamento' => Tab::make('Em Andamento')
                ->modifyQueryUsing(fn (Builder $query) => $query->emAndamento()),
            
            'finalizados' => Tab::make('Finalizados')
                ->modifyQueryUsing(fn (Builder $query) => $query->finalizados()),
            
            'inativos' => Tab::make('Inativos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ativo', false)),
        ];
    }
}
