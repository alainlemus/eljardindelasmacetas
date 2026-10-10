<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Figure;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends StatsOverviewWidget
{
    /** Se carga con la página: el tablero no debe quedar vacío esperando al scroll. */
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $value = (float) (Figure::selectRaw('COALESCE(SUM(price * stock), 0) as total')->value('total') ?? 0);

        return [
            Stat::make('Total de figuras', Figure::count())
                ->description(Figure::active()->count().' activas · '.Category::count().' categorías')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Sin precio', Figure::where('price', '<=', 0)->count())
                ->description('Faltan costo y precio de venta')
                ->icon('heroicon-o-currency-dollar')
                ->color('danger'),

            Stat::make('Stock bajo', Figure::lowStock()->where('stock', '>', 0)->count())
                ->description('Figuras en stock mínimo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Valor del inventario', '$'.number_format($value, 2))
                ->description('Precio × stock disponible')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
