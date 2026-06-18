<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected string $view = 'filament.members.view-member';

    protected function getHeaderActions(): array
    {
        return auth()->user()->hasRole('Super Admin')
            || auth()->user()->hasRole('Admin')
            || auth()->user()->hasRole('Encoder')
            ? [
                EditAction::make(),
            ]
            : [];
    }

    public function getTitle(): string
    {
        return $this->record->member_id_no . ' - ' . $this->record->mem_last_name . ', ' . $this->record->mem_first_name;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [
             url('/admin/member-search') => 'PSA Member',

            '#' => sprintf(
                '%s - %s',
                $this->record->member_id_no,
                strtoupper(
                    trim(
                        $this->record->mem_first_name . ' ' .
                        $this->record->mem_last_name
                    )
                )
            ),
        ];
    }
}