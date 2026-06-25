<?php

namespace App\Filament\Resources\CMEPrograms\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CMEProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('ProgramTabs')
                    ->tabs([

                        Tab::make('Program Information')
                            ->icon('heroicon-m-document-text')
                            ->schema([

                                Section::make('Basic Information')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_program_code')
                                            ->label('Program Code'),

                                        TextEntry::make('cme_year')
                                            ->label('Fiscal Year'),

                                        TextEntry::make('cme_program_type')
                                            ->label('Program Type'),

                                        TextEntry::make('cme_title')
                                            ->label('Program Title')
                                            ->columnSpanFull(),

                                        TextEntry::make('cme_topic')
                                            ->label('Topic')
                                            ->placeholder('-')
                                            ->columnSpanFull(),

                                    ]),

                                Section::make('Schedule & Venue')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_venue')
                                            ->label('Venue')
                                            ->columnSpanFull(),

                                        TextEntry::make('cme_startdate')
                                            ->label('Start Date')
                                            ->date(),

                                        TextEntry::make('cme_enddate')
                                            ->label('End Date')
                                            ->date(),

                                        TextEntry::make('cme_equiv_pts')
                                            ->label('Equivalent Points')
                                            ->placeholder('-'),

                                    ]),

                                Section::make('Administration')
                                    ->columns(3)
                                    ->schema([

                                        TextEntry::make('cme_chair')
                                            ->label('Chairman')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_incumbent_prez')
                                            ->label('Incumbent President')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_prc_ref')
                                            ->label('PRC Reference No.')
                                            ->placeholder('-'),

                                        TextEntry::make('cme_budget')
                                            ->label('Budget')
                                            ->money('PHP')
                                            ->placeholder('-'),

                                        TextEntry::make('stat')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                                            ->color(fn (bool $state) => $state ? 'success' : 'danger'),

                                    ]),

                                Section::make('Description')
                                    ->schema([

                                        TextEntry::make('cme_desc')
                                            ->label('CME Description')
                                            ->placeholder('-'),

                                    ]),
                            ]),

                        Tab::make('Rates')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Section::make('Convention Fees')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                        Tab::make('Units')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Section::make('CME Units')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                        Tab::make('Lecturers')
                            ->icon('heroicon-m-user-group')
                            ->schema([
                                Section::make('Lecturers')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                        Tab::make('Sponsors')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Section::make('Sponsors')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                        Tab::make('Registrations')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([
                                Section::make('Registrations')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                        Tab::make('Attendance')
                            ->icon('heroicon-m-check-badge')
                            ->schema([
                                Section::make('Attendance')
                                    ->description('Coming soon.')
                                    ->schema([]),
                            ]),

                    ])
                    ->columnSpanFull(),
            ]);
    }
}
