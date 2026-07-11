@php $data = $this->getDigestData(); @endphp

<x-filament-widgets::widget>
    <p class="text-gray-400 dark:text-gray-500" style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.875rem;">
        Today's Priorities
    </p>

    @if(empty($data['items']))
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
             style="border-radius:.75rem;padding:2rem 1.5rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;background:#f0fdf4;border-radius:50%;margin-bottom:.875rem;">
                <x-heroicon-o-check-circle style="width:1.25rem;height:1.25rem;color:#22c55e;" />
            </div>
            <p class="text-gray-800 dark:text-gray-200" style="font-size:.9rem;font-weight:600;margin-bottom:.25rem;">You're on top of everything</p>
            <p class="text-gray-400 dark:text-gray-500" style="font-size:.8rem;">No overdue invoices, follow-ups, tasks, calendar gaps, or appointments right now.</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:.625rem;">
            @foreach($data['items'] as $item)
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
                 style="border-left:4px solid {{ $item['border_color'] }};border-radius:.75rem;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="min-width:0;">
                    <p class="text-gray-900 dark:text-white" style="font-size:.9rem;font-weight:600;margin-bottom:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $item['title'] }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-400" style="font-size:.78rem;">
                        {{ $item['subtitle'] }}
                    </p>
                </div>
                <div style="flex-shrink:0;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;justify-content:flex-end;">
                    @if(isset($item['action_method'], $item['action_id']))
                        <button type="button"
                                wire:click="{{ $item['action_method'] }}({{ $item['action_id'] }}{{ isset($item['action_value']) ? ", '" . $item['action_value'] . "'" : '' }})"
                                wire:loading.attr="disabled"
                                style="display:inline-block;padding:.375rem .875rem;border-radius:.5rem;font-size:.78rem;font-weight:600;background:{{ $item['action_color'] }};color:{{ $item['action_text'] }};text-decoration:none;white-space:nowrap;transition:opacity .15s;border:0;cursor:pointer;"
                                onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                            {{ $item['action_label'] }}
                        </button>
                    @else
                        <a href="{{ $item['action_url'] }}"
                           style="display:inline-block;padding:.375rem .875rem;border-radius:.5rem;font-size:.78rem;font-weight:600;background:{{ $item['action_color'] }};color:{{ $item['action_text'] }};text-decoration:none;white-space:nowrap;transition:opacity .15s;"
                           onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                            {{ $item['action_label'] }}
                        </a>
                    @endif

                    @foreach($item['secondary_actions'] ?? [] as $secondaryAction)
                        <button type="button"
                                wire:click="{{ $secondaryAction['method'] }}({{ $secondaryAction['id'] }}{{ isset($secondaryAction['value']) ? ", '" . $secondaryAction['value'] . "'" : '' }})"
                                wire:loading.attr="disabled"
                                style="display:inline-block;padding:.375rem .6rem;border-radius:.5rem;font-size:.76rem;font-weight:600;background:#f8fafc;color:#475569;text-decoration:none;white-space:nowrap;transition:opacity .15s;border:1px solid #e2e8f0;cursor:pointer;"
                                onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                            {{ $secondaryAction['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>
