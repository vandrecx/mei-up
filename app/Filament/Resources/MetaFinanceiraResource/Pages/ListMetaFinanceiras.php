<?php

namespace App\Filament\Resources\MetaFinanceiraResource\Pages;

use App\Filament\Resources\MetaFinanceiraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMetaFinanceiras extends ListRecords
{
    protected static string $resource = MetaFinanceiraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
