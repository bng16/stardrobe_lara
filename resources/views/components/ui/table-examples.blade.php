{{-- Table Component Usage Examples --}}

{{-- Example 1: Basic Table with User Data --}}
<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-bold mb-4">Basic Table Example</h2>
        
        @php
            // Example data - in real usage, this would come from your controller
            $users = \App\Models\User::paginate(10);
            
            $userColumns = [
                ['key' => 'id', 'label' => 'ID', 'width' => '80px', 'align' => 'center'],
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Joined', 'format' => 'date', 'sortable' => true],
            ];
        @endphp
        
        <x-ui.table 
            :data="$users" 
            :columns="$userColumns"
            :sortable="true"
            current-sort="{{ request('sort') }}"
            current-direction="{{ request('direction', 'asc') }}"
            empty-message="No users found"
            :striped="true"
            :hover="true"
        />
    </div>

    {{-- Example 2: Product Table with Custom Rendering --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">Advanced Table with Custom Rendering</h2>
        
        @php
            // Example product data
            $products = \App\Models\Product::with(['creator.creatorShop', 'images'])
                ->withCount('bids')
                ->paginate(10);
            
            $productColumns = [
                [
                    'key' => 'title', 
                    'label' => 'Product', 
                    'sortable' => true,
                    'render' => function($product, $value) {
                        $image = $product->images->where('is_primary', true)->first();
                        $imageSrc = $image ? asset('storage/' . $image->image_path) : asset('images/placeholder.jpg');
                        
                        return '
                            <div class="flex items-center space-x-3">
                                <img src="' . $imageSrc . '" alt="' . e($value) . '" class="h-10 w-10 rounded-lg object-cover">
                                <div>
                                    <div class="font-medium">' . e($value) . '</div>
                                    <div class="text-sm text-muted-foreground">' . e($product->creator->creatorShop->shop_name ?? 'Unknown Shop') . '</div>
                                </div>
                            </div>
                        ';
                    }
                ],
                [
                    'key' => 'status', 
                    'label' => 'Status', 
                    'align' => 'center',
                    'sortable' => true,
                    'render' => function($product, $value) {
                        $colors = [
                            'active' => 'bg-green-100 text-green-800',
                            'sold' => 'bg-blue-100 text-blue-800',
                            'unsold' => 'bg-gray-100 text-gray-800',
                            'draft' => 'bg-yellow-100 text-yellow-800',
                        ];
                        
                        $colorClass = $colors[$value] ?? 'bg-gray-100 text-gray-800';
                        
                        return '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ' . $colorClass . '">' . ucfirst($value) . '</span>';
                    }
                ],
                ['key' => 'starting_bid', 'label' => 'Starting Bid', 'format' => 'currency', 'align' => 'right', 'sortable' => true],
                ['key' => 'bids_count', 'label' => 'Bids', 'align' => 'center', 'sortable' => true],
                ['key' => 'auction_end', 'label' => 'Ends', 'format' => 'datetime', 'sortable' => true],
                [
                    'key' => 'id', 
                    'label' => 'Actions', 
                    'align' => 'center',
                    'sortable' => false,
                    'render' => function($product, $value) {
                        return '
                            <div class="flex items-center justify-center space-x-2">
                                <a href="/admin/products/' . $product->id . '" class="text-blue-600 hover:text-blue-800">View</a>
                                <a href="/admin/products/' . $product->id . '/edit" class="text-green-600 hover:text-green-800">Edit</a>
                                <button onclick="deleteProduct(' . $product->id . ')" class="text-red-600 hover:text-red-800">Delete</button>
                            </div>
                        ';
                    }
                ],
            ];
        @endphp
        
        <x-ui.table 
            :data="$products" 
            :columns="$productColumns"
            :sortable="true"
            current-sort="{{ request('sort') }}"
            current-direction="{{ request('direction', 'asc') }}"
            empty-message="No products found"
            :hover="true"
        />
    </div>

    {{-- Example 3: Compact Table --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">Compact Table Example</h2>
        
        @php
            $bids = \App\Models\Bid::with(['user', 'product'])
                ->latest()
                ->paginate(15);
            
            $bidColumns = [
                ['key' => 'id', 'label' => '#', 'width' => '60px'],
                ['key' => 'user.name', 'label' => 'Bidder', 'sortable' => true],
                ['key' => 'product.title', 'label' => 'Product', 'sortable' => true],
                ['key' => 'amount', 'label' => 'Amount', 'format' => 'currency', 'align' => 'right', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Time', 'format' => 'datetime', 'sortable' => true],
            ];
        @endphp
        
        <x-ui.table 
            :data="$bids" 
            :columns="$bidColumns"
            :sortable="true"
            current-sort="{{ request('sort') }}"
            current-direction="{{ request('direction', 'asc') }}"
            empty-message="No bids found"
            :compact="true"
            :striped="true"
        />
    </div>

    {{-- Example 4: Loading State --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">Loading State Example</h2>
        
        <x-ui.table 
            :data="collect([])" 
            :columns="$userColumns"
            :loading="true"
        />
    </div>

    {{-- Example 5: Empty State --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">Empty State Example</h2>
        
        @php
            $emptyData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        @endphp
        
        <x-ui.table 
            :data="$emptyData" 
            :columns="$userColumns"
            empty-message="No data available at this time"
        />
    </div>
</div>

@push('scripts')
<script>
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        // Handle product deletion
        fetch(`/admin/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting product');
        });
    }
}
</script>
@endpush