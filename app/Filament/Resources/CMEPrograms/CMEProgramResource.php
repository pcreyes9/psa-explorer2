<?php

namespace App\Filament\Resources\CMEPrograms;

use App\Filament\Resources\CMEPrograms\Pages\CreateCMEProgram;
use App\Filament\Resources\CMEPrograms\Pages\EditCMEProgram;
use App\Filament\Resources\CMEPrograms\Pages\ListCMEPrograms;
use App\Filament\Resources\CMEPrograms\Pages\ViewCMEProgram;
use App\Filament\Resources\CMEPrograms\Schemas\CMEProgramForm;
use App\Filament\Resources\CMEPrograms\Schemas\CMEProgramInfolist;
use App\Filament\Resources\CMEPrograms\Tables\CMEProgramsTable;
use App\Models\CMEProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CMEProgramResource extends Resource
{
    protected static ?string $model = CMEProgram::class;
    public static function getModelLabel(): string
    {
        return 'CME Program';
    }

    public static function getPluralModelLabel(): string
    {
        return 'CME Programs';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'cme_title';

    protected static ?string $navigationLabel = 'CME Programs';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Continuing Medical Education';
    }

    public static function form(Schema $schema): Schema
    {
        return CMEProgramForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CMEProgramInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CMEProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCMEPrograms::route('/'),
            'create' => CreateCMEProgram::route('/create'),
            'view' => ViewCMEProgram::route('/{record}'),
            'edit' => EditCMEProgram::route('/{record}/edit'),
        ];
    }
}
