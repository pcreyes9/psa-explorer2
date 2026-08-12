<?php

namespace App\Filament\Resources\CashVouchers;

use App\Filament\Resources\CashVouchers\Pages\CreateCashVoucher;
use App\Filament\Resources\CashVouchers\Pages\EditCashVoucher;
use App\Filament\Resources\CashVouchers\Pages\ListCashVouchers;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\CashVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;

class CashVoucherResource extends Resource
{
    protected static ?string $model = CashVoucher::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $title = 'Check and Voucher Printing';

    public static function getNavigationLabel(): string { return 'Check and Voucher'; }

    public static function getNavigationGroup(): ?string { return 'Printing'; }

    protected static ?string $recordTitleAttribute = 'voucher_no';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | VOUCHER INFORMATION
                |--------------------------------------------------------------------------
                */

                Section::make('Voucher Information')
                    ->columnSpanFull()
                    ->schema([

                        TextInput::make('voucher_no')
                            ->label('Voucher No.')
                            ->default(function () {

                                $lastVoucher = \App\Models\CashVoucher::query()
                                    ->orderByDesc('id')
                                    ->first();

                                return $lastVoucher
                                    ? ((int) $lastVoucher->voucher_no + 1)
                                    : 1;
                            })
                            ->required()
                            ->maxLength(100),

                        TextInput::make('check_no')
                            ->label('Check No.')
                            ->default(function () {

                                $lastVoucher = \App\Models\CashVoucher::query()
                                    ->orderByDesc('id')
                                    ->first();

                                return $lastVoucher
                                    ? ((int) $lastVoucher->check_no + 1)
                                    : 1;
                            })
                            ->required()
                            ->maxLength(100),

                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),

                        TextInput::make('approved_by')
                            ->label('Approved By')
                            ->default('FB MAYUGA, MD')
                            ->maxLength(255),

                        TextInput::make('checked_by')
                            ->label('Checked By')
                            ->default('CS LUNAS, MD')
                            ->maxLength(255),

                        TextInput::make('received_by')
                            ->label('Received By')
                            ->maxLength(255),

                        TextInput::make('pay_to')
                            ->label('Pay To')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpan(3),


                    ])
                    ->columns(6),

                // Section::make('Signatories')
                //     ->schema([

                //         TextInput::make('approved_by')
                //             ->label('Approved By')
                //             ->default('FB MAYUGA, MD')
                //             ->maxLength(255),

                //         TextInput::make('checked_by')
                //             ->label('Checked By')
                //             ->default('CS LUNAS, MD')
                //             ->maxLength(255),

                //         TextInput::make('received_by')
                //             ->label('Received By')
                //             ->maxLength(255),

                //     ])
                //     ->columns(3),


                /*
                |--------------------------------------------------------------------------
                | ITEMS
                |--------------------------------------------------------------------------
                */

                Section::make('Voucher Items')
                    ->description(
                        'Add the items included in this disbursement voucher.'
                    )
                    ->columnSpanFull()
                    ->schema([

                        Repeater::make('items')
                            ->relationship('items')
                            ->live()
                            ->afterStateUpdated(function (
                                Get $get,
                                Set $set,
                                ?array $state
                            ) {

                                $total = collect($state ?? [])
                                    ->sum(function ($item) {
                                        return (float) ($item['amount'] ?? 0);
                                    });

                                $set('total_amount', $total);
                            })

                            // Prevent adding another item if an existing one is incomplete
                            ->addable(fn (Get $get) => collect($get('items') ?? [])
                                ->every(fn ($item) =>
                                    filled(trim($item['description'] ?? ''))
                                    && filled($item['amount'] ?? null)
                                )
                            )

                            ->schema([

                                Textarea::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->rows(2)
                                    ->autosize()
                                    ->columnSpan(2),

                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->required()
                                    ->minValue(0),

                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Voucher Item')

                            ->addAction(fn ($action) =>

                                $action
                                    ->label('Add Voucher Item')
                                    ->icon('heroicon-o-plus-circle')
                                    ->color('info')
                                    ->outlined()
                                    ->extraAttributes([
                                        'style' => 'height:35px; min-width:220px;',
                                    ])

                            )

                            ->reorderable()
                            ->reorderableWithDragAndDrop()
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()

                            ->itemLabel(
                                fn (array $state): ?string =>
                                    filled($state['description'] ?? null)
                                        ? \Illuminate\Support\Str::limit($state['description'], 50)
                                        : 'Voucher Item'
                            ),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->readOnly()
                            ->dehydrated(),

                    ]),


                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                // Section::make('Voucher Total')
                //     ->schema([

                //         TextInput::make('total_amount')
                //             ->label('Total Amount')
                //             ->numeric()
                //             ->prefix('₱')
                //             ->readOnly()
                //             ->dehydrated(),

                //     ]),


                /*
                |--------------------------------------------------------------------------
                | SIGNATORIES
                |--------------------------------------------------------------------------
                */



            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make('voucher_no')
                    ->label('Voucher No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('m/d/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pay_to')
                    ->label('Pay To')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_no')
                    ->label('Check No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('printed_at')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn ($record) =>
                        $record->printed_at ? 'Printed' : 'Not Printed'
                    )
                    ->color(fn ($record) =>
                        $record->printed_at ? 'success' : 'gray'
                    )
                    ->tooltip(fn ($record) =>
                        $record->printed_at
                            ?   $record->printed_at->format('M d, Y')
                                . ' | '
                                . ($record->printer?->username ?? 'Unknown')
                            : 'Not Printed'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

            ])

            ->defaultSort('date', 'desc')

            ->actions([

                ActionGroup::make([

                    EditAction::make(),

                    Action::make('printVoucher')
                        ->label('Print Voucher')
                        ->icon('heroicon-o-document-text')
                        ->url(
                            fn (CashVoucher $record) =>
                                route(
                                    'cash-voucher.pdf',
                                    $record
                                )
                        )
                        ->openUrlInNewTab(),

                    Action::make('printCheck')
                        ->label('Print Check')
                        ->icon('heroicon-o-printer')
                        ->url(
                            fn (CashVoucher $record) =>
                                route(
                                    'cash-voucher.check',
                                    $record
                                )
                        )
                        ->openUrlInNewTab(),

                    DeleteAction::make(),

                ]),

            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_amount'] = collect($data['items'] ?? [])
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_amount'] = collect($data['items'] ?? [])
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        return $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashVouchers::route('/'),
            'create' => CreateCashVoucher::route('/create'),
            'edit' => EditCashVoucher::route('/{record}/edit'),
        ];
    }
}
