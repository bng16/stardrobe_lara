@props([
    'data' => null,
    'columns' => [],
    'sortable' => true,
    'currentSort' => null,
    'currentDirection' => 'asc',
    'emptyMessage' => 'No data available',
    'loading' => false,
    'striped' => false,
    'hover' => true,
    'compact' => false
])

@php
// Base classes for table container
$containerClasses = 'relative overflow-hidden rounded-lg border bg-card';

// Table classes
$tableClasses = 'w-full caption-bottom text-sm';

// Row classes
$rowClasses = 'border-b transition-colors';
if ($hover) {
    $rowClasses .= ' hover:bg-muted/50';
}
if ($striped) {
    $rowClasses .= ' even:bg-muted/25';
}

// Cell padding classes
$cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';

// Prepare attributes
$filteredAttributes = $attributes->except(['class']);
$containerClass = trim($containerClasses . ' ' . ($attributes->get('class') ?? ''));
@endphp

<div class="{{ $containerClass }}" {{ $filteredAttributes }}>
    @if($loading)
        {{-- Loading state --}}
        <div class="flex items-center justify-center p-8">
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                <span class="text-muted-foreground">Loading...</span>
            </div>
        </div>
    @elseif($data && $data->count() > 0)
        {{-- Table with data --}}
        <div class="overflow-x-auto">
            <table class="{{ $tableClasses }}">
                @if(!empty($columns))
                    <thead class="bg-muted/50">
                        <tr class="border-b">
                            @foreach($columns as $column)
                                @php
                                    $key = $column['key'] ?? '';
                                    $label = $column['label'] ?? ucfirst($key);
                                    $sortableColumn = ($column['sortable'] ?? true) && $sortable;
                                    $width = $column['width'] ?? null;
                                    $align = $column['align'] ?? 'left';
                                    
                                    $headerClasses = $cellPadding . ' text-left font-medium text-muted-foreground';
                                    
                                    if ($align === 'center') {
                                        $headerClasses .= ' text-center';
                                    } elseif ($align === 'right') {
                                        $headerClasses .= ' text-right';
                                    }
                                    
                                    if ($sortableColumn) {
                                        $headerClasses .= ' cursor-pointer select-none hover:text-foreground';
                                    }
                                    
                                    $isSorted = $currentSort === $key;
                                    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
                                @endphp
                                
                                <th class="{{ $headerClasses }}"
                                    @if($width) style="width: {{ $width }}" @endif
                                    @if($sortableColumn)
                                        role="button"
                                        tabindex="0"
                                        aria-sort="@if($isSorted){{ $currentDirection === 'asc' ? 'ascending' : 'descending' }}@else none @endif"
                                        onclick="handleSort('{{ $key }}', '{{ $nextDirection }}')"
                                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); handleSort('{{ $key }}', '{{ $nextDirection }}'); }"
                                    @endif>
                                    <div class="flex items-center gap-2">
                                        <span>{{ $label }}</span>
                                        @if($sortableColumn)
                                            <div class="flex flex-col">
                                                <svg class="h-3 w-3 {{ $isSorted && $currentDirection === 'asc' ? 'text-foreground' : 'text-muted-foreground/50' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="h-3 w-3 -mt-1 {{ $isSorted && $currentDirection === 'desc' ? 'text-foreground' : 'text-muted-foreground/50' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                
                <tbody>
                    @foreach($data as $row)
                        <tr class="{{ $rowClasses }}">
                            @if(!empty($columns))
                                @foreach($columns as $column)
                                    @php
                                        $key = $column['key'] ?? '';
                                        $align = $column['align'] ?? 'left';
                                        $format = $column['format'] ?? null;
                                        
                                        $cellClasses = $cellPadding;
                                        
                                        if ($align === 'center') {
                                            $cellClasses .= ' text-center';
                                        } elseif ($align === 'right') {
                                            $cellClasses .= ' text-right';
                                        }
                                        
                                        // Get the value using dot notation support
                                        $value = data_get($row, $key);
                                        
                                        // Apply formatting if specified
                                        if ($format && $value !== null) {
                                            $value = match($format) {
                                                'currency' => '$' . number_format($value, 2),
                                                'date' => \Carbon\Carbon::parse($value)->format('M j, Y'),
                                                'datetime' => \Carbon\Carbon::parse($value)->format('M j, Y g:i A'),
                                                'number' => number_format($value),
                                                default => $value
                                            };
                                        }
                                    @endphp
                                    
                                    <td class="{{ $cellClasses }}">
                                        @if(isset($column['render']))
                                            {!! $column['render']($row, $value) !!}
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            @else
                                {{-- Fallback: render all attributes if no columns defined --}}
                                @foreach($row->toArray() as $key => $value)
                                    <td class="{{ $cellPadding }}">{{ $value ?? '-' }}</td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(method_exists($data, 'links'))
            <div class="border-t bg-muted/25 px-4 py-3">
                {{ $data->links('components.ui.pagination') }}
            </div>
        @endif
    @else
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center p-8 text-center">
            <svg class="h-12 w-12 text-muted-foreground/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-muted-foreground mb-1">No Data</h3>
            <p class="text-sm text-muted-foreground">{{ $emptyMessage }}</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function handleSort(column, direction) {
    // Get current URL and update sort parameters
    const url = new URL(window.location);
    url.searchParams.set('sort', column);
    url.searchParams.set('direction', direction);
    url.searchParams.delete('page'); // Reset to first page when sorting
    
    // Navigate to the new URL
    window.location.href = url.toString();
}

// Keyboard navigation for sortable headers
document.addEventListener('DOMContentLoaded', function() {
    const sortableHeaders = document.querySelectorAll('th[role="button"]');
    
    sortableHeaders.forEach(header => {
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>
@endpush