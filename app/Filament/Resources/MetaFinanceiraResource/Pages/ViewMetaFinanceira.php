<?php

namespace App\Filament\Resources\MetaFinanceiraResource\Pages;

use App\Filament\Resources\MetaFinanceiraResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMetaFinanceira extends ViewRecord
{
    protected static string $resource = MetaFinanceiraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
