<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Gestão de Acesso';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => !empty($state))
                    ->label('Senha (Deixe em branco para manter)'),
                Forms\Components\Toggle::make('is_admin')
                    ->required()
                    ->label('É Administrador?')
                    ->helperText('Habilite para dar acesso ao painel administrativo.'),
                
                Forms\Components\Section::make('Documentos')
                    ->schema([
                        Forms\Components\FileUpload::make('cv_path')
                            ->label('Curriculum Vitae (CV)')
                            ->directory('cvs')
                            ->acceptedFileTypes(['application/pdf'])
                            ->enableDownload()
                            ->enableOpen()
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('Nome'),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable(),
                Tables\Columns\BooleanColumn::make('is_admin')->sortable()->label('Admin'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Criado em'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_admin')
                    ->query(fn ($query) => $query->where('is_admin', true))
                    ->label('Apenas Admins'),
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
            UserResource\RelationManagers\CategoriesRelationManager::class,
            UserResource\RelationManagers\CoursesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
