<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\InventoryStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): array|int
    {
        return 4;
    }

    public function getWidgets(): array
    {
        return [InventoryStats::class];
    }
}
