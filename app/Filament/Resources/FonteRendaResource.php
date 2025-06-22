<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FonteRendaResource\Pages;
use App\Models\FonteRenda;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FonteRendaResource extends Resource
{
    protected static ?string $model = FonteRenda::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $navigationLabel = 'Fontes de Renda';
    protected static ?string $modelLabel = 'Fonte de Renda';
    protected static ?string $pluralModelLabel = 'Fontes de Renda';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('usuario_id')
                    ->default(fn () => Auth::id())
                    ->required(),

                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ex: Salário, Freelance, Investimentos'),

                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->required()
                    ->placeholder('0,00'),

                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'fixo'     => 'Fixo',
                        'variavel' => 'Variável',
                    ])
                    ->required()
                    ->native(false)
                    ->helperText('Fixo: valor constante (salário). Variável: valor que muda (freelance)'),

                DatePicker::make('data_recebimento')
                    ->label('Data de Recebimento')
                    ->nullable()
                    ->helperText('Quando você recebe esta renda mensalmente'),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->helperText('Desative se não está mais recebendo esta renda'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (FonteRenda $record): string => $record->descricao),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'fixo'     => 'Fixo',
                        'variavel' => 'Variável',
                        default    => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'fixo'     => 'primary',
                        'variavel' => 'success',
                        default    => 'gray',
                    }),

                TextColumn::make('data_recebimento')
                    ->label('Recebimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Não definido'),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'fixo'     => 'Fixo',
                        'variavel' => 'Variável',
                    ])
                    ->native(false),

                SelectFilter::make('ativo')
                    ->label('Status')
                    ->options([
                        1 => 'Ativo',
                        0 => 'Inativo',
                    ])
                    ->native(false),

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
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFonteRendas::route('/'),
            'create' => Pages\CreateFonteRenda::route('/create'),
            'edit'   => Pages\EditFonteRenda::route('/{record}/edit'),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('usuario_id', Auth::id())->where('ativo', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}