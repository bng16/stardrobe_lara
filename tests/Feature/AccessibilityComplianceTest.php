<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccessibilityComplianceTest extends TestCase
{
    /**
     * Test WCAG 2.1 AA compliance for keyboard navigation.
     */
    public function test_wcag_keyboard_navigation_compliance()
    {
        // Test all interactive elements are keyboard accessible
        $interactiveView = $this->blade('
            <x-ui.button>Button</x-ui.button>
            <x-ui.button href="/link">Link Button</x-ui.button>
            <x-ui.input name="text_input" />
            <x-ui.textarea name="textarea_input" />
            <x-ui.select name="select_input" :options="[]" />
            <x-ui.dropdown id="dropdown">
                <x-slot name="trigger">Menu</x-slot>
                <x-ui.dropdown-item>Item</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        
        // All interactive elements should be focusable
        $interactiveView->assertSee('tabindex="0"', false); // Dropdown trigger
        $interactiveView->assertDontSee('tabindex="-1"', false); // No elements should be unfocusable unless disabled
        
        // Test disabled elements are properly excluded from tab order
        $disabledView = $this->blade('
            <x-ui.button disabled>Disabled Button</x-ui.button>
            <x-ui.button href="/test" disabled>Disabled Link</x-ui.button>
            <x-ui.input name="test" disabled />
        ');
        
        $disabledView->assertSee('tabindex="-1"', false); // Disabled link should have tabindex="-1"
        $disabledView->assertSee('disabled', false); // Disabled form elements
    }

    /**
     * Test WCAG 2.1 AA compliance for focus indicators.
     */
    public function test_wcag_focus_indicators_compliance()
    {
        // Test button and link focus indicators
        $buttonView = $this->blade('<x-ui.button>Button</x-ui.button>');
        $buttonView->assertSee('focus-visible:outline-none', false);
        $buttonView->assertSee('focus-visible:ring-2', false);
        $buttonView->assertSee('focus-visible:ring-ring', false);
        $buttonView->assertSee('focus-visible:ring-offset-2', false);
        
        $linkView = $this->blade('<x-ui.button href="/test">Link</x-ui.button>');
        $linkView->assertSee('focus-visible:outline-none', false);
        $linkView->assertSee('focus-visible:ring-2', false);
        
        // Test input and textarea focus indicators
        $inputView = $this->blade('<x-ui.input name="test" />');
        $inputView->assertSee('focus-visible:outline-none', false);
        $inputView->assertSee('focus-visible:ring-2', false);
        $inputView->assertSee('focus-visible:ring-ring', false);
        $inputView->assertSee('focus-visible:ring-offset-2', false);
        
        $textareaView = $this->blade('<x-ui.textarea name="test" />');
        $textareaView->assertSee('focus-visible:outline-none', false);
        $textareaView->assertSee('focus-visible:ring-2', false);
        
        // Test select focus indicators (uses focus: instead of focus-visible:)
        $selectView = $this->blade('<x-ui.select name="test" :options="[]" />');
        $selectView->assertSee('focus:outline-none', false);
        $selectView->assertSee('focus:ring-2', false);
        $selectView->assertSee('focus:ring-ring', false);
        $selectView->assertSee('focus:ring-offset-2', false);
        
        // Test modal and dropdown focus management
        $modalView = $this->blade('<x-ui.modal title="Test">Content</x-ui.modal>');
        $modalView->assertSee('role="dialog"', false);
        
        $dropdownView = $this->blade('
            <x-ui.dropdown>
                <x-slot name="trigger">Menu</x-slot>
                <x-ui.dropdown-item>Item</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        $dropdownView->assertSee('tabindex="0"', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for color contrast.
     */
    public function test_wcag_color_contrast_compliance()
    {
        // Test button variants have sufficient contrast
        $buttonVariants = [
            'default' => ['bg-primary', 'text-primary-foreground'],
            'secondary' => ['bg-secondary', 'text-secondary-foreground'],
            'destructive' => ['bg-destructive', 'text-destructive-foreground'],
            'outline' => ['border', 'bg-background'],
            'ghost' => ['hover:bg-accent', 'hover:text-accent-foreground'],
            'link' => ['text-primary', 'underline-offset-4']
        ];
        
        foreach ($buttonVariants as $variant => $expectedClasses) {
            $view = $this->blade("<x-ui.button variant=\"{$variant}\">Test</x-ui.button>");
            
            foreach ($expectedClasses as $class) {
                $view->assertSee($class, false);
            }
        }
        
        // Test alert variants have sufficient contrast
        $alertVariants = [
            'destructive' => ['text-red-800', 'bg-red-50', 'border-red-200'],
            'warning' => ['text-yellow-800', 'bg-yellow-50', 'border-yellow-200'],
            'success' => ['text-green-800', 'bg-green-50', 'border-green-200'],
            'info' => ['text-blue-800', 'bg-blue-50', 'border-blue-200']
        ];
        
        foreach ($alertVariants as $variant => $expectedClasses) {
            $view = $this->blade("<x-ui.alert variant=\"{$variant}\">Test</x-ui.alert>");
            
            foreach ($expectedClasses as $class) {
                $view->assertSee($class, false);
            }
        }
    }

    /**
     * Test WCAG 2.1 AA compliance for form labels and descriptions.
     */
    public function test_wcag_form_labeling_compliance()
    {
        // Test proper form labeling
        $formView = $this->blade('
            <div>
                <label for="username">Username *</label>
                <x-ui.input id="username" name="username" required aria-describedby="username-help" />
                <div id="username-help">Enter your username (required)</div>
            </div>
            
            <div>
                <label for="email">Email Address</label>
                <x-ui.input id="email" name="email" type="email" aria-describedby="email-help" />
                <div id="email-help">We will never share your email</div>
            </div>
            
            <div>
                <label for="bio">Biography</label>
                <x-ui.textarea id="bio" name="bio" aria-describedby="bio-help" />
                <div id="bio-help">Tell us about yourself (optional)</div>
            </div>
        ');
        
        // Check for proper ID associations
        $formView->assertSee('for="username"', false);
        $formView->assertSee('id="username"', false);
        $formView->assertSee('aria-describedby="username-help"', false);
        $formView->assertSee('id="username-help"', false);
        
        $formView->assertSee('for="email"', false);
        $formView->assertSee('id="email"', false);
        $formView->assertSee('aria-describedby="email-help"', false);
        
        $formView->assertSee('for="bio"', false);
        $formView->assertSee('id="bio"', false);
        $formView->assertSee('aria-describedby="bio-help"', false);
        
        // Test required field indicators
        $formView->assertSee('required', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for error handling.
     */
    public function test_wcag_error_handling_compliance()
    {
        // Test form validation error announcements
        $errorFormView = $this->blade('
            <div>
                <label for="email">Email</label>
                <x-ui.input id="email" name="email" error="true" aria-describedby="email-error" aria-invalid="true" />
                <div id="email-error" role="alert" aria-live="assertive">Please enter a valid email address</div>
            </div>
            
            <div>
                <label for="password">Password</label>
                <x-ui.input id="password" name="password" type="password" error="true" aria-describedby="password-error" aria-invalid="true" />
                <div id="password-error" role="alert" aria-live="assertive">Password must be at least 8 characters</div>
            </div>
        ');
        
        // Check error state attributes
        $errorFormView->assertSee('aria-invalid="true"', false);
        $errorFormView->assertSee('aria-describedby="email-error"', false);
        $errorFormView->assertSee('role="alert"', false);
        $errorFormView->assertSee('aria-live="assertive"', false);
        
        // Check visual error indicators
        $errorFormView->assertSee('border-destructive', false);
        $errorFormView->assertSee('focus-visible:ring-destructive', false);
        
        // Test alert error messages
        $alertErrorView = $this->blade('<x-ui.alert variant="destructive" title="Error">Form submission failed</x-ui.alert>');
        $alertErrorView->assertSee('role="alert"', false);
        $alertErrorView->assertSee('aria-live="polite"', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for headings and landmarks.
     */
    public function test_wcag_headings_and_landmarks_compliance()
    {
        // Test proper heading hierarchy
        $headingView = $this->blade('
            <main>
                <h1>Main Page Title</h1>
                <x-ui.card>
                    <x-ui.card-header>
                        <h2>Section Title</h2>
                        <p>Section description</p>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <h3>Subsection Title</h3>
                        <p>Content</p>
                    </x-ui.card-content>
                </x-ui.card>
            </main>
        ');
        
        $headingView->assertSee('<main>', false);
        $headingView->assertSee('<h1>Main Page Title</h1>', false);
        $headingView->assertSee('<h2>Section Title</h2>', false);
        $headingView->assertSee('<h3>Subsection Title</h3>', false);
        
        // Test modal heading structure
        $modalView = $this->blade('<x-ui.modal title="Dialog Title" description="Dialog description">Content</x-ui.modal>');
        $modalView->assertSee('aria-labelledby', false);
        $modalView->assertSee('aria-describedby', false);
        
        // Test table caption and headers
        $tableView = $this->blade('<x-ui.table :data="$data" :columns="$columns" />', [
            'data' => collect([(object)['name' => 'Test', 'email' => 'test@example.com']]),
            'columns' => [
                ['key' => 'name', 'label' => 'Full Name'],
                ['key' => 'email', 'label' => 'Email Address']
            ]
        ]);
        
        $tableView->assertSee('<thead', false);
        $tableView->assertSee('<th', false);
        $tableView->assertSee('Full Name');
        $tableView->assertSee('Email Address');
    }

    /**
     * Test WCAG 2.1 AA compliance for live regions.
     */
    public function test_wcag_live_regions_compliance()
    {
        // Test status updates
        $statusView = $this->blade('<x-ui.loading text="Saving your changes" />');
        $statusView->assertSee('role="status"', false);
        $statusView->assertSee('aria-live="polite"', false);
        $statusView->assertSee('aria-label="Loading: Saving your changes"', false);
        
        // Test alert announcements
        $alertView = $this->blade('<x-ui.alert variant="success">Changes saved successfully</x-ui.alert>');
        $alertView->assertSee('role="alert"', false);
        $alertView->assertSee('aria-live="polite"', false);
        
        // Test error announcements (should be assertive)
        $errorAlertView = $this->blade('<x-ui.alert variant="destructive">An error occurred</x-ui.alert>');
        $errorAlertView->assertSee('role="alert"', false);
        $errorAlertView->assertSee('aria-live="polite"', false);
        
        // Test form validation live regions
        $validationView = $this->blade('
            <x-ui.input name="email" error="true" aria-describedby="email-error" />
            <div id="email-error" role="alert" aria-live="assertive">Invalid email format</div>
        ');
        
        $validationView->assertSee('role="alert"', false);
        $validationView->assertSee('aria-live="assertive"', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for modal focus management.
     */
    public function test_wcag_modal_focus_management_compliance()
    {
        // Test modal dialog attributes
        $modalView = $this->blade('
            <x-ui.modal id="test_modal" title="Important Dialog" description="Please review the information">
                <p>Modal content here</p>
                <x-slot name="footer">
                    <x-ui.button variant="outline">Cancel</x-ui.button>
                    <x-ui.button>Confirm</x-ui.button>
                </x-slot>
            </x-ui.modal>
        ');
        
        // Check modal dialog attributes
        $modalView->assertSee('role="dialog"', false);
        $modalView->assertSee('aria-modal="true"', false);
        $modalView->assertSee('aria-labelledby', false);
        $modalView->assertSee('aria-describedby', false);
        
        // Check close button accessibility
        $modalView->assertSee('aria-label="Close modal"', false);
        
        // Test modal without description
        $simpleModalView = $this->blade('<x-ui.modal title="Simple Dialog">Content</x-ui.modal>');
        $simpleModalView->assertSee('aria-labelledby', false);
        $simpleModalView->assertDontSee('aria-describedby', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for dropdown menus.
     */
    public function test_wcag_dropdown_menu_compliance()
    {
        // Test dropdown menu attributes
        $dropdownView = $this->blade('
            <x-ui.dropdown id="user_menu">
                <x-slot name="trigger">
                    <button>User Menu</button>
                </x-slot>
                <x-ui.dropdown-item href="/profile" role="menuitem">Profile</x-ui.dropdown-item>
                <x-ui.dropdown-item href="/settings" role="menuitem">Settings</x-ui.dropdown-item>
                <x-ui.dropdown-separator />
                <x-ui.dropdown-item href="/logout" role="menuitem">Logout</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        
        // Check menu button attributes
        $dropdownView->assertSee('role="button"', false);
        $dropdownView->assertSee('aria-haspopup="true"', false);
        $dropdownView->assertSee('aria-expanded="false"', false);
        $dropdownView->assertSee('tabindex="0"', false);
        
        // Check menu attributes
        $dropdownView->assertSee('role="menu"', false);
        $dropdownView->assertSee('aria-orientation="vertical"', false);
        
        // Test disabled dropdown
        $disabledDropdownView = $this->blade('
            <x-ui.dropdown disabled="true">
                <x-slot name="trigger">Disabled Menu</x-slot>
                <x-ui.dropdown-item>Item</x-ui.dropdown-item>
            </x-ui.dropdown>
        ');
        
        $disabledDropdownView->assertSee('aria-disabled="true"', false);
        $disabledDropdownView->assertSee('tabindex="-1"', false);
    }

    /**
     * Test WCAG 2.1 AA compliance for table accessibility.
     */
    public function test_wcag_table_accessibility_compliance()
    {
        $columns = [
            ['key' => 'name', 'label' => 'Full Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email Address', 'sortable' => true],
            ['key' => 'role', 'label' => 'User Role', 'sortable' => false],
            ['key' => 'status', 'label' => 'Account Status', 'sortable' => true]
        ];
        
        $data = collect([
            (object)['name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin', 'status' => 'Active'],
            (object)['name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'User', 'status' => 'Inactive']
        ]);
        
        $tableView = $this->blade('<x-ui.table :data="$data" :columns="$columns" currentSort="name" currentDirection="asc" />', [
            'data' => $data,
            'columns' => $columns
        ]);
        
        // Check table structure
        $tableView->assertSee('<table', false);
        $tableView->assertSee('<thead', false);
        $tableView->assertSee('<tbody', false);
        
        // Check sortable headers
        $tableView->assertSee('role="button"', false);
        $tableView->assertSee('tabindex="0"', false);
        $tableView->assertSee('aria-sort="ascending"', false);
        
        // Check keyboard support for sorting
        $tableView->assertSee('onkeydown="if(event.key === \'Enter\' || event.key === \' \')', false);
        
        // Test table with caption
        $captionTableView = $this->blade('
            <div class="relative overflow-hidden rounded-lg border bg-card">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">User Management Table</h2>
                    <p class="text-sm text-muted-foreground">Manage user accounts and permissions</p>
                </div>
                <x-ui.table :data="$data" :columns="$columns" />
            </div>
        ', [
            'data' => $data,
            'columns' => $columns
        ]);
        
        $captionTableView->assertSee('User Management Table');
        $captionTableView->assertSee('Manage user accounts and permissions');
    }
}