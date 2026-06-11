<div class="flex items-center justify-between bg-neutral-100 px-5 py-3">
    <div class="relative sm:static flex items-center gap-2">
        <div class="text-md font-semibold text-slate-900">
            {{ $lg->nama_pos }}
        </div>
        <div x-data="{ open: false }" class="sm:relative">
            <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                class="inline-flex h-8 w-8 items-center justify-center" aria-label="Info Logger">
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0 9.16667C0 4.10392 4.10392 0 9.16667 0C14.2294 0 18.3333 4.10392 18.3333 9.16667C18.3333 14.2294 14.2294 18.3333 9.16667 18.3333C4.10392 18.3333 0 14.2294 0 9.16667ZM7.79167 5.5C7.79167 5.13533 7.93653 4.78559 8.19439 4.52773C8.45226 4.26987 8.80199 4.125 9.16667 4.125H9.17583C9.54051 4.125 9.89024 4.26987 10.1481 4.52773C10.406 4.78559 10.5508 5.13533 10.5508 5.5V5.50917C10.5508 5.87384 10.406 6.22358 10.1481 6.48144C9.89024 6.7393 9.54051 6.88417 9.17583 6.88417H9.16667C8.80199 6.88417 8.45226 6.7393 8.19439 6.48144C7.93653 6.22358 7.79167 5.87384 7.79167 5.50917V5.5ZM9.16667 8.25C9.40978 8.25 9.64294 8.34658 9.81485 8.51849C9.98676 8.69039 10.0833 8.92355 10.0833 9.16667V12.8333C10.0833 13.0764 9.98676 13.3096 9.81485 13.4815C9.64294 13.6534 9.40978 13.75 9.16667 13.75C8.92355 13.75 8.69039 13.6534 8.51849 13.4815C8.34658 13.3096 8.25 13.0764 8.25 12.8333V9.16667C8.25 8.92355 8.34658 8.69039 8.51849 8.51849C8.69039 8.34658 8.92355 8.25 9.16667 8.25Z"
                        fill="#303481" />
                </svg>
            </button>
            <div x-show="open" x-transition.origin.top.left @click.outside="open = false"
                class="absolute left-0 z-[9999] mt-2 w-[320px] max-w-[calc(100vw-2.5rem)] sm:max-w-xs md:max-w-sm lg:max-w-[320px]" style="display: none;">
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl">
                    <div class="grid grid-cols-3 gap-0 border-b border-slate-200 bg-white text-xs font-semibold text-slate-700">
                        <div class="px-3 py-2">ID Logger</div>
                        <div class="col-span-2 px-3 py-2 text-right text-slate-900">{{ $lg->id_logger }}</div>
                    </div>
                    <div class="grid grid-cols-3 gap-0 border-b border-slate-200 bg-white text-xs font-semibold text-slate-700">
                        <div class="px-3 py-2">Status Logger</div>
                        <div class="col-span-2 flex items-center justify-end gap-2 px-3 py-2 text-slate-900">
                            <span>{{ $statusText }}</span>
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border {{ $badgeClass }}">
                                {!! $isOnline
                                    ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>'
                                    : '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>' !!}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-0 bg-white text-xs font-semibold text-slate-700">
                        <div class="px-3 py-2">Status SD Card</div>
                        <div class="col-span-2 flex items-center justify-end gap-2 px-3 py-2 text-slate-900">
                            <span>{{ $sdText }}</span>
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border {{ $sdClass }}">
                                {!! $isSdOk
                                    ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>'
                                    : '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>' !!}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold {{ $timeClass }}">
        <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
        <span>{{ $waktu }}</span>
    </div>
</div>
