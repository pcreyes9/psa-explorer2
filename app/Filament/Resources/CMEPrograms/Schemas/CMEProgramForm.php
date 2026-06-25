<?php

namespace App\Filament\Resources\CMEPrograms\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class CMEProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)

            ->components([

                Section::make('Basic Information')
                    ->description('Create or update a CME Program.')
                    ->columns(3)
                    ->schema([

                        TextInput::make('cme_program_code')
                            ->label('Program Code')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->dehydrated()
                            ->helperText('Program Code cannot be changed after creation.'),

                        Select::make('cme_year')
                            ->label('Fiscal Year')
                            ->options(
                                collect(range(date('Y') - 2, date('Y') + 5))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                            )
                            ->searchable()
                            ->required(),

                        Select::make('cme_program_type')
                            ->label('Program Type')
                            ->options(fn () =>
                                DB::table('cme_program_type')
                                    ->where('stat', 1)
                                    ->orderBy('cme_type')
                                    ->pluck('cme_type', 'cme_type')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('cme_title')
                            ->label('Program Title')
                            ->required()
                            ->columnSpanFull(),

                    ]),

                Section::make('Program Details')
                    ->schema([

                        TextInput::make('cme_topic')
                            ->label('Topic'),

                        Textarea::make('cme_desc')
                            ->label('Description')
                            ->rows(6),

                    ]),

                Section::make('Schedule & Venue')
                    ->columns(3)
                    ->schema([

                        TextInput::make('cme_venue')
                            ->label('Venue')
                            ->columnSpanFull(),

                        DatePicker::make('cme_startdate')
                            ->label('Start Date')
                            ->native(false),

                        DatePicker::make('cme_enddate')
                            ->label('End Date')
                            ->native(false),

                        TextInput::make('cme_equiv_pts')
                            ->label('Equivalent Points')
                            ->numeric(),

                    ]),

                Section::make('Administrative Information')
                    ->columns(3)
                    ->schema([

                        TextInput::make('cme_chair')
                            ->label('Chairman'),

                        TextInput::make('cme_incumbent_prez')
                            ->label('Incumbent President'),

                        TextInput::make('cme_prc_ref')
                            ->label('PRC Reference No.'),

                        TextInput::make('cme_budget')
                            ->label('Budget')
                            ->numeric()
                            ->prefix('₱'),

                        Toggle::make('stat')
                            ->label('Active'),

                    ]),
            ]);
    }
}
