<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            Hidden::make('guard_name')
                ->default(config('auth.defaults.guard', 'web')),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ];

        foreach (self::permissionGroups() as $groupKey => $group) {
            $filterQuery = static function ($query) use ($group): void {
                $query->whereIn('name', $group['names']);
            };

            $components[] = Section::make($group['label'] . ' Permissions')
                ->collapsible()
                ->schema([
                    self::selectAllCheckbox(
                        target: 'permissions_' . $groupKey,
                        label: 'Pilih semua ' . $group['label'] . ' Permissions',
                        modifyQueryUsing: $filterQuery,
                    ),
                    self::permissionCheckboxList(
                        name: 'permissions_' . $groupKey,
                        label: $group['label'] . ' Permissions',
                        modifyQueryUsing: $filterQuery,
                    ),
                ]);
        }

        return $schema
            ->components([
                Section::make('Role')
                    ->columnSpanFull()
                    ->schema($components),

            ]);
    }

    /**
     * @return array<string, array{label:string, names:array<int, string>}>
     */
    private static function permissionGroups(): array
    {
        $guard = config('auth.defaults.guard', 'web');

        /** @var array<int, string> $permissionNames */
        $permissionNames = Permission::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $groups = [];

        foreach ($permissionNames as $permissionName) {
            $label = self::extractGroupLabelFromPermission($permissionName);
            $key = Str::slug($label, '_');

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $label,
                    'names' => [],
                ];
            }

            $groups[$key]['names'][] = $permissionName;
        }

        ksort($groups);

        return $groups;
    }

    private static function extractGroupLabelFromPermission(string $permissionName): string
    {
        [$action, $target] = array_pad(explode(':', $permissionName, 2), 2, null);

        $resolved = trim((string) ($target ?: $action));

        return $resolved !== '' ? $resolved : 'General';
    }

    private static function selectAllCheckbox(
        string $target,
        string $label,
        Closure $modifyQueryUsing,
    ): Checkbox {
        return Checkbox::make($target . '_all')
            ->label($label)
            ->live()
            ->afterStateUpdated(static function (Set $set, ?bool $state) use ($target, $modifyQueryUsing): void {
                $query = Permission::query()
                    ->where('guard_name', config('auth.defaults.guard', 'web'));

                $modifyQueryUsing($query);

                $ids = $query->pluck('id')->map(static fn ($id): string => (string) $id)->all();

                $set($target, $state ? $ids : []);
            });
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
