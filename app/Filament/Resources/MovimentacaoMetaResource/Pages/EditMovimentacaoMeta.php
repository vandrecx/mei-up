<?php

namespace App\Filament\Resources\MovimentacaoMetaResource\Pages;

use App\Filament\Resources\MovimentacaoMetaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimentacaoMeta extends EditRecord
{
    protected static string $resource = MovimentacaoMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
