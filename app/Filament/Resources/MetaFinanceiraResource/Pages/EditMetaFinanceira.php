<?php

namespace App\Filament\Resources\MetaFinanceiraResource\Pages;

use App\Filament\Resources\MetaFinanceiraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMetaFinanceira extends EditRecord
{
    protected static string $resource = MetaFinanceiraResource::class;

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
