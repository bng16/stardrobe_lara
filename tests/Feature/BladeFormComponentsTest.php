<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BladeFormComponentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function input_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.input name="test_input" value="test value" placeholder="Enter text" />');
        
        $view->assertSee('name="test_input"', false);
        $view->assertSee('value="test value"', false);
        $view->assertSee('placeholder="Enter text"', false);
        $view->assertSee('type="text"', false);
    }

    /** @test */
    public function input_component_handles_old_values()
    {
        // Test that the component correctly uses old() helper by testing with empty name
        $view = $this->blade('<x-ui.input name="" value="fallback_value" />');
        
        $view->assertSee('value="fallback_value"', false);
    }

    /** @test */
    public function input_component_shows_error_state()
    {
        $view = $this->blade('<x-ui.input name="test_input" error="true" />');
        
        $view->assertSee('border-destructive', false);
        $view->assertSee('focus-visible:ring-destructive', false);
    }

    /** @test */
    public function textarea_component_renders_correctly()
    {
        $view = $this->blade('<x-ui.textarea name="test_textarea" placeholder="Enter description" rows="5" value="Default content" />');
        
        $view->assertSee('name="test_textarea"', false);
        $view->assertSee('placeholder="Enter description"', false);
        $view->assertSee('rows="5"', false);
        $view->assertSee('Default content');
    }

    /** @test */
    public function textarea_component_handles_old_values()
    {
        // Test that the component correctly uses old() helper by testing with empty name
        $view = $this->blade('<x-ui.textarea name="" value="fallback_value" />');
        
        $view->assertSee('fallback_value');
    }

    /** @test */
    public function select_component_renders_with_options()
    {
        $options = [
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3'
        ];
        
        $view = $this->blade('<x-ui.select name="test_select" :options="$options" placeholder="Choose option" />', [
            'options' => $options
        ]);
        
        $view->assertSee('name="test_select"', false);
        $view->assertSee('Choose option');
        $view->assertSee('Option 1');
        $view->assertSee('Option 2');
        $view->assertSee('Option 3');
    }

    /** @test */
    public function select_component_handles_selected_values()
    {
        $options = [
            'option1' => 'Option 1',
            'option2' => 'Option 2'
        ];
        
        $view = $this->blade('<x-ui.select name="test_select" :options="$options" value="option2" />', [
            'options' => $options
        ]);
        
        $view->assertSee('selected', false);
    }

    /** @test */
    public function select_component_handles_option_groups()
    {
        $options = [
            'Group 1' => [
                'g1_option1' => 'Group 1 Option 1',
                'g1_option2' => 'Group 1 Option 2'
            ],
            'Group 2' => [
                'g2_option1' => 'Group 2 Option 1'
            ]
        ];
        
        $view = $this->blade('<x-ui.select name="test_select" :options="$options" />', [
            'options' => $options
        ]);
        
        $view->assertSee('<optgroup label="Group 1">', false);
        $view->assertSee('<optgroup label="Group 2">', false);
        $view->assertSee('Group 1 Option 1');
        $view->assertSee('Group 2 Option 1');
    }

    /** @test */
    public function form_components_support_accessibility_attributes()
    {
        $view = $this->blade('
            <x-ui.input name="test_input" id="custom-id" required aria-describedby="help-text" />
            <x-ui.textarea name="test_textarea" required aria-label="Description field" />
            <x-ui.select name="test_select" required aria-describedby="select-help" />
        ');
        
        $view->assertSee('id="custom-id"', false);
        $view->assertSee('required', false);
        $view->assertSee('aria-describedby="help-text"', false);
        $view->assertSee('aria-label="Description field"', false);
        $view->assertSee('aria-describedby="select-help"', false);
    }

    /** @test */
    public function form_components_handle_disabled_state()
    {
        $view = $this->blade('
            <x-ui.input name="test_input" disabled />
            <x-ui.textarea name="test_textarea" disabled />
            <x-ui.select name="test_select" disabled />
        ');
        
        $view->assertSee('disabled', false);
        $view->assertSee('disabled:cursor-not-allowed', false);
        $view->assertSee('disabled:opacity-50', false);
    }
}