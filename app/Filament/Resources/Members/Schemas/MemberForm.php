<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('MemberTabs')
                    ->tabs([

                        Tabs\Tab::make('Personal')
                            ->schema([

                                TextInput::make('member_id_no')
                                    ->label('Member ID'),

                                TextInput::make('mem_last_name')
                                    ->required(),

                                TextInput::make('mem_first_name')
                                    ->required(),

                                TextInput::make('mem_middle_name'),

                                // TextInput::make('mem_suffix'),

                                DatePicker::make('mem_birth_date'),

                                TextInput::make('mem_birth_place'),

                                Select::make('mem_gender')
                                    ->options([
                                        'Male' => 'Male',
                                        'Female' => 'Female',
                                    ]),

                                TextInput::make('mem_civil_status'),

                                TextInput::make('mem_citizenship'),

                                TextInput::make('mem_religion'),

                            ])
                            ->columns(2),

                        Tabs\Tab::make('Contact')
                            ->schema([

                                TextInput::make('mem_home_address')
                                    ->columnSpanFull(),

                                TextInput::make('mem_province'),

                                TextInput::make('mem_email_address')
                                    ->email(),

                                TextInput::make('mem_mobile_no1'),

                                TextInput::make('mem_mobile_no2'),

                            ])
                            ->columns(2),

                        Tabs\Tab::make('Professional')
                            ->schema([

                                TextInput::make('psa_chapter_code'),

                                TextInput::make('psa_mem_type'),

                                TextInput::make('psa_mem_stat'),

                                DatePicker::make('datejoin'),

                                TextInput::make('mem_prc_no'),

                                DatePicker::make('mem_prc_exp_date'),

                                TextInput::make('mem_fellow_no'),

                                TextInput::make('mem_pma_id_no'),

                            ])
                            ->columns(2),

                        Tabs\Tab::make('Government IDs')
                            ->schema([

                                TextInput::make('mem_tin_no')
                                    ->label('TIN'),

                                TextInput::make('mem_sss_no')
                                    ->label('SSS'),

                                TextInput::make('mem_gsis_no')
                                    ->label('GSIS'),

                                TextInput::make('mem_phic_no')
                                    ->label('PhilHealth'),

                            ])
                            ->columns(2),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}