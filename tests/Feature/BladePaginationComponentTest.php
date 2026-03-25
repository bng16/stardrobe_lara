<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\User;

class BladePaginationComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        User::factory()->count(50)->create();
    }

    /** @test */
    public function it_does_not_render_when_no_pages()
    {
        $singlePageData = new LengthAwarePaginator(
            User::take(5)->get(),
            5,
            10,
            1,
            ['path' => request()->url()]
        );
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $singlePageData
        ]);
        
        // Should not render anything when there's only one page
        $view->assertDontSee('Pagination Navigation');
        $view->assertDontSee('Previous');
        $view->assertDontSee('Next');
    }

    /** @test */
    public function it_renders_pagination_with_multiple_pages()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        $view->assertSee('role="navigation"', false);
        $view->assertSee('aria-label="Pagination Navigation"', false);
        $view->assertSee('Showing');
        $view->assertSee('results');
    }

    /** @test */
    public function it_shows_correct_result_counts()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        $view->assertSee('Showing');
        $view->assertSee('1'); // First item
        $view->assertSee('10'); // Last item on first page
        $view->assertSee('50'); // Total results
        $view->assertSee('results');
    }

    /** @test */
    public function it_renders_mobile_pagination()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Mobile pagination (hidden on larger screens)
        $view->assertSee('flex justify-between flex-1 sm:hidden');
        $view->assertSee('Previous');
        $view->assertSee('Next');
    }

    /** @test */
    public function it_renders_desktop_pagination()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Desktop pagination
        $view->assertSee('hidden sm:flex-1 sm:flex sm:items-center sm:justify-between');
        $view->assertSee('relative z-0 inline-flex rounded-md shadow-sm');
    }

    /** @test */
    public function it_handles_first_page_correctly()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Previous button should be disabled on first page
        $view->assertSee('aria-disabled="true"', false);
        $view->assertSee('cursor-default');
        $view->assertSee('text-muted-foreground');
        
        // Current page should be highlighted
        $view->assertSee('aria-current="page"', false);
        $view->assertSee('bg-primary border border-primary');
    }

    /** @test */
    public function it_handles_last_page_correctly()
    {
        $lastPage = User::paginate(10)->lastPage();
        $paginatedData = User::paginate(10, ['*'], 'page', $lastPage);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Next button should be disabled on last page
        $view->assertSee('aria-disabled="true"', false);
        $view->assertSee('cursor-default');
        
        // Should show correct page number
        $view->assertSee('aria-current="page"', false);
    }

    /** @test */
    public function it_renders_page_numbers_correctly()
    {
        $paginatedData = User::paginate(10, ['*'], 'page', 3); // Go to page 3
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Should show page numbers around current page
        $view->assertSee('1'); // First page
        $view->assertSee('2'); // Previous page
        $view->assertSee('3'); // Current page
        $view->assertSee('4'); // Next page
        $view->assertSee('5'); // Page after next
    }

    /** @test */
    public function it_shows_ellipsis_for_large_page_ranges()
    {
        // Create more data to get more pages
        User::factory()->count(200)->create();
        $paginatedData = User::paginate(10, ['*'], 'page', 10); // Go to page 10
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Should show ellipsis
        $view->assertSee('...');
    }

    /** @test */
    public function it_includes_proper_accessibility_attributes()
    {
        $paginatedData = User::paginate(10, ['*'], 'page', 2); // Go to page 2 so we have both prev and next
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        $view->assertSee('role="navigation"', false);
        $view->assertSee('aria-label="Pagination Navigation"', false);
        $view->assertSee('aria-label="Previous"', false);
        $view->assertSee('aria-label="Next"', false);
        $view->assertSee('aria-current="page"', false);
        $view->assertSee('rel="prev"', false);
        $view->assertSee('rel="next"', false);
    }

    /** @test */
    public function it_includes_proper_svg_icons()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Should include SVG icons for previous/next
        $view->assertSee('<svg', false);
        $view->assertSee('viewBox="0 0 20 20"', false);
        $view->assertSee('fill-rule="evenodd"', false);
        $view->assertSee('clip-rule="evenodd"', false);
    }

    /** @test */
    public function it_handles_middle_page_navigation()
    {
        $paginatedData = User::paginate(10, ['*'], 'page', 3);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Previous button should be enabled
        $view->assertSee('href="' . $paginatedData->previousPageUrl() . '"', false);
        
        // Next button should be enabled
        $view->assertSee('href="' . $paginatedData->nextPageUrl() . '"', false);
        
        // Current page should be highlighted
        $view->assertSee('aria-current="page"', false);
        $view->assertSee('text-primary-foreground bg-primary');
    }

    /** @test */
    public function it_renders_proper_hover_and_focus_states()
    {
        $paginatedData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        $view->assertSee('hover:bg-muted');
        $view->assertSee('focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2');
        $view->assertSee('transition-colors');
    }

    /** @test */
    public function it_handles_page_range_calculation_correctly()
    {
        // Test beginning of pagination
        $beginningData = User::paginate(10, ['*'], 'page', 2);
        $beginningView = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $beginningData
        ]);
        
        // Should show pages 1-5 when near beginning
        $beginningView->assertSee('1');
        $beginningView->assertSee('2');
        $beginningView->assertSee('3');
        
        // Test end of pagination
        User::factory()->count(50)->create(); // Add more data
        $lastPage = User::paginate(10)->lastPage();
        $endData = User::paginate(10, ['*'], 'page', $lastPage - 1);
        $endView = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $endData
        ]);
        
        // Should show last few pages when near end
        $endView->assertSee((string)($lastPage - 2));
        $endView->assertSee((string)($lastPage - 1));
        $endView->assertSee((string)$lastPage);
    }

    /** @test */
    public function it_handles_empty_results_correctly()
    {
        $emptyData = new LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url()]);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $emptyData
        ]);
        
        // Should not render pagination for empty results
        $view->assertDontSee('Pagination Navigation');
        $view->assertDontSee('Showing');
    }

    /** @test */
    public function it_renders_correct_link_structure()
    {
        $paginatedData = User::paginate(10, ['*'], 'page', 2);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $paginatedData
        ]);
        
        // Check link structure
        $view->assertSee('relative inline-flex items-center');
        $view->assertSee('px-4 py-2');
        $view->assertSee('text-sm font-medium');
        $view->assertSee('border border-input');
        $view->assertSee('leading-5');
        $view->assertSee('rounded-md', false); // For mobile buttons
        $view->assertSee('rounded-l-md', false); // For first desktop button
        $view->assertSee('rounded-r-md', false); // For last desktop button
    }

    /** @test */
    public function it_handles_single_page_with_results()
    {
        // Create exactly 5 users (less than page size)
        User::query()->delete();
        User::factory()->count(5)->create();
        
        $singlePageData = User::paginate(10);
        
        $view = $this->blade('<x-ui.pagination :paginator="$paginator" />', [
            'paginator' => $singlePageData
        ]);
        
        // Should not render pagination when there's only one page
        $view->assertDontSee('Previous');
        $view->assertDontSee('Next');
        $view->assertDontSee('Pagination Navigation');
    }
}