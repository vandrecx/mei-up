<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimentacaoMetaResource\Pages;
use App\Models\MovimentacaoMeta;
use App\Models\MetaFinanceira;
use App\Models\Transacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;

class MovimentacaoMetaResource extends Resource
{
    protected static ?string $model = MovimentacaoMeta::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimentações de Metas';

    protected static ?string $modelLabel = 'Movimentação';

    protected static ?string $pluralModelLabel = 'Movimentações de Metas';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Movimentação')
                    ->schema([
                        Forms\Components\Select::make('meta_id')
                            ->label('Meta Financeira')
                            ->relationship('meta', 'titulo')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (MetaFinanceira $record): string => 
                                "{$record->titulo} (R$ {$record->valor_atual}/{$record->valor_objetivo})"
                            ),

                        Forms\Components\Select::make('transacao_id')
                            ->label('Transação Relacionada')
                            ->relationship('transacao', 'descricao')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opcional: vincule a uma transação existente'),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo de Movimentação')
                            ->options([
                                'deposito' => 'Depósito',
                                'retirada' => 'Retirada',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $set('valor', null);
                            }),

                        Forms\Components\TextInput::make('valor')
                            ->label('Valor')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->minValue(0.01)
                            ->maxValue(function (Forms\Get $get) {
                                $metaId = $get('meta_id');
                                $tipo = $get('tipo');
                                
                                if ($tipo === 'retirada' && $metaId) {
                                    $meta = MetaFinanceira::find($metaId);
                                    return $meta ? $meta->valor_atual : null;
                                }
                                
                                return null;
                            })
                            ->helperText(function (Forms\Get $get) {
                                $metaId = $get('meta_id');
                                $tipo = $get('tipo');
                                
                                if ($tipo === 'retirada' && $metaId) {
                                    $meta = MetaFinanceira::find($metaId);
                                    if ($meta) {
                                        return "Valor disponível para retirada: R$ " . number_format($meta->valor_atual, 2, ',', '.');
                                    }
                                }
                                
                                return null;
                            }),

                        Forms\Components\DatePicker::make('data_movimentacao')
                            ->label('Data da Movimentação')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('meta.titulo')
                    ->label('Meta')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (MovimentacaoMeta $record): string => $record->meta->titulo ?? ''),

                Tables\Columns\TextColumn::make('tipo_label')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (MovimentacaoMeta $record): string => $record->tipo_badge_color),

                Tables\Columns\TextColumn::make('valor_formatado')
                    ->label('Valor')
                    ->weight(FontWeight::Bold)
                    ->color(fn (MovimentacaoMeta $record): string => 
                        $record->tipo === 'deposito' ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('data_movimentacao')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transacao.descricao')
                    ->label('Transação')
                    ->limit(20)
                    ->placeholder('Não vinculada')
                    ->tooltip(fn (MovimentacaoMeta $record): ?string => 
                        $record->transacao?->descricao
                    ),

                Tables\Columns\TextColumn::make('observacoes')
                    ->label('Observações')
                    ->limit(30)
                    ->placeholder('Sem observações')
                    ->tooltip(fn (MovimentacaoMeta $record): ?string => $record->observacoes),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                
                Tables\Filters\SelectFilter::make('meta_id')
                    ->label('Meta Financeira')
                    ->relationship('meta', 'titulo')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'deposito' => 'Depósito',
                        'retirada' => 'Retirada',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('data_movimentacao')
                    ->form([
                        Forms\Components\DatePicker::make('data_de')
                            ->label('Data de'),
                        Forms\Components\DatePicker::make('data_ate')
                            ->label('Data até'),
                    ])->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_de'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_movimentacao', '>=', $date),
                            )
                            ->when(
                                $data['data_ate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_movimentacao', '<=', $date),
                            );
                    })->columns(2), 
                    
                    Tables\Filters\Filter::make('valor_alto')
                        ->label('Valores > R$ 1.000')
                        ->query(fn (Builder $query): Builder => $query->where('valor', '>', 1000)),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
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
            ->defaultSort('data_movimentacao', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações da Movimentação')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta.titulo')
                            ->label('Meta Financeira')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('tipo_label')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn (MovimentacaoMeta $record): string => $record->tipo_badge_color),

                        Infolists\Components\TextEntry::make('valor')
                            ->label('Valor')
                            ->money('BRL')
                            ->weight(FontWeight::Bold)
                            ->color(fn (MovimentacaoMeta $record): string => 
                                $record->tipo === 'deposito' ? 'success' : 'danger'
                            ),

                        Infolists\Components\TextEntry::make('data_movimentacao')
                            ->label('Data da Movimentação')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('transacao.descricao')
                            ->label('Transação Relacionada')
                            ->placeholder('Não vinculada'),

                        Infolists\Components\TextEntry::make('observacoes')
                            ->label('Observações')
                            ->placeholder('Sem observações')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Informações da Meta')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta.valor_objetivo')
                            ->label('Valor Objetivo da Meta')
                            ->money('BRL'),

                        Infolists\Components\TextEntry::make('meta.valor_atual')
                            ->label('Valor Atual da Meta')
                            ->money('BRL'),

                        Infolists\Components\TextEntry::make('meta.progresso')
                            ->label('Progresso da Meta')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (MovimentacaoMeta $record): string => match (true) {
                                $record->meta->progresso >= 100 => 'success',
                                $record->meta->progresso >= 75 => 'primary',
                                $record->meta->progresso >= 50 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('meta.status_label')
                            ->label('Status da Meta')
                            ->badge()
                            ->color(fn (MovimentacaoMeta $record): string => $record->meta->status_badge_color),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListMovimentacaoMetas::route('/'),
            'create' => Pages\CreateMovimentacaoMeta::route('/create'),
            'view' => Pages\ViewMovimentacaoMeta::route('/{record}'),
            'edit' => Pages\EditMovimentacaoMeta::route('/{record}/edit'),
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
        return parent::getGlobalSearchEloquentQuery()->with(['meta', 'transacao']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['observacoes', 'meta.titulo', 'transacao.descricao'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->meta) {
            $details['Meta'] = $record->meta->titulo;
        }

        $details['Tipo'] = $record->tipo_label;
        $details['Valor'] = 'R$ ' . number_format($record->valor, 2, ',', '.');

        return $details;
    }
}