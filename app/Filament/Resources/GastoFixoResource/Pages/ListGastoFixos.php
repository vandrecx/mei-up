<?php

namespace App\Filament\Resources\GastoFixoResource\Pages;

use App\Filament\Resources\GastoFixoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGastoFixos extends ListRecords
{
    protected static string $resource = GastoFixoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
