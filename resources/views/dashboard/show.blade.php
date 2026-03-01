@php
    $severityConfig = [
        'critical' => [
            'bg' => 'bg-red-100 dark:bg-red-950/40',
            'border' => 'border-red-300 dark:border-red-900',
            'text' => 'text-red-900 dark:text-red-200',
            'icon' => 'text-red-600',
        ],
        'high' => [
            'bg' => 'bg-rose-50 dark:bg-rose-900/20',
            'border' => 'border-rose-200 dark:border-rose-800',
            'text' => 'text-rose-800 dark:text-rose-300',
            'icon' => 'text-rose-500',
        ],
        'medium' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'border' => 'border-amber-200 dark:border-amber-800',
            'text' => 'text-amber-800 dark:text-amber-300',
            'icon' => 'text-amber-500',
        ],
        'low' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'text' => 'text-blue-800 dark:text-blue-300',
            'icon' => 'text-blue-500',
        ],
    ];

    $severityConfig = [
        'critical' => [
            'bg'     => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'text'   => 'text-red-800 dark:text-red-300',
            'icon'   => 'text-red-500',
        ],
        'high' => [
            'bg'     => 'bg-orange-50 dark:bg-orange-900/20',
            'border' => 'border-orange-200 dark:border-orange-800',
            'text'   => 'text-orange-800 dark:text-orange-300',
            'icon'   => 'text-orange-500',
        ],
        'medium' => [
            'bg'     => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'text'   => 'text-yellow-800 dark:text-yellow-300',
            'icon'   => 'text-yellow-500',
        ],
        'low' => [
            'bg'     => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'text'   => 'text-blue-800 dark:text-blue-300',
            'icon'   => 'text-blue-500',
        ],
        'default' => [
            'bg'     => 'bg-gray-50 dark:bg-gray-900/20',
            'border' => 'border-gray-200 dark:border-gray-800',
            'text'   => 'text-gray-800 dark:text-gray-300',
            'icon'   => 'text-gray-500',
        ],
    ];
@endphp
@extends('db-sentinel::layouts.app')
@section('title', "Log #{$log->id}")

@section('content')
    <nav class="mb-6">
        <a href="{{ route('db-sentinel.logs.index') }}" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">
            &larr; Return to Dashboard
        </a>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-lg font-bold mb-4">Query Context</h3>
                <dl class="space-y-6">
                    <div>
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Execution Time</dt>
                        <dd class="text-2xl font-black text-blue-600">{{ $log->execution_time }}ms</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Method & URL</dt>
                        <dd class="text-sm font-medium break-all mt-1">
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] mr-1">{{ $log->method }}</span>
                            {{ $log->url }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Caller Location</dt>
                        <dd class="text-xs font-mono font-bold  mt-1 break-all">
                            {{ $log->caller['location'] ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900 rounded-xl p-6 shadow-lg border border-slate-800">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Raw SQL Query</span>
                    <button onclick="navigator.clipboard.writeText(`{{ $log->sql }}`)" class="text-[10px] text-blue-400 hover:text-blue-300">Copy SQL</button>
                </div>
                <code class="text-blue-300 font-mono text-sm leading-relaxed block whitespace-pre-wrap">{{ $log->sql }}</code>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold">Execution Plan (EXPLAIN)</h3>
                    @if(!empty($log->explanation))
                    <span class="text-xs text-slate-500">
                        {{ count($log->explanation) }} rows analyzed
                    </span>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    @if(!empty($log->explanation))
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                @foreach(array_keys($log->explanation[0]) as $key)
                                    <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-tighter">{{ $key }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($log->explanation as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                                @foreach($row as $value)
                                    <td class="px-4 py-3 {{ in_array($value, ['Using filesort', 'ALL']) ? 'text-rose-500 font-bold' : '' }}">
                                        {{ $value ?? 'NULL' }}
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/50 rounded-b-xl p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 mb-4">
                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <h3 class="text-sm font-bold text-amber-900 dark:text-amber-400">Analysis Pending</h3>

                        <p class="text-xs text-amber-700 dark:text-amber-500/80 mt-1 max-w-xs mx-auto">
                            Sentinel is currently performing an EXPLAIN analysis on this query. Results will appear shortly.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            @if($log->suggestions)
            <div class="space-y-4">
                <h3 class="text-slate-900 dark:text-slate-100 font-bold flex items-center px-1">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Performance Suggestions
                </h3>

                @foreach($log->suggestions as $suggestion)
                    @php
                        // Convert to object if it's an array for easier access
                        $suggestion = (object) $suggestion;

                        $style = $severityConfig[$suggestion->severity ?? 'low'] ?? $severityConfig['low'];
                    @endphp

                    <div class="flex items-start p-4 rounded-xl border {{ $style['bg'] }} {{ $style['border'] }} shadow-sm">
                        <div class="flex-shrink-0 mt-0.5">
                            @if(($suggestion->severity ?? '') === 'high')
                                <svg class="w-5 h-5 {{ $style['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 {{ $style['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>

                        <div class="ml-3">
                            <h4 class="text-sm font-bold {{ $style['text'] }}">
                                {{ $suggestion->title }}
                            </h4>
                            <p class="text-xs mt-1 opacity-90 {{ $style['text'] }}">
                                {{ $suggestion->message }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            @elseif(!empty($log->explanation))
            <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-900/50 rounded-xl p-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-400">Query Optimized</h3>
                <p class="text-xs text-emerald-700 dark:text-emerald-500/80 mt-1 max-w-xs mx-auto">
                    Sentinel analyzed this execution and found no structural bottlenecks or missing indexes.
                </p>
            </div>
            @endif
        </div>
    </div>
@endsection