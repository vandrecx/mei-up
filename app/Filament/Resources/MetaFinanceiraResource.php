<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetaFinanceiraResource\Pages;
use App\Filament\Resources\MetaFinanceiraResource\RelationManagers\MovimentacoesRelationManager;
use App\Models\MetaFinanceira;
use App\Models\User;
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
use Filament\Support\Colors\Color;

class MetaFinanceiraResource extends Resource
{
    protected static ?string $model = MetaFinanceira::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Metas Financeiras';

    protected static ?string $modelLabel = 'Meta Financeira';

    protected static ?string $pluralModelLabel = 'Metas Financeiras';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 4;

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

                        Forms\Components\Select::make('categoria')
                            ->label('Categoria')
                            ->options([
                                'emergencia' => 'Emergência',
                                'investimento' => 'Investimento',
                                'compra' => 'Compra',
                                'viagem' => 'Viagem',
                                'outros' => 'Outros',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Valores e Metas')
                    ->schema([
                        Forms\Components\TextInput::make('valor_objetivo')
                            ->label('Valor Objetivo')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->minValue(0.01),

                        Forms\Components\TextInput::make('valor_atual')
                            ->label('Valor Atual')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Será atualizado automaticamente pelas movimentações'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Datas e Status')
                    ->schema([
                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\DatePicker::make('data_objetivo')
                            ->label('Data Objetivo')
                            ->required()
                            ->minDate(now())
                            ->after('data_inicio'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'ativo' => 'Ativo',
                                'pausado' => 'Pausado',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->default('ativo')
                            ->native(false),
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

                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (MetaFinanceira $record): string => $record->titulo),

                Tables\Columns\TextColumn::make('categoria_label')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (MetaFinanceira $record): string => $record->categoria_badge_color),

                Tables\Columns\TextColumn::make('valor_objetivo')
                    ->label('Objetivo')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_atual')
                    ->label('Atual')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progresso')
                    ->label('Progresso')
                    ->getStateUsing(fn (MetaFinanceira $record): string => 
                        number_format($record->progresso, 1) . '%'
                    )
                    ->badge()
                    ->color(fn (MetaFinanceira $record): string => match (true) {
                        $record->progresso >= 100 => 'success',
                        $record->progresso >= 75 => 'primary',
                        $record->progresso >= 50 => 'warning',
                        $record->progresso >= 25 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('progresso')
                    ->label('Progresso')
                    ->getStateUsing(fn (MetaFinanceira $record): string => 
                        number_format($record->progresso, 1) . '%'
                    )
                    ->badge()
                    ->color(fn (MetaFinanceira $record): string => match (true) {
                        $record->progresso >= 100 => 'success',
                        $record->progresso >= 75 => 'primary',
                        $record->progresso >= 50 => 'warning',
                        $record->progresso >= 25 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (MetaFinanceira $record): string => $record->status_badge_color),

                Tables\Columns\TextColumn::make('data_objetivo')
                    ->label('Data Objetivo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (MetaFinanceira $record): string => 
                        $record->data_objetivo->isPast() && $record->status === 'ativo' ? 'danger' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Dias Restantes')
                    ->getStateUsing(fn (MetaFinanceira $record): string => 
                        $record->dias_restantes > 0 ? $record->dias_restantes . ' dias' : 'Vencida'
                    )
                    ->color(fn (MetaFinanceira $record): string => match (true) {
                        $record->dias_restantes <= 0 => 'danger',
                        $record->dias_restantes <= 7 => 'warning',
                        $record->dias_restantes <= 30 => 'info',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
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

                Tables\Filters\SelectFilter::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'emergencia' => 'Emergência',
                        'investimento' => 'Investimento',
                        'compra' => 'Compra',
                        'viagem' => 'Viagem',
                        'outros' => 'Outros',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ativo' => 'Ativo',
                        'pausado' => 'Pausado',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('vencendo')
                    ->label('Vencendo (30 dias)')
                    ->query(fn (Builder $query): Builder => $query->vencendo(30)),

                Tables\Filters\Filter::make('vencidas')
                    ->label('Vencidas')
                    ->query(fn (Builder $query): Builder => $query->vencidas()),

                Tables\Filters\Filter::make('progresso_alto')
                    ->label('Progresso > 50%')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('(valor_atual / valor_objetivo) * 100 > 50');
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('adicionar_valor')
                        ->label('Adicionar Valor')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->visible(fn (MetaFinanceira $record): bool => 
                            in_array($record->status, ['ativo', 'pausado'])
                        )
                        ->form([
                            Forms\Components\TextInput::make('valor')
                                ->label('Valor')
                                ->required()
                                ->numeric()
                                ->prefix('R$')
                                ->step(0.01)
                                ->minValue(0.01),
                            
                            Forms\Components\DatePicker::make('data_movimentacao')
                                ->label('Data da Movimentação')
                                ->required()
                                ->default(now())
                                ->maxDate(now()),
                            
                            Forms\Components\Textarea::make('observacoes')
                                ->label('Observações')
                                ->rows(2),
                        ])
                        ->action(function (MetaFinanceira $record, array $data): void {
                            $record->movimentacoes()->create([
                                'valor' => $data['valor'],
                                'tipo' => 'deposito',
                                'data_movimentacao' => $data['data_movimentacao'],
                                'observacoes' => $data['observacoes'] ?? null,
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Valor adicionado com sucesso!')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\Action::make('retirar_valor')
                        ->label('Retirar Valor')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->visible(fn (MetaFinanceira $record): bool => 
                            in_array($record->status, ['ativo', 'pausado']) && $record->valor_atual > 0
                        )
                        ->form([
                            Forms\Components\TextInput::make('valor')
                                ->label('Valor')
                                ->required()
                                ->numeric()
                                ->prefix('R$')
                                ->step(0.01)
                                ->minValue(0.01)
                                ->maxValue(fn (MetaFinanceira $record): float => $record->valor_atual),
                            
                            Forms\Components\DatePicker::make('data_movimentacao')
                                ->label('Data da Movimentação')
                                ->required()
                                ->default(now())
                                ->maxDate(now()),
                            
                            Forms\Components\Textarea::make('observacoes')
                                ->label('Observações')
                                ->rows(2),
                        ])
                        ->action(function (MetaFinanceira $record, array $data): void {
                            $record->movimentacoes()->create([
                                'valor' => $data['valor'],
                                'tipo' => 'retirada',
                                'data_movimentacao' => $data['data_movimentacao'],
                                'observacoes' => $data['observacoes'] ?? null,
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Valor retirado com sucesso!')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
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
                        Infolists\Components\TextEntry::make('titulo')
                            ->label('Título')
                            ->weight(FontWeight::Bold),

                        Infolists\Components\TextEntry::make('usuario.name')
                            ->label('Usuário'),

                        Infolists\Components\TextEntry::make('categoria_label')
                            ->label('Categoria')
                            ->badge()
                            ->color(fn (MetaFinanceira $record): string => $record->categoria_badge_color),

                        Infolists\Components\TextEntry::make('status_label')
                            ->label('Status')
                            ->badge()
                            ->color(fn (MetaFinanceira $record): string => $record->status_badge_color),

                        Infolists\Components\TextEntry::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Nenhuma descrição')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Valores e Progresso')
                    ->schema([
                        Infolists\Components\TextEntry::make('valor_objetivo')
                            ->label('Valor Objetivo')
                            ->money('BRL')
                            ->weight(FontWeight::Bold)
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('valor_atual')
                            ->label('Valor Atual')
                            ->money('BRL')
                            ->weight(FontWeight::Bold)
                            ->color('success'),

                        Infolists\Components\TextEntry::make('valor_restante')
                            ->label('Valor Restante')
                            ->money('BRL')
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('progresso')
                            ->label('Progresso')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (MetaFinanceira $record): string => match (true) {
                                $record->progresso >= 100 => 'success',
                                $record->progresso >= 75 => 'primary',
                                $record->progresso >= 50 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('progresso_status')
                            ->label('Status do Progresso')
                            ->badge(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Datas e Prazos')
                    ->schema([
                        Infolists\Components\TextEntry::make('data_inicio')
                            ->label('Data de Início')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('data_objetivo')
                            ->label('Data Objetivo')
                            ->date('d/m/Y')
                            ->color(fn (MetaFinanceira $record): string => 
                                $record->data_objetivo->isPast() && $record->status === 'ativo' ? 'danger' : 'gray'
                            ),

                        Infolists\Components\TextEntry::make('dias_decorridosk')
                            ->label('Dias Decorridos')
                            ->suffix(' dias'),

                        Infolists\Components\TextEntry::make('dias_restantes')
                            ->label('Dias Restantes')
                            ->getStateUsing(fn (MetaFinanceira $record): string => 
                                $record->dias_restantes > 0 ? $record->dias_restantes . ' dias' : 'Vencida'
                            )
                            ->color(fn (MetaFinanceira $record): string => match (true) {
                                $record->dias_restantes <= 0 => 'danger',
                                $record->dias_restantes <= 7 => 'warning',
                                $record->dias_restantes <= 30 => 'info',
                                default => 'success',
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         MovimentacoesRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetaFinanceiras::route('/'),
            'create' => Pages\CreateMetaFinanceira::route('/create'),
            'view' => Pages\ViewMetaFinanceira::route('/{record}'),
            'edit' => Pages\EditMetaFinanceira::route('/{record}/edit'),
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
        return parent::getGlobalSearchEloquentQuery()->with(['usuario']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['titulo', 'descricao', 'usuario.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->usuario) {
            $details['Usuário'] = $record->usuario->name;
        }

        $details['Categoria'] = $record->categoria_label;
        $details['Progresso'] = number_format($record->progresso, 1) . '%';

        return $details;
    }
}