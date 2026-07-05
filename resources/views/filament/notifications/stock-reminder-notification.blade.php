@php
    use Filament\Notifications\View\Components\NotificationComponent\IconComponent;
    use Filament\Notifications\View\NotificationsIconAlias;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Js;
    use Illuminate\View\ComponentAttributeBag;

    $status = $notification->getStatus();
    $title = $notification->getTitle();
    $icon = $notification->getIcon();
    $summary = $summary ?? null;
    $itemsPreview = $itemsPreview ?? collect();
    $extraCount = $extraCount ?? 0;
@endphp

<div
    x-data="notificationComponent({ notification: {{ Js::from($notification->toArray()) }} })"
    x-transition:enter-start="fi-transition-enter-start"
    x-transition:enter-end="fi-transition-enter-end"
    x-transition:leave-start="fi-transition-leave-start"
    x-transition:leave-end="fi-transition-leave-end"
    @class([
        'fi-no-notification relative',
        'fi-inline' => $notification->isInline(),
        "fi-status-{$status}" => filled($status),
    ])
    wire:key="{{ $notification->getId() }}.notifications.{{ $notification->getId() }}"
    x-on:close-notification.window="if ($event.detail.id == '{{ $notification->getId() }}') close()"
>
    @if (filled($destinationUrl))
        <a
            href="{{ $destinationUrl }}"
            class="absolute inset-0 z-0 rounded-xl"
            aria-label="{{ $title }}"
        ></a>
    @endif

    {{ \Filament\Support\generate_icon_html(
        $icon,
        attributes: (new ComponentAttributeBag)
            ->color(IconComponent::class, $notification->getIconColor())
            ->class(['fi-no-notification-icon pointer-events-none relative z-10']),
        size: $notification->getIconSize(),
    ) }}

    <div class="fi-no-notification-main relative z-10">
        <div class="fi-no-notification-text pointer-events-none">
            @if (filled($title))
                <h3 class="fi-no-notification-title">
                    {{ $title }}
                </h3>
            @endif

            @if (filled($summary) || $itemsPreview->isNotEmpty() || $extraCount > 0)
                <div class="fi-no-notification-body space-y-3">
                    @if (filled($summary))
                        <p>{{ $summary }}</p>
                    @endif

                    @if ($itemsPreview->isNotEmpty())
                        <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300">
                            @foreach ($itemsPreview as $line)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70"></span>
                                    <span>{{ $line }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($extraCount > 0)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            +{{ number_format($extraCount) }} item lainnya. Klik notifikasi untuk membuka daftar lengkap.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if ($actions = $notification->getActions())
            <div class="fi-ac fi-no-notification-actions relative z-20">
                @foreach ($actions as $action)
                    {{ $action->toHtml() }}
                @endforeach
            </div>
        @endif
    </div>

    <button
        type="button"
        x-on:click.stop="close"
        class="fi-icon-btn fi-no-notification-close-btn relative z-20"
    >
        {{ \Filament\Support\generate_icon_html(Heroicon::XMark, alias: NotificationsIconAlias::NOTIFICATION_CLOSE_BUTTON) }}
    </button>
</div>
