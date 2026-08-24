<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $navigationLabel = 'Manajemen User';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Manajemen User';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isGuruBk() ?? false) && auth()->id() !== $record->getKey();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isGuruBk() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}