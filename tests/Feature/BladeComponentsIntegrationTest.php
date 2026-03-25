<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class BladeComponentsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(10)->create();
    }

    public function test_components_work_together_in_complex_layouts()
    {
        $users = User::paginate(5);
        
        $view = $this->blade('
            <x-ui.card>
                <x-ui.card-header>
                    <h2>User Management</h2>
                    <x-ui.alert variant="info" title="Information">
                        This table shows all registered users.
                    </x-ui.alert>
                </x-ui.card-header>
                <x-ui.card-content>
                    <x-ui.table :data="$users" :columns="$columns" />
                    <div class="mt-4">
                        <x-ui.pagination :paginator="$users" />
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        ', [
            'users' => $users,
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email']
            ]
        ]);
        
        $view->assertSee('User Management');
        $view->assertSee('This table shows all registered users');
        $view->assertSee('Name');
        $view->assertSee('Email');
        $view->assertSee('Showing');
    }

    public function test_form_components_work_together()
    {
        $view = $this->blade('
            <form>
                <div class="space-y-4">
                    <x-ui.input name="name" placeholder="Enter your name" />
                    <x-ui.textarea name="description" placeholder="Enter description" />
                    <x-ui.select name="role" :options="$options" placeholder="Select role" />
                    <x-ui.button type="submit">Submit Form</x-ui.button>
                </div>
            </form>
        ', [
            'options' => [
                'admin' => 'Administrator',
                'user' => 'Regular User'
            ]
        ]);
        
        $view->assertSee('Enter your name');
        $view->assertSee('Enter description');
        $view->assertSee('Select role');
        $view->assertSee('Administrator');
        $view->assertSee('Submit Form');
    }

    public function test_loading_component_with_different_scenarios()
    {
        // Test loading with text
        $loadingView = $this->blade('<x-ui.loading text="Processing..." />');
        $loadingView->assertSee('Processing...');
        $loadingView->assertSee('role="status"', false);
        
        // Test loading without text
        $simpleView = $this->blade('<x-ui.loading />');
        $simpleView->assertSee('Loading', false);
        $simpleView->assertSee('aria-live="polite"', false);
        
        // Test loading with custom variant
        $dotsView = $this->blade('<x-ui.loading variant="dots" />');
        $dotsView->assertSee('animate-bounce');
    }

    public function test_button_component_edge_cases()
    {
        // Test button with complex content
        $complexView = $this->blade('
            <x-ui.button variant="outline" size="lg">
                <svg class="w-4 h-4 mr-2">...</svg>
                Download Report
            </x-ui.button>
        ');
        $complexView->assertSee('Download Report');
        $complexView->assertSee('border border-input');
        $complexView->assertSee('h-11 rounded-md px-8');
        
        // Test disabled button with href (should render as span)
        $disabledLinkView = $this->blade('<x-ui.button href="/test" disabled>Disabled Link</x-ui.button>');
        $disabledLinkView->assertSee('Disabled Link');
        $disabledLinkView->assertSee('aria-disabled="true"', false);
    }

    public function test_card_components_with_various_content()
    {
        $view = $this->blade('
            <x-ui.card class="max-w-md">
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold">Product Details</h3>
                    <p class="text-gray-600">View product information</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> Sample Product</p>
                        <p><strong>Price:</strong> $99.99</p>
                        <p><strong>Status:</strong> In Stock</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <x-ui.button size="sm">Edit</x-ui.button>
                        <x-ui.button variant="destructive" size="sm">Delete</x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        ');
        
        $view->assertSee('Product Details');
        $view->assertSee('Sample Product');
        $view->assertSee('$99.99');
        $view->assertSee('Edit');
        $view->assertSee('Delete');
        $view->assertSee('max-w-md');
    }

    public function test_table_component_with_complex_data()
    {
        $users = User::take(3)->get();
        
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'align' => 'center'],
            ['key' => 'name', 'label' => 'Full Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email Address', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Joined', 'format' => 'date']
        ];
        
        $view = $this->blade('
            <x-ui.table 
                :data="$users" 
                :columns="$columns" 
                :striped="true" 
                :hover="true"
                current-sort="name"
                current-direction="asc"
            />
        ', [
            'users' => $users,
            'columns' => $columns
        ]);
        
        $view->assertSee('Full Name');
        $view->assertSee('Email Address');
        $view->assertSee('text-center'); // Center alignment
        $view->assertSee('even:bg-muted/25'); // Striped
        $view->assertSee('hover:bg-muted/50'); // Hover
        $view->assertSee('aria-sort="ascending"', false); // Sorted column
    }

    public function test_alert_component_accessibility_and_variants()
    {
        // Test all variants with accessibility
        $variants = ['default', 'destructive', 'warning', 'success', 'info'];
        
        foreach ($variants as $variant) {
            $view = $this->blade("
                <x-ui.alert variant=\"{$variant}\" title=\"{$variant} Alert\" dismissible>
                    This is a {$variant} alert message.
                </x-ui.alert>
            ");
            
            $view->assertSee("{$variant} Alert");
            $view->assertSee("This is a {$variant} alert message");
            $view->assertSee('role="alert"', false);
            $view->assertSee('aria-live="polite"', false);
            $view->assertSee('<button', false); // Dismissible
        }
    }

    public function test_form_components_with_validation_states()
    {
        $view = $this->blade('
            <div class="space-y-4">
                <div>
                    <x-ui.input name="valid_field" value="Valid input" />
                </div>
                <div>
                    <x-ui.input name="error_field" value="Invalid input" error="true" />
                    <p class="text-red-600 text-sm mt-1">This field has an error</p>
                </div>
                <div>
                    <x-ui.textarea name="description" error="true" />
                    <p class="text-red-600 text-sm mt-1">Description is required</p>
                </div>
                <div>
                    <x-ui.select name="category" :options="$options" />
                </div>
            </div>
        ', [
            'options' => [
                '' => 'Select category...',
                'tech' => 'Technology',
                'design' => 'Design'
            ]
        ]);
        
        $view->assertSee('Valid input');
        $view->assertSee('Invalid input');
        $view->assertSee('This field has an error');
        $view->assertSee('Description is required');
        $view->assertSee('Select category...');
        $view->assertSee('Technology');
        $view->assertSee('border-destructive'); // Error state styling
    }

    public function test_components_handle_empty_and_null_values()
    {
        $view = $this->blade('
            <div>
                <x-ui.input name="empty_input" value="" />
                <x-ui.textarea name="empty_textarea"></x-ui.textarea>
                <x-ui.select name="empty_select" :options="[]" />
                <x-ui.button></x-ui.button>
                <x-ui.alert></x-ui.alert>
            </div>
        ');
        
        // Should render without errors
        $view->assertSee('name="empty_input"', false);
        $view->assertSee('name="empty_textarea"', false);
        $view->assertSee('name="empty_select"', false);
    }

    public function test_components_with_special_characters_and_html()
    {
        $view = $this->blade('
            <div>
                <x-ui.alert title="Alert with &quot;quotes&quot; &amp; symbols">
                    Content with <strong>HTML</strong> and &lt;script&gt; tags.
                </x-ui.alert>
                <x-ui.input name="special" value="Value with &quot;quotes&quot;" />
                <x-ui.button>Button with &lt;script&gt; content</x-ui.button>
            </div>
        ');
        
        // Should properly escape content
        $view->assertSee('Content with');
        $view->assertSee('HTML');
        $view->assertSee('Button with &lt;script&gt; content', false);
    }
}