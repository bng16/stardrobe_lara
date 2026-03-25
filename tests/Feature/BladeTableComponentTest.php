<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Product;

class BladeTableComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        User::factory()->count(5)->create();
        Product::factory()->count(10)->create();
    }

    /** @test */
    public function it_renders_table_with_basic_data()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('ID');
        $view->assertSee('Name');
        $view->assertSee('Email');
        $view->assertSeeInOrder(['ID', 'Name', 'Email']);
    }

    /** @test */
    public function it_renders_empty_state_when_no_data()
    {
        $emptyData = new LengthAwarePaginator([], 0, 10);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
        ];

        $view = $this->blade('<x-ui.table :data="$emptyData" :columns="$columns" empty-message="No users found" />', [
            'emptyData' => $emptyData,
            'columns' => $columns
        ]);

        $view->assertSee('No Data');
        $view->assertSee('No users found');
    }

    /** @test */
    public function it_renders_loading_state()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" :loading="true" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('Loading...');
        $view->assertDontSee('ID');
        $view->assertDontSee('Name');
    }

    /** @test */
    public function it_renders_sortable_headers_with_indicators()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => false],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" current-sort="name" current-direction="asc" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        // Check for sortable headers
        $view->assertSee('role="button"', false);
        $view->assertSee('cursor-pointer');
        
        // Check for sort indicators (SVG arrows)
        $view->assertSee('<svg', false);
    }

    /** @test */
    public function it_applies_column_formatting()
    {
        // Create a product with a known price
        $product = Product::factory()->create(['reserve_price' => 1234.56]);
        $products = new LengthAwarePaginator([$product], 1, 10);
        
        $columns = [
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'reserve_price', 'label' => 'Reserve Price', 'format' => 'currency'],
            ['key' => 'created_at', 'label' => 'Created', 'format' => 'date'],
        ];

        $view = $this->blade('<x-ui.table :data="$products" :columns="$columns" />', [
            'products' => $products,
            'columns' => $columns
        ]);

        $view->assertSee('$1,234.56');
        $view->assertSee($product->created_at->format('M j, Y'));
    }

    /** @test */
    public function it_handles_custom_column_alignment()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'align' => 'center'],
            ['key' => 'name', 'label' => 'Name', 'align' => 'left'],
            ['key' => 'email', 'label' => 'Email', 'align' => 'right'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('text-center');
        $view->assertSee('text-right');
    }

    /** @test */
    public function it_renders_pagination_when_data_is_paginated()
    {
        // Create more users than the page size
        User::factory()->count(25)->create();
        $users = User::paginate(10);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        // Should show pagination info
        $view->assertSee('Showing');
        $view->assertSee('results');
        
        // Should show page numbers if there are multiple pages
        if ($users->lastPage() > 1) {
            $view->assertSee('1');
        }
    }

    /** @test */
    public function it_applies_striped_and_hover_classes()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" :striped="true" :hover="true" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('even:bg-muted/25');
        $view->assertSee('hover:bg-muted/50');
    }

    /** @test */
    public function it_handles_compact_mode()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" :compact="true" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('px-3 py-2');
    }

    /** @test */
    public function it_handles_dot_notation_in_column_keys()
    {
        // Create users with related data
        $users = User::factory()->count(3)->create();
        
        // Assuming User has a profile relationship or similar
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'created_at.date', 'label' => 'Date Only'], // Test dot notation
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        $view->assertSee('ID');
        $view->assertSee('Name');
        $view->assertSee('Date Only');
    }

    /** @test */
    public function it_includes_sort_javascript_functionality()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        // Check for JavaScript function
        $view->assertSee('handleSort');
        $view->assertSee('onclick=');
        $view->assertSee('onkeydown=');
    }

    /** @test */
    public function it_handles_accessibility_attributes()
    {
        $users = User::paginate(5);
        
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'sortable' => true],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ];

        $view = $this->blade('<x-ui.table :data="$users" :columns="$columns" current-sort="name" current-direction="asc" />', [
            'users' => $users,
            'columns' => $columns
        ]);

        // Check for accessibility attributes - they should be present in the HTML
        $view->assertSee('role="button"', false);
        $view->assertSee('tabindex="0"', false);
        $view->assertSee('aria-sort="ascending"', false);
    }
}