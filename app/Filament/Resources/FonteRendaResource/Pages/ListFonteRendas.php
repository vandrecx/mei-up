<?php

namespace App\Filament\Resources\FonteRendaResource\Pages;

use App\Filament\Resources\FonteRendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFonteRendas extends ListRecords
{
    protected static string $resource = FonteRendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
