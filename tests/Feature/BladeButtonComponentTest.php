<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeButtonComponentTest extends TestCase
{
    /** @test */
    public function it_renders_default_button_correctly()
    {
        $view = $this->blade('<x-ui.button>Click me</x-ui.button>');
        
        $view->assertSee('Click me');
        $view->assertSee('button', false);
        $view->assertSee('type="button"', false);
        $view->assertSee('bg-primary', false);
        $view->assertSee('text-primary-foreground', false);
        $view->assertSee('h-10 px-4 py-2', false);
    }

    /** @test */
    public function it_renders_all_variants_correctly()
    {
        $variants = [
            'default' => 'bg-primary text-primary-foreground',
            'secondary' => 'bg-secondary text-secondary-foreground',
            'outline' => 'border border-input bg-background',
            'destructive' => 'bg-destructive text-destructive-foreground',
            'ghost' => 'hover:bg-accent hover:text-accent-foreground',
            'link' => 'text-primary underline-offset-4'
        ];

        foreach ($variants as $variant => $expectedClasses) {
            $view = $this->blade("<x-ui.button variant=\"{$variant}\">Test</x-ui.button>");
            
            $view->assertSee('Test');
            foreach (explode(' ', $expectedClasses) as $class) {
                $view->assertSee($class, false);
            }
        }
    }

    /** @test */
    public function it_renders_all_sizes_correctly()
    {
        $sizes = [
            'sm' => 'h-9 rounded-md px-3',
            'default' => 'h-10 px-4 py-2',
            'lg' => 'h-11 rounded-md px-8',
            'icon' => 'h-10 w-10'
        ];

        foreach ($sizes as $size => $expectedClasses) {
            $view = $this->blade("<x-ui.button size=\"{$size}\">Test</x-ui.button>");
            
            $view->assertSee('Test');
            foreach (explode(' ', $expectedClasses) as $class) {
                $view->assertSee($class, false);
            }
        }
    }

    /** @test */
    public function it_renders_as_anchor_when_href_provided()
    {
        $view = $this->blade('<x-ui.button href="/test">Link Button</x-ui.button>');
        
        $view->assertSee('Link Button');
        $view->assertSee('<a', false);
        $view->assertSee('href="/test"', false);
        $view->assertDontSee('<button', false);
    }

    /** @test */
    public function it_handles_disabled_state_for_buttons()
    {
        $view = $this->blade('<x-ui.button disabled>Disabled</x-ui.button>');
        
        $view->assertSee('Disabled');
        $view->assertSee('disabled', false);
        $view->assertSee('disabled:opacity-50', false);
    }

    /** @test */
    public function it_handles_disabled_state_for_links()
    {
        $view = $this->blade('<x-ui.button href="/test" disabled>Disabled Link</x-ui.button>');
        
        $view->assertSee('Disabled Link');
        $view->assertSee('aria-disabled="true"', false);
        $view->assertSee('tabindex="-1"', false);
    }

    /** @test */
    public function it_accepts_custom_css_classes()
    {
        $view = $this->blade('<x-ui.button class="custom-class">Test</x-ui.button>');
        
        $view->assertSee('Test');
        $view->assertSee('custom-class', false);
    }

    /** @test */
    public function it_accepts_custom_attributes()
    {
        $view = $this->blade('<x-ui.button id="test-button" data-test="value">Test</x-ui.button>');
        
        $view->assertSee('Test');
        $view->assertSee('id="test-button"', false);
        $view->assertSee('data-test="value"', false);
    }

    /** @test */
    public function it_supports_different_button_types()
    {
        $view = $this->blade('<x-ui.button type="submit">Submit</x-ui.button>');
        
        $view->assertSee('Submit');
        $view->assertSee('type="submit"', false);
    }

    /** @test */
    public function it_includes_proper_accessibility_classes()
    {
        $view = $this->blade('<x-ui.button>Accessible</x-ui.button>');
        
        $view->assertSee('Accessible');
        $view->assertSee('focus-visible:outline-none', false);
        $view->assertSee('focus-visible:ring-2', false);
        $view->assertSee('focus-visible:ring-ring', false);
        $view->assertSee('focus-visible:ring-offset-2', false);
    }

    /** @test */
    public function it_includes_transition_classes()
    {
        $view = $this->blade('<x-ui.button>Animated</x-ui.button>');
        
        $view->assertSee('Animated');
        $view->assertSee('transition-colors', false);
    }
}