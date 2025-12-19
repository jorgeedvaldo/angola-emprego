<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    protected static ?string $recordTitleAttribute = 'title';
    
    protected static ?string $label = 'Curso';
    
    protected static ?string $pluralLabel = 'Cursos Inscritos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Curso'),
                Tables\Columns\TextColumn::make('pivot.completed_at')
                    ->label('Concluído em')
                    ->dateTime(),
                Tables\Columns\BadgeColumn::make('pivot.completed_at')
                    ->label('Status')
                    ->color(fn ($record) => $record->pivot->completed_at ? 'success' : 'warning')
                    ->formatStateUsing(fn ($record) => $record->pivot->completed_at ? 'Concluído' : 'Em Progresso'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
               // Tables\Actions\AttachAction::make(),
            ])
            ->actions([
               Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
               Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
