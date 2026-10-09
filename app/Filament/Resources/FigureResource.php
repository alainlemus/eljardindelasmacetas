<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FigureResource\Pages;
use App\Models\Figure;
use App\Support\ImageOptimizer;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FigureResource extends Resource
{
    protected static ?string $model = Figure::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Catalogo';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getModelLabel(): string
    {
        return 'Figura';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Figuras';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2)
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))
                    ),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio de venta')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('cost')
                    ->label('Costo')
                    ->numeric()
                    ->prefix('$')
                    ->nullable(),
                Forms\Components\TextInput::make('stock')
                    ->label('Stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('min_stock')
                    ->label('Stock minimo')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\FileUpload::make('image')
                    ->label('Imagen principal')
                    ->image()
                    ->disk('public')
                    ->directory('funkomacetas')
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => ImageOptimizer::store($file))
                    ->helperText('Se convierte a WebP y se comprime automáticamente.'),
                Forms\Components\FileUpload::make('images')
                    ->label('Imagenes adicionales')
                    ->multiple()
                    ->image()
                    ->disk('public')
                    ->directory('funkomacetas')
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => ImageOptimizer::store($file))
                    ->helperText('Se convierte a WebP y se comprime automáticamente.'),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->rows(3)
                    ->columnSpan('full'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Destacado')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->disk('public')
                    ->size(60),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (Figure $record) => $record->is_low_stock ? 'danger' : ($record->stock > 20 ? 'success' : 'warning')
                    ),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable(),
                Tables\Filters\Filter::make('in_stock')
                    ->label('En stock')
                    ->query(fn ($query) => $query->where('stock', '>', 0)),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bajo')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'min_stock')),
                Tables\Filters\Filter::make('featured')
                    ->label('Solo destacados')
                    ->query(fn ($query) => $query->where('is_featured', true)),
                Tables\Filters\Filter::make('active')
                    ->label('Solo activos')
                    ->query(fn ($query) => $query->where('is_active', true)),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFigures::route('/'),
        ];
    }
}
