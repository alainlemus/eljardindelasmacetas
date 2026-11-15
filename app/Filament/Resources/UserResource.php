<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Usuarios del panel y de la app móvil (misma cuenta para ambos). Los administradores
 * entran a /admin y pueden gestionar el catálogo desde la app; los demás solo inician sesión.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users';
    }

    public static function getModelLabel(): string
    {
        return 'Usuario';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Usuarios';
    }

    public static function isSelf(?User $record): bool
    {
        return $record !== null && $record->is(Auth::user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    // Al editar, vacío = conservar la contraseña actual. El modelo la hashea.
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Déjala vacía para no cambiarla.' : null),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Administrador')
                    ->helperText('Entra al panel y gestiona el catálogo desde la app.')
                    ->default(true)
                    // Evita que te quites el acceso a ti mismo por error.
                    ->disabled(fn (?User $record): bool => static::isSelf($record))
                    ->dehydrated(fn (?User $record): bool => ! static::isSelf($record))
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): ?string => static::isSelf($record) ? 'Tú' : null),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Administrador')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tokens_count')
                    ->label('Sesiones en la app')
                    ->counts('tokens')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Alta')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Administrador'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('logoutApp')
                    ->label('Cerrar sesiones de la app')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('El usuario tendrá que volver a iniciar sesión en la app.')
                    ->visible(fn (User $record): bool => $record->tokens()->exists())
                    ->action(fn (User $record) => $record->tokens()->delete())
                    ->successNotificationTitle('Sesiones cerradas'),
                Actions\DeleteAction::make()
                    ->hidden(fn (User $record): bool => static::isSelf($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
