<?php

namespace App\Filament\Resources\CMEPrograms\Pages;

use App\Filament\Resources\CMEPrograms\CMEProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCMEPrograms extends ListRecords
{
    protected static string $resource = CMEProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()->can('cme_create')),
        ];
    }
}
