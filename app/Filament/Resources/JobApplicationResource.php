<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Jobs';

    protected static ?string $navigationLabel = 'Candidaturas';

    protected static ?string $modelLabel = 'Candidatura';

    protected static ?string $pluralModelLabel = 'Candidaturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->disabled()->label('Nome'),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('phone')->disabled()->label('Telefone'),
                Forms\Components\TextInput::make('subject')->disabled()->label('Assunto'),
                Forms\Components\Textarea::make('message')->disabled()->label('Mensagem')->rows(6),
                Forms\Components\TextInput::make('attachment_name')->disabled()->label('Anexo'),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'Nova',
                        'reviewed' => 'Vista',
                        'rejected' => 'Rejeitada',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job.title')->label('Vaga')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->label('Candidato'),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Assunto')->limit(40),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'new',
                        'success' => 'reviewed',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('Recebida'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }
}
