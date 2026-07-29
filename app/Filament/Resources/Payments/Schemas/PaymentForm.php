<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Member;
use App\Support\PaymentManager;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Member Information')
                    ->schema([

                        Select::make('member_id_no')
                            ->label('Member')
                            ->searchable()
                            ->live()
                            ->options(
                                Member::query()
                                    ->orderBy('mem_last_name')
                                    ->get()
                                    ->mapWithKeys(fn ($member) => [
                                        $member->member_id_no =>
                                            "{$member->member_id_no} - {$member->mem_last_name}, {$member->mem_first_name}",
                                    ])
                            ),

                        Hidden::make('payment_ref_no'),

                        Placeholder::make('member_name')
                            ->content(function ($get) {
                                $member = Member::find(
                                    $get('member_id_no')
                                );

                                return $member
                                    ? "{$member->mem_last_name}, {$member->mem_first_name}"
                                    : '-';
                            }),

                        Placeholder::make('membership_type')
                            ->content(function ($get) {
                                return Member::find(
                                    $get('member_id_no')
                                )?->psa_mem_type ?? '-';
                            }),

                        Placeholder::make('reference_no')
                            ->label('Reference No.')
                            ->content(function ($get) {
                                $member = Member::find(
                                    $get('member_id_no')
                                );

                                return $member
                                    ? PaymentManager::generateReference($member)
                                    : '-';
                            }),

                    ])
                    ->columns(2),
            ]);
    }
}