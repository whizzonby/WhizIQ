@php $campaigns = $this->getCampaignStats(); @endphp

<x-filament-widgets::widget>
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
         style="border-radius:.75rem;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem;">
            <div>
                <p class="text-gray-400 dark:text-gray-500" style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.35rem;">
                    Campaign Performance
                </p>
                <p class="text-gray-900 dark:text-white" style="font-size:1rem;font-weight:700;">
                    Last 30 days
                </p>
            </div>
            <a href="{{ route('filament.dashboard.pages.email-composer-page', ['campaign' => 'rebooking']) }}"
               style="flex-shrink:0;display:inline-block;padding:.45rem .875rem;border-radius:.5rem;font-size:.78rem;font-weight:600;background:#eef2ff;color:#4338ca;text-decoration:none;white-space:nowrap;">
                Launch campaign
            </a>
            <a href="{{ route('filament.dashboard.pages.campaign-report-page') }}"
               style="flex-shrink:0;display:inline-block;padding:.45rem .875rem;border-radius:.5rem;font-size:.78rem;font-weight:600;background:#f8fafc;color:#334155;text-decoration:none;white-space:nowrap;">
                Full report
            </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;">
            @foreach($campaigns as $campaign)
                <div class="border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"
                     style="border-radius:.65rem;padding:1rem;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div>
                            <p class="text-gray-900 dark:text-white" style="font-size:.92rem;font-weight:700;">
                                {{ $campaign['label'] }}
                            </p>
                            <p class="text-gray-500 dark:text-gray-400" style="font-size:.75rem;margin-top:.15rem;">
                                {{ $campaign['description'] }}
                            </p>
                        </div>
                        <a href="{{ $campaign['launch_url'] }}"
                           style="font-size:.72rem;font-weight:600;color:#2563eb;text-decoration:none;white-space:nowrap;">
                            Open
                        </a>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.875rem;">
                        <div>
                            <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">Sent</p>
                            <p class="text-gray-900 dark:text-white" style="font-size:1.1rem;font-weight:800;">{{ $campaign['sent'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">Booked</p>
                            <p class="text-gray-900 dark:text-white" style="font-size:1.1rem;font-weight:800;">{{ $campaign['booked'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">Rate</p>
                            <p class="text-gray-900 dark:text-white" style="font-size:1.1rem;font-weight:800;">{{ $campaign['conversion'] }}%</p>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;margin-top:.875rem;padding-top:.875rem;border-top:1px solid rgba(148,163,184,.22);">
                        <div>
                            <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">Value</p>
                            <p class="text-gray-900 dark:text-white" style="font-size:.95rem;font-weight:800;">{{ $campaign['formatted_projected_revenue'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">Collected</p>
                            <p class="text-gray-900 dark:text-white" style="font-size:.95rem;font-weight:800;">{{ $campaign['formatted_collected_revenue'] }}</p>
                        </div>
                    </div>

                    <div style="margin-top:.875rem;display:grid;gap:.35rem;">
                        <p class="text-gray-500 dark:text-gray-400" style="font-size:.75rem;">
                            Service:
                            <span class="text-gray-900 dark:text-white" style="font-weight:700;">
                                {{ $campaign['top_service']['name'] ?? 'No bookings yet' }}
                            </span>
                            @if($campaign['top_service'] ?? null)
                                <span>({{ $campaign['top_service']['count'] }})</span>
                            @endif
                        </p>
                        <p class="text-gray-500 dark:text-gray-400" style="font-size:.75rem;">
                            Best day:
                            <span class="text-gray-900 dark:text-white" style="font-weight:700;">
                                {{ $campaign['best_day']['label'] ?? 'No bookings yet' }}
                            </span>
                            @if($campaign['best_day'] ?? null)
                                <span>({{ $campaign['best_day']['count'] }})</span>
                            @endif
                        </p>
                    </div>

                    <div style="margin-top:1rem;padding-top:.875rem;border-top:1px solid rgba(148,163,184,.22);">
                        <div style="display:flex;align-items:flex-end;gap:.25rem;height:3.25rem;">
                            @foreach($campaign['trend'] as $day)
                                <div title="{{ $day['label'] }}: {{ $day['count'] }} booking{{ $day['count'] === 1 ? '' : 's' }}"
                                     style="flex:1;min-width:.35rem;height:{{ $day['height'] }}px;border-radius:.25rem .25rem 0 0;background:{{ $day['count'] > 0 ? '#2563eb' : '#e5e7eb' }};">
                                </div>
                            @endforeach
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:.35rem;">
                            <span class="text-gray-400 dark:text-gray-500" style="font-size:.65rem;">14 days ago</span>
                            <span class="text-gray-400 dark:text-gray-500" style="font-size:.65rem;">Today</span>
                        </div>
                    </div>

                    <div style="margin-top:1rem;display:grid;gap:.5rem;">
                        <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;font-weight:700;text-transform:uppercase;">
                            Latest bookings
                        </p>

                        @forelse($campaign['recent_bookings'] as $booking)
                            <div style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.5rem;align-items:start;padding:.5rem 0;border-top:1px solid rgba(148,163,184,.16);">
                                <div style="min-width:0;">
                                    <p class="text-gray-900 dark:text-white" style="font-size:.78rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $booking['client'] }}
                                    </p>
                                    <p class="text-gray-500 dark:text-gray-400" style="font-size:.72rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $booking['service'] }} - {{ $booking['appointment_at'] }}
                                    </p>
                                </div>
                                <div style="text-align:right;">
                                    <p class="text-gray-900 dark:text-white" style="font-size:.78rem;font-weight:800;">{{ $booking['value'] }}</p>
                                    <p class="text-gray-400 dark:text-gray-500" style="font-size:.68rem;">{{ $booking['status'] }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400" style="font-size:.75rem;padding-top:.25rem;">
                                No campaign bookings yet.
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
