<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\ListMembers;
use App\Filament\Resources\Members\Pages\ViewMember;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Schemas\MemberInfolist;
use App\Filament\Resources\Members\Tables\MembersTable;
use App\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'member_id_no';

    public static function getNavigationGroup(): ?string
    {
        return 'Membership';
    }

    public static function getNavigationLabel(): string
    {
        return 'Members';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'member_id_no',
            'mem_last_name',
            'mem_first_name',
            'mem_email_address',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MemberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'view' => ViewMember::route('/{record}'),
        ];
    }
}