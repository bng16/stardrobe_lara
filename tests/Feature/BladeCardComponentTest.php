<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BladeCardComponentTest extends TestCase
{
    public function test_card_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.card>Test content</x-ui.card>');
        
        $view->assertSee('Test content');
        $view->assertSeeInOrder([
            'rounded-lg',
            'border',
            'bg-card',
            'text-card-foreground',
            'shadow-sm'
        ]);
    }

    public function test_card_component_accepts_custom_classes()
    {
        $view = $this->blade('<x-ui.card class="custom-class">Test content</x-ui.card>');
        
        $view->assertSee('custom-class');
        $view->assertSee('Test content');
    }

    public function test_card_header_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.card-header>Header content</x-ui.card-header>');
        
        $view->assertSee('Header content');
        $view->assertSeeInOrder([
            'flex',
            'flex-col',
            'space-y-1.5',
            'p-6'
        ]);
    }

    public function test_card_content_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.card-content>Content here</x-ui.card-content>');
        
        $view->assertSee('Content here');
        $view->assertSeeInOrder([
            'p-6',
            'pt-0'
        ]);
    }

    public function test_card_components_work_together()
    {
        $view = $this->blade('
            <x-ui.card>
                <x-ui.card-header>
                    <h3>Card Title</h3>
                    <p>Card description</p>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p>This is the card content</p>
                </x-ui.card-content>
            </x-ui.card>
        ');
        
        $view->assertSee('Card Title');
        $view->assertSee('Card description');
        $view->assertSee('This is the card content');
    }

    public function test_card_components_accept_additional_attributes()
    {
        $view = $this->blade('<x-ui.card id="test-card" data-testid="card">Content</x-ui.card>');
        
        $view->assertSee('id="test-card"', false);
        $view->assertSee('data-testid="card"', false);
    }
}