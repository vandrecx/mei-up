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

class TransacaoResource extends Resource
{
    protected static ?string $model = Transacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestão Financeira'; 
    protected static ?string $navigationLabel = 'Transações';
    protected static ?string $pluralModelLabel = 'Transações';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('usuario_id')
                    ->label('Usuário')
                    ->relationship('usuario', 'name') 
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan('full')
                    ->default(auth()->id()) 
                    ->disabledOn('edit'), 

                Forms\Components\Select::make('conta_id')
                    ->label('Conta')
                    ->relationship('conta', 'nome') 
                    ->placeholder('Nenhuma conta selecionada (opcional)')
                    ->searchable()
                    ->preload()
                    ->columnSpan('full')
                    ->nullable(),

                Forms\Components\TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(200)
                    ->columnSpan('full'),

                Forms\Components\TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->rules(['numeric', 'min:0.01']) 
                    ->required()
                    ->prefix('R$') 
                    ->columnSpan('full'),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                    ])
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\Select::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'alimentacao' => 'Alimentação',
                        'transporte' => 'Transporte',
                        'saude' => 'Saúde',
                        'lazer' => 'Lazer',
                        'trabalho' => 'Trabalho',
                        'outros' => 'Outros',
                    ])
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\Select::make('forma_pagamento')
                    ->label('Forma de Pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'debito' => 'Débito',
                        'credito' => 'Crédito',
                    ])
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\DatePicker::make('data_transacao')
                    ->label('Data da Transação')
                    ->native(false) 
                    ->required()
                    ->default(now()) 
                    ->columnSpan('full'),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->maxLength(65535) 
                    ->columnSpan('full')
                    ->nullable(),
            ])
            ->columns(2); 
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

                Tables\Columns\TextColumn::make('conta.nome')
                    ->label('Conta')
                    ->sortable()
                    ->searchable()
                    ->default('N/A'), 

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL') 
                    ->color(fn (string $state, $record): string => match ($record->tipo) {
                        'entrada' => 'success', 
                        'saida' => 'danger',    
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge() 
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'saida' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge() 
                    ->sortable(),

                Tables\Columns\TextColumn::make('forma_pagamento')
                    ->label('Pagamento')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_transacao')
                    ->label('Data')
                    ->date('d/m/Y') 
                    ->sortable(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deletado Em')
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
                    ->label('Filtrar por Tipo'),

                Tables\Filters\SelectFilter::make('categoria')
                    ->options([
                        'alimentacao' => 'Alimentação',
                        'transporte' => 'Transporte',
                        'saude' => 'Saúde',
                        'lazer' => 'Lazer',
                        'trabalho' => 'Trabalho',
                        'outros' => 'Outros',
                    ])
                    ->label('Filtrar por Categoria'),

                Tables\Filters\SelectFilter::make('forma_pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'PIX',
                        'debito' => 'Débito',
                        'credito' => 'Crédito',
                    ])
                    ->label('Filtrar por Forma de Pagamento'),

                Tables\Filters\TrashedFilter::make() 
                    ->label('Ver Deletados'),
            ])
            ->actions([
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            ]);
    }
}
