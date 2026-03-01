@extends('db-sentinel::layouts.app')

@section('title', 'Query Logs')

@php
    $bgColors = [
        'GET'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'POST'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'PUT'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'DELETE' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    ];

    $statusColors = [
        'pending'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'analyzed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'failed'   => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    ];

    $severityColors = [
        'critical' => 'bg-red-100 text-red-700 border-red-200',
        'high' => 'bg-orange-100 text-orange-700 border-orange-200',
        'medium' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'low' => 'bg-blue-100 text-blue-700 border-blue-200',
        'default'  => 'bg-gray-100 text-gray-700 border-gray-200',
    ];
@endphp

@section('content')
    @include('db-sentinel::partials.filters')

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        @if($logs->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Origin</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Query</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Performance</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Suggestions</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-5 whitespace-nowrap align-top">
                            <div class="flex flex-col gap-1.5">
                                <span class="w-min px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $bgColors[$log->method] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate max-w-[180px]" title="{{ $log->url }}">
                                    {{ parse_url($log->url, PHP_URL_PATH) }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono tracking-tighter">
                                    {{ $log->connection }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="group relative">
                                <div class="bg-slate-900 dark:bg-black rounded-lg p-3 text-xs font-mono text-blue-300 dark:text-blue-400 leading-relaxed break-all line-clamp-2 group-hover:line-clamp-none transition-all border border-transparent dark:border-slate-800">
                                    {{ $log->sql }}
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 italic">
                                        {{ $log->caller['location'] ?? 'Internal' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-5 whitespace-nowrap align-top">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold {{ $log->execution_time > 50 ? 'text-rose-500 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }}">
                                    {{ $log->execution_time }} ms
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-semibold">Duration</span>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="flex flex-col gap-2">
                                <span class="w-min px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $statusColors[$log->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $log->status }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase">
                                    {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top text-[10px]">
                            <div class="flex flex-col items-center space-y-1">
                            @forelse($log->severityCounts as $level => $count)
                                <span class="px-2 py-0.5 rounded font-bold uppercase tracking-wider border {{ $severityColors[$level] }}">
                                    {{ strtoupper($level) }}: {{ $count }}
                                </span>
                            @empty
                                @if($log->status === 'analyzed')
                                    <span class="w-min px-2 py-0.5 rounded font-bold border bg-green-100 text-green-700 border-green-200">OPTIMIZED</span>
                                    <span class="text-xxs text-gray-400 mt-1 whitespace-nowrap">No suggestions found</span>
                                @elseif($log->status === 'pending')
                                    <span class="inline-flex items-center px-1 py-0.5 rounded font-normal bg-gray-100 text-gray-600 border border-gray-300 italic">
                                        Analysis Pending
                                    </span>
                                @endif
                            @endforelse
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <a href="{{ route('db-sentinel.logs.show', $log->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 dark:hover:text-white transition-all duration-300 group">
                                View Full Details
                                <svg class="w-3.5 h-3.5 ml-2 transform group-hover:rotate-45 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @else
        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center transition-colors dark:border-gray-700">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                No Sentinel Logs Found
            </h3>
            <p class="mx-auto mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                Database Sentinel hasn't captured any queries yet.
            </p>

            <div class="mt-6">
                <a href="{{ url()->full() }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:bg-blue-500 dark:hover:bg-blue-400">
                    Reload
                </a>
            </div>
        </div>
        @endif
    </div>
@endsection