<?php

namespace App\Filament\Resources\TransacaoResource\Pages;

use App\Filament\Resources\TransacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransacaos extends ListRecords
{
    protected static string $resource = TransacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
