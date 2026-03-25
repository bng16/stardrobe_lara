<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeComponentAccessibilityTest extends TestCase
{
    /**
     * Test Button component accessibility features.
     */
    public function test_button_component_accessibility()
    {
        // Test basic button accessibility
        $view = $this->blade('<x-ui.button>Click me</x-ui.button>');
        
        // Check focus management
        $view->assertSee('focus-visible:outline-none', false);
        $view->assertSee('focus-visible:ring-2', false);
        $view->assertSee('focus-visible:ring-ring', false);
        $view->assertSee('focus-visible:ring-offset-2', false);
        
        // Check disabled state accessibility
        $disabledView = $this->blade('<x-ui.button disabled>Disabled</x-ui.button>');
        $disabledView->assertSee('disabled', false);
        $disabledView->assertSee('disabled:pointer-events-none', false);
        $disabledView->assertSee('disabled:opacity-50', false);
        
        // Test link button accessibility
        $linkView = $this->blade('<x-ui.button href="/test" disabled>Disabled Link</x-ui.button>');
        $linkView->assertSee('aria-disabled="true"', false);
        $linkView->assertSee('tabindex="-1"', false);
        
        // Test button with ARIA attributes
        $ariaView = $this->blade('<x-ui.button aria-label="Save document" aria-describedby="save-help">Save</x-ui.button>');
        $ariaView->assertSee('aria-label="Save document"', false);
        $ariaView->assertSee('aria-describedby="save-help"', false);
    }

    /**
     * Test Card component accessibility features.
     */
    public function test_card_component_accessibility()
    {
        // Test card with proper semantic structure
        $view = $this->blade('
            <x-ui.card role="article" aria-labelledby="card-title">
                <x-ui.card-header>
                    <h2 id="card-title">Card Title</h2>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p>Card content</p>
                </x-ui.card-content>
            </x-ui.card>
        ');
        
        $view->assertSee('role="article"', false);
        $view->assertSee('aria-labelledby="card-title"', false);
        $view->assertSee('id="card-title"', false);
        
        // Test card with custom accessibility attributes
        $customView = $this->blade('<x-ui.card aria-label="Statistics card" tabindex="0">Content</x-ui.card>');
        $customView->assertSee('aria-label="Statistics card"', false);
        $customView->assertSee('tabindex="0"', false);
    }

    /**
     * Test Form component accessibility features.
     */
    public function test_form_components_accessibility()
    {
        // Test input with proper labeling
        $inputView = $this->blade('
            <label for="username">Username</label>
            <x-ui.input id="username" name="username" required aria-describedby="username-help" />
            <div id="username-help">Enter your username</div>
        ');
        
        $inputView->assertSee('id="username"', false);
        $inputView->assertSee('required', false);
        $inputView->assertSee('aria-describedby="username-help"', false);
        
        // Test input error state accessibility
        $errorView = $this->blade('
            <label for="email">Email</label>
            <x-ui.input id="email" name="email" error="true" aria-describedby="email-error" />
            <div id="email-error" role="alert">Invalid email format</div>
        ');
        
        $errorView->assertSee('border-destructive', false);
        $errorView->assertSee('focus-visible:ring-destructive', false);
        $errorView->assertSee('aria-describedby="email-error"', false);
        $errorView->assertSee('role="alert"', false);
        
        // Test textarea accessibility
        $textareaView = $this->blade('
            <label for="description">Description</label>
            <x-ui.textarea id="description" name="description" aria-label="Product description" />
        ');
        
        $textareaView->assertSee('id="description"', false);
        $textareaView->assertSee('aria-label="Product description"', false);
        
        // Test select accessibility
        $selectView = $this->blade('
            <label for="category">Category</label>
            <x-ui.select id="category" name="category" :options="[\'option1\' => \'Option 1\']" aria-required="true" />
        ', ['options' => ['option1' => 'Option 1']]);
        
        $selectView->assertSee('id="category"', false);
        $selectView->assertSee('aria-required="true"', false);
    }

    /**
     * Test Alert component accessibility features.
     */
    public function test_alert_component_accessibility()
    {
        // Test basic alert accessibility
        $view = $this->blade('<x-ui.alert variant="success" title="Success">Operation completed</x-ui.alert>');
        
        $view->assertSee('role="alert"', false);
        $view->assertSee('aria-live="polite"', false);
        
        // Test dismissible alert accessibility
        $dismissibleView = $this->blade('<x-ui.alert variant="warning" dismissible="true">Warning message</x-ui.alert>');
        
        $dismissibleView->assertSee('role="alert"', false);
        $dismissibleView->assertSee('aria-label="Close alert"', false);
        $dismissibleView->assertSee('focus:outline-none', false);
        $dismissibleView->assertSee('focus:ring-2', false);
        
        // Test different alert variants for proper color contrast
        $variants = ['default', 'destructive', 'warning', 'success', 'info'];
        foreach ($variants as $variant) {
            $variantView = $this->blade("<x-ui.alert variant=\"{$variant}\">Test message</x-ui.alert>");
            $variantView->assertSee('role="alert"', false);
            $variantView->assertSee('aria-live="polite"', false);
        }
    }

    /**
     * Test Table component accessibility features.
     */
    public function test_table_component_accessibility()
    {
        $columns = [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => false]
        ];
        
        $data = collect([
            (object)['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'Active'],
            (object)['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'Inactive']
        ]);
        
        $view = $this->blade('<x-ui.table :data="$data" :columns="$columns" currentSort="name" currentDirection="asc" />', [
            'data' => $data,
            'columns' => $columns
        ]);
        
        // Check table structure
        $view->assertSee('<table', false);
        $view->assertSee('<thead', false);
        $view->assertSee('<tbody', false);
        
        // Check sortable header accessibility
        $view->assertSee('role="button"', false);
        $view->assertSee('tabindex="0"', false);
        $view->assertSee('aria-sort="ascending"', false);
        
        // Check keyboard navigation support
        $view->assertSee('onkeydown="if(event.key === \'Enter\' || event.key === \' \')', false);
        
        // Test empty table accessibility
        $emptyView = $this->blade('<x-ui.table :data="$emptyData" :columns="$columns" />', [
            'emptyData' => collect(),
            'columns' => $columns
        ]);
        
        $emptyView->assertSee('No Data');
        $emptyView->assertSee('No data available');
    }

    /**
     * Test Modal component accessibility features.
     */
    public function test_modal_component_accessibility()
    {
        // Test basic modal accessibility
        $view = $this->blade('<x-ui.modal id="test_modal" title="Test Modal" description="Modal description">Modal content</x-ui.modal>');
        
        // Check ARIA attributes
        $view->assertSee('role="dialog"', false);
        $view->assertSee('aria-modal="true"', false);
        $view->assertSee('aria-labelledby', false);
        $view->assertSee('aria-describedby', false);
        
        // Check close button accessibility
        $view->assertSee('aria-label="Close modal"', false);
        
        // Test modal without description
        $noDescView = $this->blade('<x-ui.modal id="simple_modal" title="Simple Modal">Content</x-ui.modal>');
        $noDescView->assertSee('aria-labelledby', false);
        $noDescView->assertDontSee('aria-describedby', false);
        
        // Test non-dismissible modal
        $nonDismissibleView = $this->blade('<x-ui.modal id="locked_modal" title="Locked" :dismissible="false">Content</x-ui.modal>');
        $nonDismissibleView->assertSee('data-dismissible="false"', false);
        $nonDismissibleView->assertDontSee('aria-label="Close modal"', false);
    }

    /**
     * Test Dropdown component accessibility features.
     */
    public function test_dropdown_component_accessibility()
    {
        $view = $this->blade('
            <x-ui.dropdown id="test_dropdown">
                <x-slot name="trigger">
                    <button>Open Menu</button>
                </x-slot>
                <x-ui.dropdown-item href="/profile">Profile</x-ui.dropdown-item>
                <x-ui.dropdown-item href="/settings">Settings</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        
        // Check trigger accessibility
        $view->assertSee('role="button"', false);
        $view->assertSee('aria-haspopup="true"', false);
        $view->assertSee('aria-expanded="false"', false);
        $view->assertSee('tabindex="0"', false);
        
        // Check menu accessibility
        $view->assertSee('role="menu"', false);
        $view->assertSee('aria-orientation="vertical"', false);
        
        // Test disabled dropdown
        $disabledView = $this->blade('
            <x-ui.dropdown id="disabled_dropdown" disabled="true">
                <x-slot name="trigger">
                    <button>Disabled Menu</button>
                </x-slot>
                <x-ui.dropdown-item>Item</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        
        $disabledView->assertSee('aria-disabled="true"', false);
        $disabledView->assertSee('tabindex="-1"', false);
        $disabledView->assertSee('cursor-not-allowed', false);
    }

    /**
     * Test Loading component accessibility features.
     */
    public function test_loading_component_accessibility()
    {
        // Test basic loading component
        $view = $this->blade('<x-ui.loading text="Loading data" />');
        
        $view->assertSee('role="status"', false);
        $view->assertSee('aria-live="polite"', false);
        $view->assertSee('aria-label="Loading: Loading data"', false);
        $view->assertSee('class="sr-only"', false);
        
        // Test loading without text
        $noTextView = $this->blade('<x-ui.loading />');
        $noTextView->assertSee('aria-label="Loading"', false);
        $noTextView->assertSee('class="sr-only"', false);
        $noTextView->assertSee('Loading', false);
        
        // Test different variants
        $variants = ['spinner', 'dots', 'bars'];
        foreach ($variants as $variant) {
            $variantView = $this->blade("<x-ui.loading variant=\"{$variant}\" text=\"Loading\" />");
            $variantView->assertSee('role="status"', false);
            $variantView->assertSee('aria-hidden="true"', false);
        }
        
        // Test hidden loading component
        $hiddenView = $this->blade('<x-ui.loading :show="false" />');
        $hiddenView->assertDontSee('role="status"', false);
    }

    /**
     * Test keyboard navigation support across components.
     */
    public function test_keyboard_navigation_support()
    {
        // Test button keyboard support
        $buttonView = $this->blade('<x-ui.button onclick="handleClick()">Click me</x-ui.button>');
        $buttonView->assertSee('type="button"', false);
        
        // Test link button keyboard support
        $linkView = $this->blade('<x-ui.button href="/test">Link</x-ui.button>');
        $linkView->assertSee('href="/test"', false);
        
        // Test form elements keyboard support
        $formView = $this->blade('
            <x-ui.input name="test" onkeydown="handleKeydown(event)" />
            <x-ui.textarea name="description" onkeydown="handleKeydown(event)" />
            <x-ui.select name="category" :options="[]" onkeydown="handleKeydown(event)" />
        ');
        
        $formView->assertSee('onkeydown="handleKeydown(event)"', false);
    }

    /**
     * Test focus management and visual indicators.
     */
    public function test_focus_management()
    {
        // Test button focus styles
        $buttonView = $this->blade('<x-ui.button>Focus me</x-ui.button>');
        $buttonView->assertSee('focus-visible:outline-none', false);
        $buttonView->assertSee('focus-visible:ring-2', false);
        $buttonView->assertSee('focus-visible:ring-ring', false);
        $buttonView->assertSee('focus-visible:ring-offset-2', false);
        
        // Test input focus styles
        $inputView = $this->blade('<x-ui.input name="test" />');
        $inputView->assertSee('focus-visible:outline-none', false);
        $inputView->assertSee('focus-visible:ring-2', false);
        $inputView->assertSee('focus-visible:ring-ring', false);
        $inputView->assertSee('focus-visible:ring-offset-2', false);
        
        // Test alert close button focus
        $alertView = $this->blade('<x-ui.alert dismissible="true">Alert</x-ui.alert>');
        $alertView->assertSee('focus:outline-none', false);
        $alertView->assertSee('focus:ring-2', false);
        $alertView->assertSee('focus:ring-ring', false);
        $alertView->assertSee('focus:ring-offset-2', false);
    }

    /**
     * Test screen reader compatibility.
     */
    public function test_screen_reader_compatibility()
    {
        // Test loading component screen reader text
        $loadingView = $this->blade('<x-ui.loading text="Processing" />');
        $loadingView->assertSee('class="sr-only"', false);
        $loadingView->assertSee('Processing', false);
        
        // Test alert live regions
        $alertView = $this->blade('<x-ui.alert variant="error">Error occurred</x-ui.alert>');
        $alertView->assertSee('aria-live="polite"', false);
        $alertView->assertSee('role="alert"', false);
        
        // Test modal screen reader support
        $modalView = $this->blade('<x-ui.modal title="Dialog" description="Description">Content</x-ui.modal>');
        $modalView->assertSee('role="dialog"', false);
        $modalView->assertSee('aria-modal="true"', false);
        $modalView->assertSee('aria-labelledby', false);
        $modalView->assertSee('aria-describedby', false);
        
        // Test table headers for screen readers
        $tableView = $this->blade('<x-ui.table :data="$data" :columns="$columns" />', [
            'data' => collect([(object)['name' => 'Test']]),
            'columns' => [['key' => 'name', 'label' => 'Name']]
        ]);
        $tableView->assertSee('<thead', false);
        $tableView->assertSee('<th', false);
    }

    /**
     * Test ARIA attributes and roles.
     */
    public function test_aria_attributes_and_roles()
    {
        // Test button with ARIA attributes
        $buttonView = $this->blade('<x-ui.button aria-pressed="false" aria-controls="menu">Toggle</x-ui.button>');
        $buttonView->assertSee('aria-pressed="false"', false);
        $buttonView->assertSee('aria-controls="menu"', false);
        
        // Test input with ARIA attributes
        $inputView = $this->blade('<x-ui.input name="search" aria-label="Search products" role="searchbox" />');
        $inputView->assertSee('aria-label="Search products"', false);
        $inputView->assertSee('role="searchbox"', false);
        
        // Test dropdown ARIA attributes
        $dropdownView = $this->blade('
            <x-ui.dropdown>
                <x-slot name="trigger">Menu</x-slot>
                <x-ui.dropdown-item role="menuitem">Item</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        $dropdownView->assertSee('aria-haspopup="true"', false);
        $dropdownView->assertSee('role="menu"', false);
        
        // Test alert ARIA attributes
        $alertView = $this->blade('<x-ui.alert variant="warning" role="alert">Warning</x-ui.alert>');
        $alertView->assertSee('role="alert"', false);
        $alertView->assertSee('aria-live="polite"', false);
    }

    /**
     * Test color contrast and visual indicators.
     */
    public function test_color_contrast_and_visual_indicators()
    {
        // Test button variants have proper contrast classes
        $variants = ['default', 'secondary', 'outline', 'destructive', 'ghost', 'link'];
        foreach ($variants as $variant) {
            $view = $this->blade("<x-ui.button variant=\"{$variant}\">Test</x-ui.button>");
            $view->assertSee('text-', false); // Should have text color classes
        }
        
        // Test alert variants have proper contrast
        $alertVariants = ['default', 'destructive', 'warning', 'success', 'info'];
        foreach ($alertVariants as $variant) {
            $view = $this->blade("<x-ui.alert variant=\"{$variant}\">Test</x-ui.alert>");
            $view->assertSee('text-', false); // Should have text color classes
            $view->assertSee('bg-', false);   // Should have background color classes
        }
        
        // Test input error state visual indicators
        $errorInputView = $this->blade('<x-ui.input name="test" error="true" />');
        $errorInputView->assertSee('border-destructive', false);
        $errorInputView->assertSee('focus-visible:ring-destructive', false);
        
        // Test disabled state visual indicators
        $disabledView = $this->blade('<x-ui.button disabled>Disabled</x-ui.button>');
        $disabledView->assertSee('disabled:opacity-50', false);
    }

    /**
     * Test semantic markup and structure.
     */
    public function test_semantic_markup_and_structure()
    {
        // Test proper heading structure in cards
        $cardView = $this->blade('
            <x-ui.card>
                <x-ui.card-header>
                    <h2>Main Title</h2>
                    <p>Subtitle</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <h3>Section Title</h3>
                    <p>Content</p>
                </x-ui.card-content>
            </x-ui.card>
        ');
        
        $cardView->assertSee('<h2>Main Title</h2>', false);
        $cardView->assertSee('<h3>Section Title</h3>', false);
        
        // Test table semantic structure
        $tableView = $this->blade('<x-ui.table :data="$data" :columns="$columns" />', [
            'data' => collect([(object)['name' => 'Test']]),
            'columns' => [['key' => 'name', 'label' => 'Name']]
        ]);
        
        $tableView->assertSee('<table', false);
        $tableView->assertSee('<thead', false);
        $tableView->assertSee('<tbody', false);
        $tableView->assertSee('<th', false);
        $tableView->assertSee('<td', false);
        
        // Test form semantic structure
        $formView = $this->blade('
            <form>
                <fieldset>
                    <legend>User Information</legend>
                    <x-ui.input name="name" />
                    <x-ui.textarea name="bio" />
                </fieldset>
            </form>
        ');
        
        $formView->assertSee('<form>', false);
        $formView->assertSee('<fieldset>', false);
        $formView->assertSee('<legend>User Information</legend>', false);
    }

    /**
     * Test live regions and dynamic content announcements.
     */
    public function test_live_regions_and_dynamic_content()
    {
        // Test alert live regions
        $alertView = $this->blade('<x-ui.alert variant="success">Success message</x-ui.alert>');
        $alertView->assertSee('aria-live="polite"', false);
        $alertView->assertSee('role="alert"', false);
        
        // Test loading status announcements
        $loadingView = $this->blade('<x-ui.loading text="Saving changes" />');
        $loadingView->assertSee('role="status"', false);
        $loadingView->assertSee('aria-live="polite"', false);
        
        // Test form validation error announcements
        $errorView = $this->blade('
            <x-ui.input name="email" error="true" aria-describedby="email-error" />
            <div id="email-error" role="alert" aria-live="assertive">Email is required</div>
        ');
        
        $errorView->assertSee('role="alert"', false);
        $errorView->assertSee('aria-live="assertive"', false);
        $errorView->assertSee('aria-describedby="email-error"', false);
    }
}