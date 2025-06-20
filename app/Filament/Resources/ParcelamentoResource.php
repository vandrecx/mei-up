<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParcelamentoResource\Pages;
use App\Models\Parcelamento;
use App\Models\User;
use App\Models\Conta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Model;

class ParcelamentoResource extends Resource
{
    protected static ?string $model = Parcelamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Parcelamentos';

    protected static ?string $modelLabel = 'Parcelamento';

    protected static ?string $pluralModelLabel = 'Parcelamentos';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Select::make('usuario_id')
                            ->label('Usuário')
                            ->relationship('usuario', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('conta_id')
                            ->label('Conta')
                            ->relationship('conta', 'nome')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Valores e Parcelas')
                    ->schema([
                        Forms\Components\TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $valorTotal = floatval($state);
                                $totalParcelas = intval($get('total_parcelas'));
                                
                                if ($valorTotal > 0 && $totalParcelas > 0) {
                                    $valorParcela = $valorTotal / $totalParcelas;
                                    $set('valor_parcela', number_format($valorParcela, 2, '.', ''));
                                }
                            }),

                        Forms\Components\TextInput::make('total_parcelas')
                            ->label('Total de Parcelas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(999)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $valorTotal = floatval($get('valor_total'));
                                $totalParcelas = intval($state);
                                
                                if ($valorTotal > 0 && $totalParcelas > 0) {
                                    $valorParcela = $valorTotal / $totalParcelas;
                                    $set('valor_parcela', number_format($valorParcela, 2, '.', ''));
                                }
                            }),

                        Forms\Components\TextInput::make('valor_parcela')
                            ->label('Valor da Parcela')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->readOnly(),

                        Forms\Components\TextInput::make('parcelas_pagas')
                            ->label('Parcelas Pagas')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Datas e Configurações')
                    ->schema([
                        Forms\Components\DatePicker::make('data_primeira_parcela')
                            ->label('Data da Primeira Parcela')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('dia_vencimento')
                            ->label('Dia de Vencimento')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->helperText('Dia do mês para vencimento das parcelas'),

                        Forms\Components\Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Parcelamento ativo/inativo'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Parcelamento $record): string => $record->descricao),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progresso')
                    ->label('Progresso')
                    ->getStateUsing(fn (Parcelamento $record): string => 
                        "{$record->parcelas_pagas}/{$record->total_parcelas} ({$record->progresso}%)"
                    )
                    ->badge()
                    ->color(fn (Parcelamento $record): string => match (true) {
                        $record->progresso == 100 => 'success',
                        $record->progresso >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Finalizado' => 'success',
                        'Em andamento' => 'warning',
                        'Inativo' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('data_primeira_parcela')
                    ->label('Primeira Parcela')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                
                Tables\Filters\SelectFilter::make('usuario_id')
                    ->label('Usuário')
                    ->relationship('usuario', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('Criado a partir de'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Criado até'),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })->columnSpan(2),

                Tables\Filters\Filter::make('ativo')
                    ->label('Apenas Ativos')
                    ->query(fn (Builder $query): Builder => $query->where('ativo', true))
                    ->default(),

                Tables\Filters\Filter::make('em_andamento')
                    ->label('Em Andamento')
                    ->query(fn (Builder $query): Builder => $query->emAndamento()),

                Tables\Filters\Filter::make('finalizados')
                    ->label('Finalizados')
                    ->query(fn (Builder $query): Builder => $query->finalizados()),

                
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações Gerais')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID'),

                        Infolists\Components\TextEntry::make('usuario.name')
                            ->label('Usuário'),

                        Infolists\Components\TextEntry::make('conta.nome')
                            ->label('Conta')
                            ->placeholder('Nenhuma conta vinculada'),

                        Infolists\Components\TextEntry::make('descricao')
                            ->label('Descrição')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Valores e Parcelas')
                    ->schema([
                        Infolists\Components\TextEntry::make('valor_total')
                            ->label('Valor Total')
                            ->money('BRL')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('valor_parcela')
                            ->label('Valor da Parcela')
                            ->money('BRL'),

                        Infolists\Components\TextEntry::make('total_parcelas')
                            ->label('Total de Parcelas'),

                        Infolists\Components\TextEntry::make('parcelas_pagas')
                            ->label('Parcelas Pagas')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('progresso')
                            ->label('Progresso')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (Parcelamento $record): string => match (true) {
                                $record->progresso == 100 => 'success',
                                $record->progresso >= 50 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('valor_restante')
                            ->label('Valor Restante')
                            ->money('BRL')
                            ->color('warning'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Datas e Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('data_primeira_parcela')
                            ->label('Data da Primeira Parcela')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('dia_vencimento')
                            ->label('Dia de Vencimento')
                            ->suffix('º dia do mês'),

                        Infolists\Components\IconEntry::make('ativo')
                            ->label('Ativo')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Finalizado' => 'success',
                                'Em andamento' => 'warning',
                                'Inativo' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i:s'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(3),
            ]);
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
            'index' => Pages\ListParcelamentos::route('/'),
            'create' => Pages\CreateParcelamento::route('/create'),
            'view' => Pages\ViewParcelamento::route('/{record}'),
            'edit' => Pages\EditParcelamento::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['usuario', 'conta']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['descricao', 'usuario.name', 'conta.nome'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->usuario) {
            $details['Usuário'] = $record->usuario->name;
        }

        if ($record->conta) {
            $details['Conta'] = $record->conta->nome;
        }

        return $details;
    }
}