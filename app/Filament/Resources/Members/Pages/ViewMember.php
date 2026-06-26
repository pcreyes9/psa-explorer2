<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\ActionGroup;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected string $view = 'filament.members.view-member';

    protected function getHeaderActions(): array
    {
        return [

            Action::make('certificateOfGoodStanding')
                ->label('Certificate of Good Standing')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                // ->visible(fn () => auth()->user()->can('member_print_cogs'))
                ->url(fn () =>
                    \App\Filament\Pages\CertificateOfGoodStanding::getUrl([
                        'member' => $this->record->member_id_no,
                    ])
                ),

            ActionGroup::make([

                EditAction::make()->color('gray')->visible(fn () => auth()->user()?->can('members_edit')),

                Action::make('updatePhoto')->visible(fn () => auth()->user()?->can('members_edit'))
                    ->label('Update Photo')
                    ->icon('heroicon-o-camera')
                    ->color('gray')
                    ->form([
                        FileUpload::make('photo')
                            ->image()
                            ->required()
                            ->disk('local')
                            ->directory('temp/member-photos'),
                    ])
                    ->action(function (array $data): void {

                        $path = storage_path(
                            'app/private/' . $data['photo']
                        );

                        $binary = file_get_contents($path);

                        $hex = bin2hex($binary);

                        DB::statement(
                            "
                            UPDATE member
                            SET mem_pic = CONVERT(varbinary(max), ?, 2)
                            WHERE member_id_no = ?
                            ",
                            [
                                $hex,
                                $this->record->member_id_no,
                            ]
                        );

                        Storage::disk('local')->delete(
                            $data['photo']
                        );

                        $this->record->refresh();

                        Notification::make()
                            ->title('Photo updated successfully')
                            ->success()
                            ->send();

                    }),

                Action::make('payDues')->visible(fn () => auth()->user()?->can('members_edit'))
                    ->label('Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->url(
                        fn () => url(
                            '/admin/new-payment?member=' .
                            $this->record->member_id_no
                        )
                    ),

            ])
                ->label('Actions')
                ->color('gray')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button(),

        ];
    }

    public function getTitle(): string
    {
        return $this->record->member_id_no
            . ' - '
            . $this->record->mem_last_name
            . ', '
            . $this->record->mem_first_name;
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
    public static function getNavigationItem(): ?string
    {
        return \App\Filament\Pages\MemberSearch::class;
    }
}
