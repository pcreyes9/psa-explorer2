<?php

namespace App\Filament\Resources\CMEPrograms\Pages;

use App\Filament\Resources\CMEPrograms\CMEProgramResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\CMEPrograms\Schemas\CMEProgramInfolist;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

class ViewCMEProgram extends ViewRecord
{
    protected static string $resource = CMEProgramResource::class;

    public function getTitle(): string
    {
        return $this->record->cme_program_code;
    }

    protected function getHeaderActions(): array
    {
        return [

            EditAction::make(),

            ActionGroup::make([

                Action::make('duplicate')
                    ->label('Duplicate Program')
                    ->icon('heroicon-o-document-duplicate'),

                Action::make('archive')
                    ->label('Archive Program')
                    ->icon('heroicon-o-archive-box'),

            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),

        ];
    }
    public function infolist(Schema $schema): Schema
    {
        return CMEProgramInfolist::configure($schema);
    }
}
