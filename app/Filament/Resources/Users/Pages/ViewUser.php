<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()
                ->visible(function () {
                    $record = $this->getRecord();

                    return ! (
                        $record->hasRole('Super Admin')
                        && ! auth()->user()?->hasRole('Super Admin')
                    );
                }),
        ];
    }
}
