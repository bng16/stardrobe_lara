<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeAlertComponentTest extends TestCase
{
    /** @test */
    public function it_renders_default_alert_correctly()
    {
        $view = $this->blade('<x-ui.alert>Default alert message</x-ui.alert>');
        
        $view->assertSee('Default alert message');
        $view->assertSee('role="alert"', false);
        $view->assertSee('aria-live="polite"', false);
        $view->assertSee('bg-background text-foreground border-border');
    }

    /** @test */
    public function it_renders_all_variants_correctly()
    {
        $variants = [
            'default' => 'bg-background text-foreground border-border',
            'destructive' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 text-yellow-800',
            'success' => 'bg-green-50 text-green-800',
            'info' => 'bg-blue-50 text-blue-800'
        ];

        foreach ($variants as $variant => $expectedClasses) {
            $view = $this->blade("<x-ui.alert variant=\"{$variant}\">Test message</x-ui.alert>");
            
            $view->assertSee('Test message');
            foreach (explode(' ', $expectedClasses) as $class) {
                if (!empty($class)) {
                    $view->assertSee($class, false);
                }
            }
        }
    }

    /** @test */
    public function it_renders_with_title()
    {
        $view = $this->blade('<x-ui.alert title="Alert Title">Alert content</x-ui.alert>');
        
        $view->assertSee('Alert Title');
        $view->assertSee('Alert content');
        $view->assertSee('font-medium leading-none tracking-tight');
    }

    /** @test */
    public function it_renders_with_icon_by_default()
    {
        $view = $this->blade('<x-ui.alert>Alert with icon</x-ui.alert>');
        
        $view->assertSee('Alert with icon');
        $view->assertSee('<svg', false);
        $view->assertSee('h-4 w-4', false);
    }

    /** @test */
    public function it_can_hide_icon()
    {
        $view = $this->blade('<x-ui.alert :icon="false">Alert without icon</x-ui.alert>');
        
        $view->assertSee('Alert without icon');
        $view->assertDontSee('<svg', false);
    }

    /** @test */
    public function it_renders_different_icons_for_variants()
    {
        // Test destructive variant has X icon
        $destructiveView = $this->blade('<x-ui.alert variant="destructive">Error</x-ui.alert>');
        $destructiveView->assertSee('fill-rule="evenodd"', false);
        
        // Test warning variant has warning triangle icon
        $warningView = $this->blade('<x-ui.alert variant="warning">Warning</x-ui.alert>');
        $warningView->assertSee('fill-rule="evenodd"', false);
        
        // Test success variant has checkmark icon
        $successView = $this->blade('<x-ui.alert variant="success">Success</x-ui.alert>');
        $successView->assertSee('fill-rule="evenodd"', false);
        
        // Test info variant has info icon
        $infoView = $this->blade('<x-ui.alert variant="info">Info</x-ui.alert>');
        $infoView->assertSee('fill-rule="evenodd"', false);
    }

    /** @test */
    public function it_renders_dismissible_alert()
    {
        $view = $this->blade('<x-ui.alert dismissible>Dismissible alert</x-ui.alert>');
        
        $view->assertSee('Dismissible alert');
        $view->assertSee('<button', false);
        $view->assertSee('aria-label="Close alert"', false);
        $view->assertSee('onclick=', false);
        $view->assertSee('absolute right-2 top-2');
    }

    /** @test */
    public function it_renders_non_dismissible_alert_by_default()
    {
        $view = $this->blade('<x-ui.alert>Non-dismissible alert</x-ui.alert>');
        
        $view->assertSee('Non-dismissible alert');
        $view->assertDontSee('aria-label="Close alert"', false);
        $view->assertDontSee('<button', false);
    }

    /** @test */
    public function it_generates_unique_ids_for_dismissible_alerts()
    {
        $view1 = $this->blade('<x-ui.alert dismissible>Alert 1</x-ui.alert>');
        $view2 = $this->blade('<x-ui.alert dismissible>Alert 2</x-ui.alert>');
        
        // Both should have unique IDs
        $view1->assertSee('id="alert-', false);
        $view2->assertSee('id="alert-', false);
        
        // Content should be different
        $view1->assertSee('Alert 1');
        $view2->assertSee('Alert 2');
    }

    /** @test */
    public function it_accepts_custom_css_classes()
    {
        $view = $this->blade('<x-ui.alert class="custom-alert-class">Custom styled alert</x-ui.alert>');
        
        $view->assertSee('Custom styled alert');
        $view->assertSee('custom-alert-class', false);
    }

    /** @test */
    public function it_accepts_custom_attributes()
    {
        $view = $this->blade('<x-ui.alert id="custom-alert" data-testid="alert">Custom alert</x-ui.alert>');
        
        $view->assertSee('Custom alert');
        $view->assertSee('data-testid="alert"', false);
    }

    /** @test */
    public function it_includes_proper_accessibility_attributes()
    {
        $view = $this->blade('<x-ui.alert>Accessible alert</x-ui.alert>');
        
        $view->assertSee('Accessible alert');
        $view->assertSee('role="alert"', false);
        $view->assertSee('aria-live="polite"', false);
    }

    /** @test */
    public function it_handles_complex_content()
    {
        $view = $this->blade('
            <x-ui.alert variant="warning" title="Complex Alert" dismissible>
                <p>This is a paragraph with <strong>bold text</strong>.</p>
                <ul>
                    <li>List item 1</li>
                    <li>List item 2</li>
                </ul>
            </x-ui.alert>
        ');
        
        $view->assertSee('Complex Alert');
        $view->assertSee('This is a paragraph with');
        $view->assertSee('bold text');
        $view->assertSee('List item 1');
        $view->assertSee('List item 2');
        $view->assertSee('bg-yellow-50');
        $view->assertSee('<button', false); // Dismissible
    }

    /** @test */
    public function it_applies_proper_spacing_classes()
    {
        $view = $this->blade('<x-ui.alert>Spaced alert</x-ui.alert>');
        
        $view->assertSee('Spaced alert');
        $view->assertSee('px-4 py-3');
        $view->assertSee('[&>svg~*]:pl-7');
        $view->assertSee('[&>svg+div]:translate-y-[-3px]');
    }

    /** @test */
    public function it_handles_title_and_content_structure()
    {
        $view = $this->blade('
            <x-ui.alert title="Alert Title">
                <p>First paragraph</p>
                <p>Second paragraph</p>
            </x-ui.alert>
        ');
        
        $view->assertSee('Alert Title');
        $view->assertSee('First paragraph');
        $view->assertSee('Second paragraph');
        $view->assertSee('mb-1 font-medium leading-none tracking-tight');
        $view->assertSee('[&_p]:leading-relaxed', false);
    }

    /** @test */
    public function dismissible_alert_includes_close_functionality()
    {
        $view = $this->blade('<x-ui.alert dismissible>Closeable alert</x-ui.alert>');
        
        $view->assertSee('Closeable alert');
        $view->assertSee('onclick="document.getElementById(', false);
        $view->assertSee(').style.display=\'none\'"', false);
        $view->assertSee('transition-opacity hover:opacity-100');
        $view->assertSee('focus:outline-none focus:ring-2');
    }
}