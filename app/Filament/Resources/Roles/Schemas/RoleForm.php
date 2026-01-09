<?php

namespace App\Filament\Resources\Roles\Schemas;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role')
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('guard_name')
                            ->default(config('auth.defaults.guard', 'web')),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Section::make('User Permissions')
                            ->collapsible()
                            ->schema([
                                self::permissionCheckboxList(
                                    name: 'permissions_user',
                                    label: 'User Permissions',
                                    modifyQueryUsing: static fn ($query) => $query->where('name', 'like', '%:User'),
                                ),
                            ]),
                        Section::make('Role Permissions')
                            ->collapsible()
                            ->schema([
                                self::permissionCheckboxList(
                                    name: 'permissions_role',
                                    label: 'Role Permissions',
                                    modifyQueryUsing: static fn ($query) => $query->where('name', 'like', '%:Role'),
                                ),
                            ]),
                        Section::make('Permission Permissions')
                            ->collapsible()
                            ->schema([
                                self::permissionCheckboxList(
                                    name: 'permissions_permission',
                                    label: 'Permission Permissions',
                                    modifyQueryUsing: static fn ($query) => $query->where('name', 'like', '%:Permission'),
                                ),
                            ]),
                    ]),

            ]);
    }

    private static function permissionCheckboxList(
        string $name,
        string $label,
        Closure $modifyQueryUsing,
    ): CheckboxList {
        return CheckboxList::make($name)
            ->label($label)
            ->relationship('permissions', 'name', $modifyQueryUsing)
            ->searchable()
            ->columns(2)
            ->loadStateFromRelationshipsUsing(static function (CheckboxList $component): void {
                $relationship = $component->getRelationship();
                if (! $relationship) {
                    $component->state([]);

                    return;
                }

                $optionIds = array_map('strval', array_keys($component->getOptions()));
                $relatedIds = $relationship->getResults()
                    ->pluck($relationship->getRelatedKeyName())
                    ->map(static fn ($key): string => strval($key))
                    ->all();

                $component->state(array_values(array_intersect($relatedIds, $optionIds)));
            })
            ->saveRelationshipsUsing(static function (CheckboxList $component, ?array $state): void {
                $relationship = $component->getRelationship();
                if (! $relationship) {
                    return;
                }

                $state = array_map('strval', $state ?? []);
                $optionIds = array_map('strval', array_keys($component->getOptions()));
                $relatedIds = $relationship->getResults()
                    ->pluck($relationship->getRelatedKeyName())
                    ->map(static fn ($key): string => strval($key))
                    ->all();

                $recordsToDetach = array_diff(array_intersect($relatedIds, $optionIds), $state);
                if (count($recordsToDetach) > 0) {
                    $relationship->detach($recordsToDetach);
                }

                $relationship->sync($state, detaching: false);
            });
    }
}
