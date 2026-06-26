<?php

namespace App\Filament\Pages;

use App\Models\Funkomaceta;
use App\Models\Category;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends BaseDashboard
{
    public function getColumns(): array|int
    {
        return 4;
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getStats(): array
    {
        $totalProducts = Funkomaceta::count();
        $lowStock = Funkomaceta::lowStock()->count();
        $outOfStock = Funkomaceta::where('stock', 0)->count();
        $totalCategories = Category::count();
        $totalValue = Funkomaceta::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;

        return [
            Stat::make('Total Productos', $totalProducts)
                ->description('Funkomacetas en catalogo')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Stock Bajo', $lowStock)
                ->description('Productos con stock minimo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Sin Stock', $outOfStock)
                ->description('Productos agotados')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Valor Inventario', '$' . number_format($totalValue, 2))
                ->description('Valor total del inventario')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
