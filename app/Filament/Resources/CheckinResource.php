<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinResource\Pages;
use App\Models\Checkin;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Support\Facades\Auth;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Relatórios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('usuario_id')
                    ->default(fn () => Auth::id())
                    ->required(),

                DatePicker::make('data_checkin')
                    ->label('Data do Check-in')
                    ->required()
                    ->default(now()),

                Select::make('humor_financeiro')
                    ->label('Humor Financeiro')
                    ->options([
                        'otimo'   => 'Ótimo',
                        'bom'     => 'Bom',
                        'neutro'  => 'Neutro',
                        'ruim'    => 'Ruim',
                        'pessimo' => 'Péssimo',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->placeholder('Como está sua situação financeira geral?'),

                Textarea::make('objetivos_alcancados')
                    ->label('Objetivos Alcançados')
                    ->rows(3)
                    ->placeholder('Quais metas financeiras você conquistou recentemente?'),

                Textarea::make('dificuldades')
                    ->label('Dificuldades')
                    ->rows(3)
                    ->placeholder('Quais desafios você está enfrentando?'),

                Textarea::make('proximos_passos')
                    ->label('Próximos Passos')
                    ->rows(3)
                    ->placeholder('O que você planeja fazer para melhorar sua situação financeira?'),
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

                TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('data_checkin')
                    ->date('d/m/Y')
                    ->label('Data')
                    ->sortable(),

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
                        'neutro' => 'gray',
                        'ruim' => 'warning',
                        'pessimo' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('observacoes')
                    ->label('Observações')
                    ->limit(30)
                    ->placeholder('Sem observações')
                    ->tooltip(fn (Checkin $record): ?string => $record->observacoes),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Criado em')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('data_checkin', 'desc');
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

    // Filtrar apenas check-ins do usuário logado
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('usuario_id', Auth::id());
    }
}