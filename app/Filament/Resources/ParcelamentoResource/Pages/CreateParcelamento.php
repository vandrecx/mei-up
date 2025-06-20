<?php

namespace App\Filament\Resources\ParcelamentoResource\Pages;

use App\Filament\Resources\ParcelamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParcelamento extends CreateRecord
{
    protected static string $resource = ParcelamentoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Parcelamento criado com sucesso!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Recalcula o valor da parcela para garantir precisão
        if (isset($data['valor_total']) && isset($data['total_parcelas']) && $data['total_parcelas'] > 0) {
            $data['valor_parcela'] = $data['valor_total'] / $data['total_parcelas'];
        }

        return $data;
    }
}
