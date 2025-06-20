<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContaResource\Pages;
use App\Models\Conta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class ContaResource extends Resource
{
    protected static ?string $model = Conta::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Contas';

    protected static ?string $modelLabel = 'Conta';

    protected static ?string $pluralModelLabel = 'Contas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Conta')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('nome')
                                    ->label('Nome da Conta')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ex: Conta Corrente Banco do Brasil'),

                                Select::make('tipo')
                                    ->label('Tipo de Conta')
                                    ->options(Conta::getTipos())
                                    ->required()
                                    ->native(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('banco')
                                    ->label('Banco')
                                    ->maxLength(50)
                                    ->placeholder('Ex: Banco do Brasil'),

                                TextInput::make('numero_conta')
                                    ->label('Número da Conta')
                                    ->maxLength(20)
                                    ->placeholder('Ex: 12345-6'),
                            ]),
                    ]),

                Forms\Components\Section::make('Informações Financeiras')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('saldo_atual')
                                    ->label('Saldo Atual')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->step(0.01)
                                    ->default(0)
                                    ->placeholder('0,00'),

                                TextInput::make('limite')
                                    ->label('Limite')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->step(0.01)
                                    ->placeholder('0,00')
                                    ->helperText('Apenas para cartões de crédito'),
                            ]),
                    ])
                    ->visible(fn (Forms\Get $get) => in_array($get('tipo'), ['conta_corrente', 'poupanca', 'cartao_credito'])),

                Forms\Components\Section::make('Datas de Vencimento')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('data_fechamento')
                                    ->label('Dia do Fechamento')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(31)
                                    ->placeholder('Ex: 10'),

                                TextInput::make('data_vencimento')
                                    ->label('Dia do Vencimento')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(31)
                                    ->placeholder('Ex: 15'),
                            ]),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('tipo') === 'cartao_credito'),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Toggle::make('ativo')
                            ->label('Conta Ativa')
                            ->default(true)
                            ->helperText('Desative para ocultar a conta sem excluir'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Conta::getTipos()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'conta_corrente' => 'primary',
                        'poupanca' => 'success',
                        'cartao_credito' => 'warning',
                        'cartao_debito' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('banco')
                    ->label('Banco')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('saldo_atual')
                    ->label('Saldo Atual')
                    ->money('BRL')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                TextColumn::make('limite')
                    ->label('Limite')
                    ->money('BRL')
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('ativo')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ativo' : 'Inativo')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de Conta')
                    ->options(Conta::getTipos()),

                SelectFilter::make('ativo')
                    ->label('Status')
                    ->options([
                        1 => 'Ativo',
                        0 => 'Inativo',
                    ]),

                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContas::route('/'),
            'create' => Pages\CreateConta::route('/create'),
            'view' => Pages\ViewConta::route('/{record}'),
            'edit' => Pages\EditConta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
