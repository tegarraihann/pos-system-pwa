<x-filament-panels::page>
    @php($snapshot = $this->getSnapshot())

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Item Bermasalah</p>
                <p class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($snapshot['impacted_count']) }}</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
                <p class="text-sm text-red-700 dark:text-red-300">Stok Habis</p>
                <p class="mt-2 text-3xl font-semibold text-red-700 dark:text-red-200">{{ number_format($snapshot['out_count']) }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                <p class="text-sm text-amber-700 dark:text-amber-300">Stok Menipis</p>
                <p class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-200">{{ number_format($snapshot['low_count']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Filter Aktif</p>
                <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">
                    {{ match($this->status) {
                        'out' => 'Habis',
                        'low' => 'Menipis',
                        default => 'Semua',
                    } }}
                </p>
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Item Yang Perlu Diperhatikan
            </x-slot>

            <x-slot name="description">
                Klik item untuk membuka data terkait dan lakukan penyesuaian stok atau reminder sesuai kebutuhan.
            </x-slot>

            @if ($snapshot['items']->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                    Tidak ada item pada filter ini.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr class="text-left text-gray-600 dark:text-gray-300">
                                <th class="px-4 py-3 font-medium">Item</th>
                                <th class="px-4 py-3 font-medium">Tipe</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium text-right">Stok</th>
                                <th class="px-4 py-3 font-medium text-right">Reminder</th>
                                <th class="px-4 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-transparent">
                            @foreach ($snapshot['items'] as $item)
                                <tr class="text-gray-700 dark:text-gray-200">
                                    <td class="px-4 py-3 font-medium">{{ $item['name'] }}</td>
                                    <td class="px-4 py-3">{{ $item['type_label'] }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                            'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200' => $item['status'] === 'out',
                                            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' => $item['status'] === 'low',
                                        ])>
                                            {{ $item['status'] === 'out' ? 'Habis' : 'Menipis' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format((float) $item['stock'], 3, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format((float) $item['reminder_stock'], 3, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a
                                            href="{{ $item['detail_url'] }}"
                                            class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-xs font-medium text-white hover:bg-primary-500"
                                        >
                                            Buka Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
