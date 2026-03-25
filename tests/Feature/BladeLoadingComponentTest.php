<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeLoadingComponentTest extends TestCase
{
    /** @test */
    public function it_renders_basic_loading_component()
    {
        $view = $this->blade('<x-ui.loading />');

        $view->assertSee('role="status"', false);
        $view->assertSee('aria-live="polite"', false);
        $view->assertSee('aria-label="Loading"', false);
        $view->assertSee('animate-spin', false);
        $view->assertSee('Loading', false); // Screen reader text
    }

    /** @test */
    public function it_renders_loading_with_text()
    {
        $view = $this->blade('<x-ui.loading text="Loading data..." />');

        $view->assertSee('Loading data...');
        $view->assertSee('aria-label="Loading: Loading data..."', false);
    }

    /** @test */
    public function it_renders_different_variants()
    {
        // Spinner variant
        $spinnerView = $this->blade('<x-ui.loading variant="spinner" />');
        $spinnerView->assertSee('animate-spin', false);
        $spinnerView->assertSee('<svg', false);

        // Dots variant
        $dotsView = $this->blade('<x-ui.loading variant="dots" />');
        $dotsView->assertSee('animate-bounce', false);
        $dotsView->assertSee('rounded-full', false);

        // Bars variant
        $barsView = $this->blade('<x-ui.loading variant="bars" />');
        $barsView->assertSee('animate-pulse', false);
        $barsView->assertSee('items-end', false);
    }

    /** @test */
    public function it_renders_different_sizes()
    {
        // Small size
        $smallView = $this->blade('<x-ui.loading size="sm" />');
        $smallView->assertSee('h-4 w-4', false);

        // Medium size (default)
        $mediumView = $this->blade('<x-ui.loading size="md" />');
        $mediumView->assertSee('h-6 w-6', false);

        // Large size
        $largeView = $this->blade('<x-ui.loading size="lg" />');
        $largeView->assertSee('h-8 w-8', false);

        // Extra large size
        $xlView = $this->blade('<x-ui.loading size="xl" />');
        $xlView->assertSee('h-12 w-12', false);
    }

    /** @test */
    public function it_renders_different_colors()
    {
        // Primary color (default)
        $primaryView = $this->blade('<x-ui.loading color="primary" />');
        $primaryView->assertSee('text-blue-600', false);

        // Secondary color
        $secondaryView = $this->blade('<x-ui.loading color="secondary" />');
        $secondaryView->assertSee('text-gray-600', false);

        // White color
        $whiteView = $this->blade('<x-ui.loading color="white" />');
        $whiteView->assertSee('text-white', false);

        // Success color
        $successView = $this->blade('<x-ui.loading color="success" />');
        $successView->assertSee('text-green-600', false);

        // Warning color
        $warningView = $this->blade('<x-ui.loading color="warning" />');
        $warningView->assertSee('text-yellow-600', false);

        // Danger color
        $dangerView = $this->blade('<x-ui.loading color="danger" />');
        $dangerView->assertSee('text-red-600', false);
    }

    /** @test */
    public function it_renders_inline_display()
    {
        $view = $this->blade('<x-ui.loading inline />');
        $view->assertSee('inline-flex', false);
    }

    /** @test */
    public function it_renders_block_display_by_default()
    {
        $view = $this->blade('<x-ui.loading />');
        $view->assertSee('flex items-center justify-center', false);
        $view->assertDontSee('inline-flex', false);
    }

    /** @test */
    public function it_does_not_render_when_show_is_false()
    {
        $view = $this->blade('<x-ui.loading :show="false" />');
        $view->assertDontSee('role="status"', false);
        $view->assertDontSee('Loading');
    }

    /** @test */
    public function it_renders_when_show_is_true()
    {
        $view = $this->blade('<x-ui.loading :show="true" />');
        $view->assertSee('role="status"', false);
        $view->assertSee('Loading');
    }

    /** @test */
    public function it_includes_accessibility_attributes()
    {
        $view = $this->blade('<x-ui.loading text="Processing data" />');

        $view->assertSee('role="status"', false);
        $view->assertSee('aria-live="polite"', false);
        $view->assertSee('aria-label="Loading: Processing data"', false);
        $view->assertSee('aria-hidden="true"', false); // For the visual elements
        $view->assertSee('sr-only', false); // Screen reader only text
    }

    /** @test */
    public function it_accepts_custom_classes()
    {
        $view = $this->blade('<x-ui.loading class="my-custom-class" />');
        $view->assertSee('my-custom-class', false);
    }

    /** @test */
    public function it_accepts_custom_attributes()
    {
        $view = $this->blade('<x-ui.loading id="custom-loader" data-testid="loader" />');
        $view->assertSee('id="custom-loader"', false);
        $view->assertSee('data-testid="loader"', false);
    }

    /** @test */
    public function it_renders_text_with_appropriate_size_classes()
    {
        // Small text
        $smallView = $this->blade('<x-ui.loading size="sm" text="Loading" />');
        $smallView->assertSee('text-sm', false);

        // Medium text (default)
        $mediumView = $this->blade('<x-ui.loading size="md" text="Loading" />');
        $mediumView->assertSee('text-base', false);

        // Large text
        $largeView = $this->blade('<x-ui.loading size="lg" text="Loading" />');
        $largeView->assertSee('text-lg', false);

        // Extra large text
        $xlView = $this->blade('<x-ui.loading size="xl" text="Loading" />');
        $xlView->assertSee('text-xl', false);
    }

    /** @test */
    public function it_renders_dots_variant_with_correct_structure()
    {
        $view = $this->blade('<x-ui.loading variant="dots" />');

        $view->assertSee('flex space-x-1', false);
        $view->assertSee('animate-bounce', false);
        $view->assertSee('rounded-full', false);
        $view->assertSee('bg-current', false);
        
        // Should have three dots with different animation delays
        $view->assertSee('animation-delay: -0.3s', false);
        $view->assertSee('animation-delay: -0.15s', false);
    }

    /** @test */
    public function it_renders_bars_variant_with_correct_structure()
    {
        $view = $this->blade('<x-ui.loading variant="bars" />');

        $view->assertSee('flex items-end space-x-1', false);
        $view->assertSee('animate-pulse', false);
        $view->assertSee('bg-current', false);
        
        // Should have three bars with different animation delays
        $view->assertSee('animation-delay: -0.4s', false);
        $view->assertSee('animation-delay: -0.2s', false);
        $view->assertSee('animation-duration: 1.2s', false);
    }

    /** @test */
    public function it_renders_spinner_variant_with_svg()
    {
        $view = $this->blade('<x-ui.loading variant="spinner" />');

        $view->assertSee('<svg', false);
        $view->assertSee('animate-spin', false);
        $view->assertSee('viewBox="0 0 24 24"', false);
        $view->assertSee('<circle', false);
        $view->assertSee('<path', false);
        $view->assertSee('opacity-25', false);
        $view->assertSee('opacity-75', false);
    }

    /** @test */
    public function it_handles_complex_usage_scenarios()
    {
        $view = $this->blade('
            <x-ui.loading 
                variant="dots" 
                size="lg" 
                color="success" 
                text="Processing your request..." 
                inline 
                class="custom-loading" 
                id="main-loader" 
            />
        ');

        $view->assertSee('inline-flex', false);
        $view->assertSee('h-4 w-4', false); // Large dots size
        $view->assertSee('text-green-600', false);
        $view->assertSee('text-lg', false); // Large text size
        $view->assertSee('Processing your request...');
        $view->assertSee('custom-loading', false);
        $view->assertSee('id="main-loader"', false);
        $view->assertSee('animate-bounce', false);
    }
}