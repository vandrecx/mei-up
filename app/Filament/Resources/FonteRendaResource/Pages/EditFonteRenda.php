<?php

namespace App\Filament\Resources\FonteRendaResource\Pages;

use App\Filament\Resources\FonteRendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFonteRenda extends EditRecord
{
    protected static string $resource = FonteRendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
