<?php

namespace App\Filament\Resources\CMEPrograms\Pages;

use App\Filament\Resources\CMEPrograms\CMEProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCMEProgram extends EditRecord
{
    protected static string $resource = CMEProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
