<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinResource\Pages;
use App\Models\Checkin;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Relatórios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->label('Usuário')
                    ->required(),

                DatePicker::make('data_checkin')
                    ->label('Data do Check-in')
                    ->required(),

                Select::make('humor_financeiro')
                    ->label('Humor Financeiro')
                    ->options([
                        'otimo'   => 'Ótimo',
                        'bom'     => 'Bom',
                        'neutro'  => 'Neutro',
                        'ruim'    => 'Ruim',
                        'pessimo' => 'Péssimo',
                    ])
                    ->required(),

                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3),

                Textarea::make('objetivos_alcancados')
                    ->label('Objetivos Alcançados')
                    ->rows(3),

                Textarea::make('dificuldades')
                    ->label('Dificuldades')
                    ->rows(3),

                Textarea::make('proximos_passos')
                    ->label('Próximos Passos')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->searchable(),
                TextColumn::make('data_checkin')
                    ->date()
                    ->label('Data'),
                TextColumn::make('humor_financeiro')
                    ->label('Humor')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'otimo' => 'Ótimo',
                        'bom' => 'Bom',
                        'neutro' => 'Neutro',
                        'ruim' => 'Ruim',
                        'pessimo' => 'Péssimo',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'otimo' => 'success',
                        'bom' => 'primary',
                        'neutro' => 'secondary',
                        'ruim' => 'warning',
                        'pessimo' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Criado em'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCheckins::route('/'),
            'create' => Pages\CreateCheckin::route('/create'),
            'edit'   => Pages\EditCheckin::route('/{record}/edit'),
        ];
    }
}
