<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionRequestResource\Pages;
use App\Filament\Resources\SubscriptionRequestResource\RelationManagers;
use App\Models\SubscriptionRequest;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionRequestResource extends Resource
{
    protected static ?string $model = SubscriptionRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('plan')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'contacted' => 'Contactado',
                        'approved' => 'Aprovado',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Utilizador')->searchable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('plan')
                    ->label('Plano')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'weekly' => 'Semanal',
                        'monthly' => 'Mensal',
                        'quarterly' => 'Trimestral',
                        'yearly' => 'Anual',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'contacted',
                        'primary' => 'approved',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'contacted' => 'Contactado',
                        'approved' => 'Aprovado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_contacted')
                    ->label('Marcar Contactado')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (SubscriptionRequest $record) => $record->update(['status' => 'contacted']))
                    ->visible(fn (SubscriptionRequest $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSubscriptionRequests::route('/'),
            'create' => Pages\CreateSubscriptionRequest::route('/create'),
            'edit' => Pages\EditSubscriptionRequest::route('/{record}/edit'),
        ];
    }    
}
