<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Figure;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalValue = Figure::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;

        return [
            Stat::make('Total Productos', Figure::count())
                ->description(Category::count().' categorías')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Stock Bajo', Figure::lowStock()->where('stock', '>', 0)->count())
                ->description('Productos en stock mínimo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Sin Stock', Figure::where('stock', 0)->count())
                ->description('Productos agotados')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Valor Inventario', '$'.number_format($totalValue, 2))
                ->description('Precio × stock')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }
}
