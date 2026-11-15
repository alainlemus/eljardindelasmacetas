<?php

namespace App\Filament\Resources\SocialLinkResource\Pages;

use App\Filament\Resources\SocialLinkResource;
use App\Models\SocialLink;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSocialLinks extends ManageRecords
{
    protected static string $resource = SocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['sort_order'] = (int) SocialLink::max('sort_order') + 1;

                    return $data;
                }),
        ];
    }
}
