<?php

namespace App\Filament\Resources\ParcelamentoResource\Pages;

use App\Filament\Resources\ParcelamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParcelamento extends EditRecord
{
    protected static string $resource = ParcelamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Parcelamento atualizado com sucesso!';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalcula o valor da parcela para garantir precisão
        if (isset($data['valor_total']) && isset($data['total_parcelas']) && $data['total_parcelas'] > 0) {
            $data['valor_parcela'] = $data['valor_total'] / $data['total_parcelas'];
        }

        // Valida se parcelas pagas não excedem o total
        if (isset($data['parcelas_pagas']) && isset($data['total_parcelas'])) {
            if ($data['parcelas_pagas'] > $data['total_parcelas']) {
                $data['parcelas_pagas'] = $data['total_parcelas'];
            }
        }

        return $data;
    }
}