@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);
        
        // Adjust range if we're near the beginning or end
        if ($currentPage <= 3) {
            $end = min($lastPage, 5);
        }
        if ($currentPage >= $lastPage - 2) {
            $start = max(1, $lastPage - 4);
        }
    @endphp
    
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            {{-- Mobile pagination --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground bg-background border border-input cursor-default leading-5 rounded-md">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" 
                   class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-foreground bg-background border border-input leading-5 rounded-md hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                    Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" 
                   class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-foreground bg-background border border-input leading-5 rounded-md hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                    Next
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-muted-foreground bg-background border border-input cursor-default leading-5 rounded-md">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-muted-foreground">
                    Showing
                    <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-md shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Previous">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-muted-foreground bg-background border border-input cursor-default rounded-l-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" 
                           rel="prev" 
                           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-muted-foreground bg-background border border-input rounded-l-md leading-5 hover:bg-muted focus:z-10 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors" 
                           aria-label="Previous">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- First Page --}}
                    @if($start > 1)
                        <a href="{{ $paginator->url(1) }}" 
                           class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-foreground bg-background border border-input leading-5 hover:bg-muted focus:z-10 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                            1
                        </a>
                        @if($start > 2)
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-muted-foreground bg-background border border-input cursor-default leading-5">
                                ...
                            </span>
                        @endif
                    @endif

                    {{-- Page Numbers --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $currentPage)
                            <span aria-current="page">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-primary-foreground bg-primary border border-primary cursor-default leading-5">
                                    {{ $page }}
                                </span>
                            </span>
                        @else
                            <a href="{{ $paginator->url($page) }}" 
                               class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-foreground bg-background border border-input leading-5 hover:bg-muted focus:z-10 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors" 
                               aria-label="Go to page {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    {{-- Last Page --}}
                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-muted-foreground bg-background border border-input cursor-default leading-5">
                                ...
                            </span>
                        @endif
                        <a href="{{ $paginator->url($lastPage) }}" 
                           class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-foreground bg-background border border-input leading-5 hover:bg-muted focus:z-10 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                            {{ $lastPage }}
                        </a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" 
                           rel="next" 
                           class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-muted-foreground bg-background border border-input rounded-r-md leading-5 hover:bg-muted focus:z-10 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors" 
                           aria-label="Next">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Next">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-muted-foreground bg-background border border-input cursor-default rounded-r-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif