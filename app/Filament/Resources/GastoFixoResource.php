<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GastoFixoResource\Pages;
use App\Models\GastoFixo;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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

class GastoFixoResource extends Resource
{
    protected static ?string $model = GastoFixo::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $navigationLabel = 'Gastos Fixos';
    protected static ?string $modelLabel = 'Gasto Fixo';
    protected static ?string $pluralModelLabel = 'Gastos Fixos';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('usuario_id')
                    ->default(fn () => Auth::id())
                    ->required(),

                Select::make('conta_id')
                    ->label('Conta')
                    ->relationship(
                        'conta', 
                        'nome',
                        fn (Builder $query) => $query->where('user_id', Auth::id())->where('ativo', true)
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Conta de onde será debitado o valor'),

                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ex: Aluguel, Internet, Academia'),

                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01)
                    ->required()
                    ->placeholder('0,00'),

                Select::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'moradia'       => 'Moradia',
                        'utilidades'    => 'Utilidades',
                        'transporte'    => 'Transporte',
                        'alimentacao'   => 'Alimentação',
                        'saude'         => 'Saúde',
                        'educacao'      => 'Educação',
                        'lazer'         => 'Lazer',
                        'seguros'       => 'Seguros',
                        'subscricoes'   => 'Assinaturas',
                        'outros'        => 'Outros',
                    ])
                    ->required()
                    ->native(false)
                    ->searchable(),

                TextInput::make('dia_vencimento')
                    ->label('Dia de Vencimento')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required()
                    ->placeholder('Ex: 10')
                    ->helperText('Dia do mês em que vence esta conta'),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->helperText('Desative se não está mais pagando este gasto'),
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
                    ->limit(25)
                    ->tooltip(fn (GastoFixo $record): string => $record->descricao),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'moradia'       => 'Moradia',
                        'utilidades'    => 'Utilidades',
                        'transporte'    => 'Transporte',
                        'alimentacao'   => 'Alimentação',
                        'saude'         => 'Saúde',
                        'educacao'      => 'Educação',
                        'lazer'         => 'Lazer',
                        'seguros'       => 'Seguros',
                        'subscricoes'   => 'Assinaturas',
                        'outros'        => 'Outros',
                        default         => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'moradia'       => 'primary',
                        'utilidades'    => 'warning',
                        'transporte'    => 'success',
                        'alimentacao'   => 'info',
                        'saude'         => 'danger',
                        'educacao'      => 'purple',
                        'lazer'         => 'pink',
                        'seguros'       => 'indigo',
                        'subscricoes'   => 'orange',
                        'outros'        => 'gray',
                        default         => 'gray',
                    }),

                TextColumn::make('conta.nome')
                    ->label('Conta')
                    ->searchable()
                    ->placeholder('Não definida')
                    ->toggleable(),

                TextColumn::make('dia_vencimento')
                    ->label('Vencimento')
                    ->sortable()
                    ->suffix('º dia')
                    ->color(fn ($state) => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 15 => 'warning',
                        default => 'success',
                    }),

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
                SelectFilter::make('categoria')
                    ->label('Categoria')
                    ->options([
                        'moradia'       => 'Moradia',
                        'utilidades'    => 'Utilidades',
                        'transporte'    => 'Transporte',
                        'alimentacao'   => 'Alimentação',
                        'saude'         => 'Saúde',
                        'educacao'      => 'Educação',
                        'lazer'         => 'Lazer',
                        'seguros'       => 'Seguros',
                        'subscricoes'   => 'Assinaturas',
                        'outros'        => 'Outros',
                    ])
                    ->native(false),

                SelectFilter::make('ativo')
                    ->label('Status')
                    ->options([
                        1 => 'Ativo',
                        0 => 'Inativo',
                    ])
                    ->native(false),

                SelectFilter::make('conta_id')
                    ->label('Conta')
                    ->relationship(
                        'conta', 
                        'nome',
                        fn (Builder $query) => $query->where('user_id', Auth::id())
                    )
                    ->searchable()
                    ->preload(),

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
            ->defaultSort('dia_vencimento', 'asc')
            ->striped();
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
        return 'warning';
    }
}