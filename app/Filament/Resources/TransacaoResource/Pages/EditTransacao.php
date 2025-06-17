<?php

namespace App\Filament\Resources\TransacaoResource\Pages;

use App\Filament\Resources\TransacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransacao extends EditRecord
{
    protected static string $resource = TransacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
