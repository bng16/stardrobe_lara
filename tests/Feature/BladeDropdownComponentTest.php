<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeDropdownComponentTest extends TestCase
{
    public function test_dropdown_item_renders_correctly()
    {
        $view = $this->blade('<x-ui.dropdown-item>Test Item</x-ui.dropdown-item>');
        
        $view->assertSee('Test Item');
        $view->assertSee('role="menuitem"', false);
        $view->assertSee('tabindex="0"', false);
    }

    public function test_dropdown_item_renders_as_link()
    {
        $view = $this->blade('<x-ui.dropdown-item href="/test">Link Item</x-ui.dropdown-item>');
        
        $view->assertSee('Link Item');
        $view->assertSee('<a', false);
        $view->assertSee('href="/test"', false);
    }

    public function test_dropdown_item_handles_disabled_state()
    {
        $view = $this->blade('<x-ui.dropdown-item disabled>Disabled Item</x-ui.dropdown-item>');
        
        $view->assertSee('Disabled Item');
        $view->assertSee('disabled', false);
        $view->assertSee('aria-disabled="true"', false);
    }

    public function test_dropdown_label_renders_correctly()
    {
        $view = $this->blade('<x-ui.dropdown-label>Section Label</x-ui.dropdown-label>');
        
        $view->assertSee('Section Label');
        $view->assertSee('role="presentation"', false);
    }

    public function test_dropdown_separator_renders_correctly()
    {
        $view = $this->blade('<x-ui.dropdown-separator />');
        
        $view->assertSee('role="separator"', false);
        $view->assertSee('border-t border-gray-200');
    }
}