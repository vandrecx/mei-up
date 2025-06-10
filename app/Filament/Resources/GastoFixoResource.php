<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GastoFixoResource\Pages;
use App\Models\GastoFixo;
use Filament\Forms\Form;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;

class GastoFixoResource extends Resource
{
    protected static ?string $model = GastoFixo::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financeiro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->label('Usuário')
                    ->required(),

                BelongsToSelect::make('conta_id')
                    ->relationship('conta', 'nome')
                    ->label('Conta'),

                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(100),

                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->required(),

                Select::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'moradia'    => 'Moradia',
                        'utilidades' => 'Utilidades',
                        'transporte' => 'Transporte',
                        'outros'     => 'Outros',
                    ])
                    ->required(),

                TextInput::make('dia_vencimento')
                    ->label('Dia de Vencimento')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required(),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true),
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

                TextColumn::make('conta.nome')
                    ->label('Conta')
                    ->searchable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL', 1, 'pt_BR')
                    ->sortable(),

                TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'moradia'    => 'Moradia',
                        'utilidades' => 'Utilidades',
                        'transporte' => 'Transporte',
                        'outros'     => 'Outros',
                        default      => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'moradia'    => 'primary',
                        'utilidades' => 'warning',
                        'transporte' => 'success',
                        'outros'     => 'secondary',
                        default      => 'secondary',
                    }),

                TextColumn::make('dia_vencimento')
                    ->label('Vencimento')
                    ->sortable(),

                BooleanColumn::make('ativo')
                    ->label('Ativo')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable(),
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
            'index'  => Pages\ListGastoFixos::route('/'),
            'create' => Pages\CreateGastoFixo::route('/create'),
            'edit'   => Pages\EditGastoFixo::route('/{record}/edit'),
        ];
    }
}
