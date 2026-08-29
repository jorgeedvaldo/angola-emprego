<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $navigationGroup = 'Jobs';

    protected static ?string $navigationLabel = 'Empresas';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required()
                    ->label('Conta'),
                Forms\Components\TextInput::make('name')->required()->label('Nome'),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('headline')
                    ->maxLength(180)
                    ->label('Headline'),
                Forms\Components\Textarea::make('description')->label('Sobre a empresa')->rows(5),
                Forms\Components\TextInput::make('location')->label('Localização'),
                Forms\Components\TextInput::make('website')->url()->label('Website'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone')->label('Telefone'),
                Forms\Components\TextInput::make('linkedin_url')->url()->label('LinkedIn'),
                Forms\Components\TextInput::make('facebook_url')->url()->label('Facebook'),
                Forms\Components\TextInput::make('instagram_url')->url()->label('Instagram'),
                Forms\Components\TextInput::make('max_attachments')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(1)
                    ->required()
                    ->label('Máximo de anexos por candidatura'),
                Forms\Components\FileUpload::make('logo')
                    ->directory('images/companies')
                    ->image()
                    ->label('Logótipo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nome'),
                Tables\Columns\TextColumn::make('slug')
                    ->url(fn ($record) => url('/company/' . $record->slug))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.email')->label('Conta'),
                Tables\Columns\TextColumn::make('jobs_count')->counts('jobs')->label('Vagas'),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
