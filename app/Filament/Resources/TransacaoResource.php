<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransacaoResource\Pages;
use App\Filament\Resources\TransacaoResource\RelationManagers;
use App\Models\Transacao;
use App\Models\User; 
use App\Models\Conta; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Model;

class TransacaoResource extends Resource
{
    protected static ?string $model = Transacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financeiro'; 
    protected static ?string $navigationLabel = 'Transações';
    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Hidden::make('usuario_id')
                            ->default(fn () => Auth::id())
                            ->required(),

                        Forms\Components\Select::make('conta_id')
                            ->label('Conta')
                            ->relationship(
                                'conta', 
                                'nome',
                                fn (Builder $query) => $query->where('user_id', Auth::id())->where('ativo', true)
                            )
                            ->placeholder('Nenhuma conta selecionada (opcional)')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo de Transação')
                            ->options([
                                'entrada' => 'Entrada',
                                'saida' => 'Saída',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Ex: Compra no supermercado, Salário, Combustível')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Valores e Categorias')
                    ->schema([
                        Forms\Components\TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->rules(['numeric', 'min:0.01']) 
                            ->required()
                            ->prefix('R$')
                            ->step(0.01)
                            ->placeholder('0,00'),

                        Forms\Components\Select::make('categoria')
                            ->label('Categoria')
                            ->options([
                                'alimentacao' => 'Alimentação',
                                'transporte' => 'Transporte',
                                'saude' => 'Saúde',
                                'lazer' => 'Lazer',
                                'educacao' => 'Educação',
                                'moradia' => 'Moradia',
                                'trabalho' => 'Trabalho',
                                'investimento' => 'Investimento',
                                'outros' => 'Outros',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('forma_pagamento')
                            ->label('Forma de Pagamento')
                            ->options([
                                'dinheiro' => 'Dinheiro',
                                'pix' => 'PIX',
                                'debito' => 'Cartão de Débito',
                                'credito' => 'Cartão de Crédito',
                                'transferencia' => 'Transferência',
                                'boleto' => 'Boleto',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('data_transacao')
                            ->label('Data da Transação')
                            ->native(false) 
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->maxLength(65535) 
                            ->rows(3)
                            ->placeholder('Informações adicionais sobre a transação')
                            ->nullable(),
                    ])
                    ->collapsible(),
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

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Transacao $record): string => $record->descricao),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL') 
                    ->color(fn (string $state, $record): string => match ($record->tipo) {
                        'entrada' => 'success', 
                        'saida' => 'danger',    
                        default => 'gray',
                    })
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge() 
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'saida' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge() 
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'alimentacao' => 'Alimentação',
                        'transporte' => 'Transporte',
                        'saude' => 'Saúde',
                        'lazer' => 'Lazer',
                        'educacao' => 'Educação',
                        'moradia' => 'Moradia',
                        'trabalho' => 'Trabalho',
                        'investimento' => 'Investimento',
                        'outros' => 'Outros',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'alimentacao' => 'orange',
                        'transporte' => 'blue',
                        'saude' => 'red',
                        'lazer' => 'purple',
                        'educacao' => 'green',
                        'moradia' => 'yellow',
                        'trabalho' => 'indigo',
                        'investimento' => 'emerald',
                        'outros' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('forma_pagamento')
                    ->label('Pagamento')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'debito' => 'Débito',
                        'credito' => 'Crédito',
                        'transferencia' => 'Transferência',
                        'boleto' => 'Boleto',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('conta.nome')
                    ->label('Conta')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Sem conta')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('data_transacao')
                    ->label('Data')
                    ->date('d/m/Y') 
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deletado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                    ])
                    ->label('Tipo')
                    ->native(false),

                Tables\Filters\SelectFilter::make('categoria')
                    ->options([
                        'alimentacao' => 'Alimentação',
                        'transporte' => 'Transporte',
                        'saude' => 'Saúde',
                        'lazer' => 'Lazer',
                        'educacao' => 'Educação',
                        'moradia' => 'Moradia',
                        'trabalho' => 'Trabalho',
                        'investimento' => 'Investimento',
                        'outros' => 'Outros',
                    ])
                    ->label('Categoria')
                    ->native(false),

                Tables\Filters\SelectFilter::make('forma_pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'debito' => 'Cartão de Débito',
                        'credito' => 'Cartão de Crédito',
                        'transferencia' => 'Transferência',
                        'boleto' => 'Boleto',
                    ])
                    ->label('Forma de Pagamento')
                    ->native(false),

                Tables\Filters\SelectFilter::make('conta_id')
                    ->label('Conta')
                    ->relationship(
                        'conta', 
                        'nome',
                        fn (Builder $query) => $query->where('user_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('data_transacao')
                    ->form([
                        Forms\Components\DatePicker::make('data_de')
                            ->label('Data de'),
                        Forms\Components\DatePicker::make('data_ate')
                            ->label('Data até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_de'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_transacao', '>=', $date),
                            )
                            ->when(
                                $data['data_ate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_transacao', '<=', $date),
                            );
                    })
                    ->columns(2),

                Tables\Filters\Filter::make('valor_alto')
                    ->label('Valores > R$ 1.000')
                    ->query(fn (Builder $query): Builder => $query->where('valor', '>', 1000)),

                Tables\Filters\TrashedFilter::make()
                    ->label('Ver Deletados'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(), 
                    Tables\Actions\RestoreAction::make(), 
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('data_transacao', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListTransacaos::route('/'),
            'create' => Pages\CreateTransacao::route('/create'),
            'edit' => Pages\EditTransacao::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('usuario_id', Auth::id());
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->where('usuario_id', Auth::id());
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['descricao', 'observacoes', 'conta.nome'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        $details['Tipo'] = $record->tipo === 'entrada' ? 'Entrada' : 'Saída';
        $details['Valor'] = 'R$ ' . number_format($record->valor, 2, ',', '.');
        
        if ($record->conta) {
            $details['Conta'] = $record->conta->nome;
        }

        return $details;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('usuario_id', Auth::id())->whereDate('data_transacao', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}