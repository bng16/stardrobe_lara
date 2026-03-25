<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladeModalComponentTest extends TestCase
{
    /**
     * Test basic modal component rendering.
     */
    public function test_modal_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.modal id="test_modal" title="Test Modal">Test content</x-ui.modal>');
        
        $view->assertSee('Test Modal');
        $view->assertSee('Test content');
        $view->assertSee('data-modal-id');
        $view->assertSee('role="dialog"', false);
        $view->assertSee('aria-modal="true"', false);
    }

    /**
     * Test modal with different sizes.
     */
    public function test_modal_sizes()
    {
        // Small modal
        $view = $this->blade('<x-ui.modal id="small_modal" size="sm" title="Small">Content</x-ui.modal>');
        $view->assertSee('max-w-sm');

        // Medium modal (default)
        $view = $this->blade('<x-ui.modal id="medium_modal" title="Medium">Content</x-ui.modal>');
        $view->assertSee('max-w-lg');

        // Large modal
        $view = $this->blade('<x-ui.modal id="large_modal" size="lg" title="Large">Content</x-ui.modal>');
        $view->assertSee('max-w-4xl');

        // Extra large modal
        $view = $this->blade('<x-ui.modal id="xl_modal" size="xl" title="XL">Content</x-ui.modal>');
        $view->assertSee('max-w-6xl');

        // Full modal
        $view = $this->blade('<x-ui.modal id="full_modal" size="full" title="Full">Content</x-ui.modal>');
        $view->assertSee('max-w-full');
    }

    /**
     * Test modal dismissible behavior.
     */
    public function test_modal_dismissible_behavior()
    {
        // Dismissible modal (default)
        $view = $this->blade('<x-ui.modal id="dismissible_modal" title="Dismissible">Content</x-ui.modal>');
        $view->assertSee('data-dismissible="true"', false);
        $view->assertSee('data-close-on-backdrop="true"', false);
        $view->assertSee('data-close-on-escape="true"', false);

        // Non-dismissible modal
        $view = $this->blade('<x-ui.modal id="non_dismissible_modal" title="Non-dismissible" :dismissible="false" :closeOnBackdrop="false" :closeOnEscape="false">Content</x-ui.modal>');
        $view->assertSee('data-dismissible="false"', false);
        $view->assertSee('data-close-on-backdrop="false"', false);
        $view->assertSee('data-close-on-escape="false"', false);
    }

    /**
     * Test modal with footer slot.
     */
    public function test_modal_with_footer()
    {
        $view = $this->blade('
            <x-ui.modal id="footer_modal" title="With Footer">
                <p>Modal content</p>
                <x-slot name="footer">
                    <button>Cancel</button>
                    <button>Save</button>
                </x-slot>
            </x-ui.modal>
        ');

        $view->assertSee('Modal content');
        $view->assertSee('Cancel');
        $view->assertSee('Save');
        $view->assertSee('border-t border-border'); // Footer border class
    }

    /**
     * Test modal accessibility attributes.
     */
    public function test_modal_accessibility_attributes()
    {
        $view = $this->blade('<x-ui.modal id="accessible_modal" title="Accessible Modal" description="Modal description">Content</x-ui.modal>');
        
        // Check ARIA attributes
        $view->assertSee('role="dialog"', false);
        $view->assertSee('aria-modal="true"', false);
        $view->assertSee('aria-labelledby');
        $view->assertSee('aria-describedby');
        
        // Check close button accessibility
        $view->assertSee('aria-label="Close modal"', false);
    }

    /**
     * Test modal backdrop configuration.
     */
    public function test_modal_backdrop_configuration()
    {
        // With backdrop and blur (default)
        $view = $this->blade('<x-ui.modal id="backdrop_modal" title="With Backdrop">Content</x-ui.modal>');
        $view->assertSee('backdrop-blur-sm');
        $view->assertSee('bg-black/50');

        // Without backdrop blur
        $view = $this->blade('<x-ui.modal id="no_blur_modal" title="No Blur" :backdropBlur="false">Content</x-ui.modal>');
        $view->assertDontSee('backdrop-blur-sm');

        // Without backdrop
        $view = $this->blade('<x-ui.modal id="no_backdrop_modal" title="No Backdrop" :backdrop="false">Content</x-ui.modal>');
        $view->assertDontSee('data-modal-backdrop');
    }

    /**
     * Test modal show state.
     */
    public function test_modal_show_state()
    {
        // Hidden modal (default)
        $view = $this->blade('<x-ui.modal id="hidden_modal" title="Hidden">Content</x-ui.modal>');
        $view->assertSee('style="display: none;"', false);
        $view->assertSee('opacity-0 scale-95 pointer-events-none');

        // Shown modal
        $view = $this->blade('<x-ui.modal id="shown_modal" title="Shown" :show="true">Content</x-ui.modal>');
        $view->assertSee('style="display: flex;"', false);
        $view->assertSee('opacity-100 scale-100');
    }

    /**
     * Test modal header component.
     */
    public function test_modal_header_component()
    {
        $view = $this->blade('<x-ui.modal-header title="Header Title" description="Header description" modalId="test_modal" />');
        
        $view->assertSee('Header Title');
        $view->assertSee('Header description');
        $view->assertSee('data-modal-close="test_modal"', false);
        $view->assertSee('border-b border-border');
    }

    /**
     * Test modal body component.
     */
    public function test_modal_body_component()
    {
        $view = $this->blade('<x-ui.modal-body class="custom-class">Body content</x-ui.modal-body>');
        
        $view->assertSee('Body content');
        $view->assertSee('p-6');
        $view->assertSee('custom-class');
    }

    /**
     * Test modal footer component.
     */
    public function test_modal_footer_component()
    {
        $view = $this->blade('<x-ui.modal-footer>Footer content</x-ui.modal-footer>');
        
        $view->assertSee('Footer content');
        $view->assertSee('border-t border-border');
        $view->assertSee('justify-end');
    }

    /**
     * Test modal with custom attributes.
     */
    public function test_modal_with_custom_attributes()
    {
        $view = $this->blade('<x-ui.modal id="custom_modal" title="Custom" data-custom="value" class="custom-class">Content</x-ui.modal>');
        
        $view->assertSee('data-custom="value"', false);
        $view->assertSee('custom-class');
    }

    /**
     * Test modal unique ID generation.
     */
    public function test_modal_unique_id_generation()
    {
        $view1 = $this->blade('<x-ui.modal id="test" title="Test 1">Content 1</x-ui.modal>');
        $view2 = $this->blade('<x-ui.modal id="test" title="Test 2">Content 2</x-ui.modal>');
        
        // Both should contain the base ID but with unique suffixes
        $view1->assertSee('data-modal-id');
        $view2->assertSee('data-modal-id');
        
        // Content should be different
        $view1->assertSee('Content 1');
        $view2->assertSee('Content 2');
    }
}