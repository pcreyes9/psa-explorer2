<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('MemberTabs')
                    ->tabs([

                        Tab::make('Personal')
                            ->icon('heroicon-m-user')
                            ->schema([

                                Section::make('Personal Information')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('mem_last_name')
                                            ->label('Last Name'),

                                        TextEntry::make('mem_first_name')
                                            ->label('First Name'),

                                        TextEntry::make('mem_middle_name')
                                            ->label('Middle Name')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_birth_date')
                                            ->label('Birth Date')
                                            ->date(),

                                        TextEntry::make('mem_birth_place')
                                            ->label('Birth Place')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_gender')
                                            ->label('Gender')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_civil_status')
                                            ->label('Civil Status')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_citizenship')
                                            ->label('Citizenship')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_religion')
                                            ->label('Religion')
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        Tab::make('Contact')
                            ->icon('heroicon-m-phone')
                            ->schema([

                                Section::make('Contact Information')
                                    ->columns(2)
                                    ->schema([

                                        TextEntry::make('mem_mobile_no1')
                                            ->label('Mobile No. 1')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_mobile_no2')
                                            ->label('Mobile No. 2')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_email_address')
                                            ->label('Email Address')
                                            ->placeholder('-')
                                            ->columnSpanFull(),

                                        TextEntry::make('mem_home_address')
                                            ->label('Home Address')
                                            ->placeholder('-')
                                            ->columnSpanFull(),

                                        TextEntry::make('mem_province')
                                            ->label('Province')
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        Tab::make('Professional')
                            ->icon('heroicon-m-briefcase')
                            ->schema([

                                Section::make('Professional Information')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('mem_prc_no')
                                            ->label('PRC Number')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_prc_exp_date')
                                            ->label('PRC Expiry')
                                            ->date(),

                                        TextEntry::make('mem_sr_no')
                                            ->label('SR Number')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_practice_pref')
                                            ->label('Practice Preference')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_pma_id_no')
                                            ->label('PMA ID')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_fellow_no')
                                            ->label('Fellow Number')
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        Tab::make('Government IDs')
                            ->icon('heroicon-m-identification')
                            ->schema([

                                Section::make('Government Information')
                                    ->columns(2)
                                    ->schema([

                                        TextEntry::make('mem_tin_no')
                                            ->label('TIN')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_sss_no')
                                            ->label('SSS')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_gsis_no')
                                            ->label('GSIS')
                                            ->placeholder('-'),

                                        TextEntry::make('mem_phic_no')
                                            ->label('PhilHealth')
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        Tab::make('Membership')
                            ->icon('heroicon-m-user-group')
                            ->schema([

                                Section::make('Membership Information')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('member_id_no')
                                            ->label('PSA ID'),

                                        TextEntry::make('psa_mem_stat')
                                            ->label('Membership Status')
                                            ->badge(),

                                        TextEntry::make('membershipType.Memtype')
                                            ->label('Membership Type')
                                            ->placeholder('-'),

                                        TextEntry::make('chapter.psa_chapter_desc')
                                            ->label('Chapter')
                                            ->placeholder('-'),

                                        TextEntry::make('datejoin')
                                            ->label('Date Joined')
                                            ->date(),

                                        TextEntry::make('mem_age')
                                            ->label('Age')
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        // Tab::make('Ledger')
                        //     ->icon('heroicon-m-banknotes')
                        //     ->schema([
                        //         Section::make('Member Ledger')
                        //             ->description('Coming soon')
                        //             ->schema([]),
                        //     ]),

                        // Tab::make('Payments')
                        //     ->icon('heroicon-m-credit-card')
                        //     ->schema([
                        //         Section::make('Payment History')
                        //             ->description('Coming soon')
                        //             ->schema([]),
                        //     ]),

                        // Tab::make('Balance Summary')
                        //     ->icon('heroicon-m-calculator')
                        //     ->schema([
                        //         Section::make('Balance Summary')
                        //             ->description('Coming soon')
                        //             ->schema([]),
                        //     ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
