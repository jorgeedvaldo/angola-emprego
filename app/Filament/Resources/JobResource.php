<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jobs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)->schema([
                    Forms\Components\Select::make('company_id')
                        ->label('Empresa (página)')
                        ->relationship('companyRecord', 'name')
                        ->searchable()
                        ->nullable(),
                    Forms\Components\TextInput::make('title')->required(),
                    Forms\Components\TextInput::make('company')->required(),
                    Forms\Components\TextInput::make('location')->required(),
                    Forms\Components\RichEditor::make('description')->required(),
                    Forms\Components\TextInput::make('email_or_link')->required(),
                    Forms\Components\FileUpload::make('image')
                        ->label('Imagem (gerada automaticamente se vazia)')
                        ->directory('images/jobs')
                        ->image(),
                    Forms\Components\MultiSelect::make('categories')
                        ->relationship('categories','name'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company')
                    ->searchable()
                    ->label('Empresa'),
                Tables\Columns\TextColumn::make('companyRecord.slug')
                    ->label('Página')
                    ->url(fn ($record) => $record->companyRecord ? url('/company/' . $record->companyRecord->slug) : null),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
