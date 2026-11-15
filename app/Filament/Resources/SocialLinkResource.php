<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $slug = 'social-links';

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getModelLabel(): string
    {
        return 'Red social';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Redes sociales';
    }

    public static function form(Schema $schema): Schema
    {
        $isWhatsapp = fn (Get $get): bool => $get('platform') === 'whatsapp';

        return $schema
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('platform')
                    ->label('Red')
                    ->options(SocialLink::PLATFORMS)
                    ->required()
                    ->native(false)
                    ->live(),
                Forms\Components\TextInput::make('label')
                    ->label('Nombre a mostrar')
                    ->placeholder('Opcional, p. ej. "Ventas"')
                    ->maxLength(60),
                Forms\Components\TextInput::make('phone')
                    ->label('Número de WhatsApp')
                    ->helperText('Con lada del país, p. ej. 52 55 1234 5678. Aquí llegan los pedidos.')
                    ->tel()
                    ->regex('/^\+?[\d\s\-()]{8,20}$/')
                    ->required($isWhatsapp)
                    ->visible($isWhatsapp)
                    ->columnSpan('full'),
                Forms\Components\TextInput::make('url')
                    ->label('Enlace')
                    ->placeholder('https://instagram.com/tu-cuenta')
                    ->url()
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => filled($get('platform')) && $get('platform') !== 'whatsapp')
                    ->visible(fn (Get $get): bool => filled($get('platform')) && $get('platform') !== 'whatsapp')
                    ->columnSpan('full'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Visible en el sitio')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->afterReordering(fn () => SocialLink::flushCache())
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Red')
                    ->formatStateUsing(fn (string $state) => SocialLink::PLATFORMS[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Nombre')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('href')
                    ->label('Enlace')
                    ->state(fn (SocialLink $record) => $record->platform === 'whatsapp' ? '+'.$record->phone : $record->url)
                    ->url(fn (SocialLink $record) => $record->href, shouldOpenInNewTab: true)
                    ->limit(50),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Sin redes sociales')
            ->emptyStateDescription('Agrega tu WhatsApp y tus redes para que aparezcan en el catálogo.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSocialLinks::route('/'),
        ];
    }
}
