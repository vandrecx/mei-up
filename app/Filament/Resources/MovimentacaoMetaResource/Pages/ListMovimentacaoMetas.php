<?php

namespace App\Filament\Resources\MovimentacaoMetaResource\Pages;

use App\Filament\Resources\MovimentacaoMetaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimentacaoMetas extends ListRecords
{
    protected static string $resource = MovimentacaoMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
