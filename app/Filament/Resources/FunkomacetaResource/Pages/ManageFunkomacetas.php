<?php

namespace App\Filament\Resources\FunkomacetaResource\Pages;

use App\Filament\Resources\FunkomacetaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFunkomacetas extends ManageRecords
{
    protected static string $resource = FunkomacetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
