@php
    $filterItems = [
        ['label' => 'Total Logs', 'value' => null, 'count' => $counts['all'], 'color' => 'blue'],
        ['label' => 'Pending', 'value' => 'pending', 'count' => $counts['pending'], 'color' => 'amber'],
        ['label' => 'Analyzed', 'value' => 'analyzed', 'count' => $counts['analyzed'], 'color' => 'emerald'],
        ['label' => 'Failed', 'value' => 'failed', 'count' => $counts['failed'], 'color' => 'rose'],
    ];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach($filterItems as $item)
        <a href="{{ $item['value'] ? '?status='.$item['value'] : '?' }}" 
           class="relative overflow-hidden group p-4 rounded-xl border transition-all duration-200 
           {{ request('status') === $item['value'] 
                ? 'bg-white dark:bg-slate-900 border-'.$item['color'].'-500 ring-2 ring-'.$item['color'].'-500/20 shadow-md' 
                : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-'.$item['color'].'-400 shadow-sm' }}">
            
            <div class="flex flex-col">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 group-hover:text-{{ $item['color'] }}-500 transition-colors">
                    {{ $item['label'] }}
                </span>
                <span class="text-2xl font-bold mt-1 text-slate-900 dark:text-white">
                    {{ number_format($item['count']) }}
                </span>
            </div>

            <div class="absolute -right-1.5 -bottom-1.5">
                <svg class="w-16 h-16 text-{{ $item['color'] }}-600" fill="currentColor" viewBox="0 0 24 24">
                    @if($item['label'] === 'Total Logs')
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    @elseif($item['label'] === 'Pending')
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    @elseif($item['label'] === 'Analyzed')
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    @elseif($item['label'] === 'Failed')
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                    @endif
                </svg>
            </div>
        </a>
    @endforeach
</div>