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
                Forms\Components\Select::make('approval_status')
                    ->label('Estado da aprovação')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovada',
                        'rejected' => 'Não aprovada',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('approval_notes')
                    ->label('Notas da aprovação')
                    ->helperText('Estas notas são apresentadas à empresa quando o cadastro não é aprovado.')
                    ->rows(3),
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
                Forms\Components\ColorPicker::make('theme_color')
                    ->default('#2557A7')
                    ->required()
                    ->label('Cor do tema'),
                Forms\Components\TextInput::make('max_attachments')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(1)
                    ->required()
                    ->label('Máximo de anexos por candidatura'),
                Forms\Components\FileUpload::make('logo')
                    ->directory('images/companies/logos')
                    ->image()
                    ->label('Logótipo'),
                Forms\Components\FileUpload::make('cover_image')
                    ->directory('images/companies/covers')
                    ->image()
                    ->label('Foto de capa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nome'),
                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Aprovação')
                    ->enum([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovada',
                        'rejected' => 'Não aprovada',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\BooleanColumn::make('user.email_verified_at')
                    ->label('Email confirmado')
                    ->getStateUsing(fn ($record) => (bool) $record->user?->email_verified_at),
                Tables\Columns\TextColumn::make('slug')
                    ->url(fn ($record) => url('/company/' . $record->slug))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.email')->label('Conta'),
                Tables\Columns\TextColumn::make('jobs_count')->counts('jobs')->label('Vagas'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Aprovação')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovada',
                        'rejected' => 'Não aprovada',
                    ]),
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
