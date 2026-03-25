@props([
    'id' => 'dropdown',
    'align' => 'left',
    'position' => 'bottom',
    'trigger' => 'click',
    'width' => 'auto',
    'offset' => 8,
    'closeOnClick' => true,
    'disabled' => false
])

@php
// Generate unique dropdown ID
$dropdownId = $id . '_' . uniqid();

// Alignment classes for dropdown menu
$alignmentClasses = match($align) {
    'right' => 'right-0',
    'center' => 'left-1/2 transform -translate-x-1/2',
    default => 'left-0' // left
};

// Position classes for dropdown menu
$positionClasses = match($position) {
    'top' => 'bottom-full mb-' . $offset,
    'left' => 'right-full mr-' . $offset . ' top-0',
    'right' => 'left-full ml-' . $offset . ' top-0',
    default => 'top-full mt-' . $offset // bottom
};

// Width classes
$widthClasses = match($width) {
    'sm' => 'w-48',
    'md' => 'w-56',
    'lg' => 'w-64',
    'xl' => 'w-72',
    'full' => 'w-full',
    default => 'w-auto min-w-[8rem]'
};

// Base dropdown menu classes
$menuClasses = 'absolute z-50 ' . $alignmentClasses . ' ' . $positionClasses . ' ' . $widthClasses . ' bg-white rounded-md border border-gray-200 shadow-lg py-1 opacity-0 scale-95 pointer-events-none transform transition-all duration-200 ease-out';

// Show classes for when dropdown is open
$showClasses = 'opacity-100 scale-100 pointer-events-auto';
@endphp

<div class="relative inline-block text-left" 
     data-dropdown-id="{{ $dropdownId }}"
     data-trigger="{{ $trigger }}"
     data-close-on-click="{{ $closeOnClick ? 'true' : 'false' }}"
     {{ $attributes->except(['id', 'align', 'position', 'trigger', 'width', 'offset', 'closeOnClick', 'disabled']) }}>
    
    {{-- Dropdown Trigger --}}
    <div data-dropdown-trigger="{{ $dropdownId }}"
         @if($disabled) aria-disabled="true" @endif
         class="{{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
         role="button"
         aria-haspopup="true"
         aria-expanded="false"
         tabindex="{{ $disabled ? '-1' : '0' }}">
        {{ $trigger ?? '' }}
    </div>
    
    {{-- Dropdown Menu --}}
    <div data-dropdown-menu="{{ $dropdownId }}"
         class="{{ $menuClasses }}"
         role="menu"
         aria-orientation="vertical"
         aria-labelledby="dropdown-trigger-{{ $dropdownId }}"
         style="display: none;">
        {{ $slot }}
    </div>
</div>

{{-- JavaScript for Dropdown Functionality --}}
@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            class Dropdown {
                constructor(element) {
                    this.element = element;
                    this.dropdownId = element.dataset.dropdownId;
                    this.trigger = element.dataset.trigger || 'click';
                    this.closeOnClick = element.dataset.closeOnClick === 'true';
                    this.isOpen = false;
                    this.triggerElement = element.querySelector(`[data-dropdown-trigger="${this.dropdownId}"]`);
                    this.menuElement = element.querySelector(`[data-dropdown-menu="${this.dropdownId}"]`);
                    this.menuItems = [];
                    this.currentFocusIndex = -1;
                    
                    this.init();
                }
                
                init() {
                    if (!this.triggerElement || !this.menuElement) return;
                    
                    // Set up trigger events
                    if (this.trigger === 'click') {
                        this.triggerElement.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            this.toggle();
                        });
                    } else if (this.trigger === 'hover') {
                        this.element.addEventListener('mouseenter', () => this.open());
                        this.element.addEventListener('mouseleave', () => this.close());
                    }
                    
                    // Keyboard navigation for trigger
                    this.triggerElement.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.toggle();
                        } else if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.open();
                            this.focusFirstItem();
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.open();
                            this.focusLastItem();
                        } else if (e.key === 'Escape') {
                            this.close();
                        }
                    });
                    
                    // Menu keyboard navigation
                    this.menuElement.addEventListener('keydown', (e) => this.handleMenuKeydown(e));
                    
                    // Close on outside click
                    document.addEventListener('click', (e) => {
                        if (!this.element.contains(e.target)) {
                            this.close();
                        }
                    });
                    
                    // Close on escape key
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.isOpen) {
                            this.close();
                            this.triggerElement.focus();
                        }
                    });
                    
                    // Handle menu item clicks
                    if (this.closeOnClick) {
                        this.menuElement.addEventListener('click', (e) => {
                            const menuItem = e.target.closest('[role="menuitem"]');
                            if (menuItem && !menuItem.hasAttribute('data-no-close')) {
                                this.close();
                            }
                        });
                    }
                    
                    // Update menu items list
                    this.updateMenuItems();
                    
                    // Watch for dynamic menu item changes
                    const observer = new MutationObserver(() => this.updateMenuItems());
                    observer.observe(this.menuElement, { childList: true, subtree: true });
                }
                
                updateMenuItems() {
                    this.menuItems = Array.from(this.menuElement.querySelectorAll('[role="menuitem"]:not([disabled])'));
                }
                
                open() {
                    if (this.isOpen || this.triggerElement.getAttribute('aria-disabled') === 'true') return;
                    
                    this.isOpen = true;
                    this.triggerElement.setAttribute('aria-expanded', 'true');
                    
                    // Show menu
                    this.menuElement.style.display = 'block';
                    
                    // Force reflow for transition
                    this.menuElement.offsetHeight;
                    
                    // Add show classes
                    this.menuElement.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    this.menuElement.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
                    
                    // Update menu items
                    this.updateMenuItems();
                    
                    // Dispatch open event
                    this.element.dispatchEvent(new CustomEvent('dropdown:open', { 
                        detail: { dropdownId: this.dropdownId } 
                    }));
                }
                
                close() {
                    if (!this.isOpen) return;
                    
                    this.isOpen = false;
                    this.currentFocusIndex = -1;
                    this.triggerElement.setAttribute('aria-expanded', 'false');
                    
                    // Hide menu with animation
                    this.menuElement.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                    this.menuElement.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    
                    // Hide menu after animation
                    setTimeout(() => {
                        if (!this.isOpen) {
                            this.menuElement.style.display = 'none';
                        }
                    }, 200);
                    
                    // Dispatch close event
                    this.element.dispatchEvent(new CustomEvent('dropdown:close', { 
                        detail: { dropdownId: this.dropdownId } 
                    }));
                }
                
                toggle() {
                    if (this.isOpen) {
                        this.close();
                    } else {
                        this.open();
                    }
                }
                
                focusFirstItem() {
                    if (this.menuItems.length > 0) {
                        this.currentFocusIndex = 0;
                        this.menuItems[0].focus();
                    }
                }
                
                focusLastItem() {
                    if (this.menuItems.length > 0) {
                        this.currentFocusIndex = this.menuItems.length - 1;
                        this.menuItems[this.currentFocusIndex].focus();
                    }
                }
                
                focusNextItem() {
                    if (this.menuItems.length === 0) return;
                    
                    this.currentFocusIndex = (this.currentFocusIndex + 1) % this.menuItems.length;
                    this.menuItems[this.currentFocusIndex].focus();
                }
                
                focusPreviousItem() {
                    if (this.menuItems.length === 0) return;
                    
                    this.currentFocusIndex = this.currentFocusIndex <= 0 
                        ? this.menuItems.length - 1 
                        : this.currentFocusIndex - 1;
                    this.menuItems[this.currentFocusIndex].focus();
                }
                
                handleMenuKeydown(e) {
                    switch (e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            this.focusNextItem();
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            this.focusPreviousItem();
                            break;
                        case 'Home':
                            e.preventDefault();
                            this.focusFirstItem();
                            break;
                        case 'End':
                            e.preventDefault();
                            this.focusLastItem();
                            break;
                        case 'Enter':
                        case ' ':
                            e.preventDefault();
                            if (document.activeElement) {
                                document.activeElement.click();
                            }
                            break;
                        case 'Escape':
                            e.preventDefault();
                            this.close();
                            this.triggerElement.focus();
                            break;
                        case 'Tab':
                            this.close();
                            break;
                    }
                }
            }
            
            // Initialize all dropdowns
            const dropdowns = document.querySelectorAll('[data-dropdown-id]');
            const dropdownInstances = new Map();
            
            dropdowns.forEach(dropdownElement => {
                const dropdown = new Dropdown(dropdownElement);
                dropdownInstances.set(dropdown.dropdownId, dropdown);
            });
            
            // Global dropdown functions
            window.openDropdown = function(dropdownId) {
                const dropdown = dropdownInstances.get(dropdownId);
                if (dropdown) dropdown.open();
            };
            
            window.closeDropdown = function(dropdownId) {
                const dropdown = dropdownInstances.get(dropdownId);
                if (dropdown) dropdown.close();
            };
            
            window.toggleDropdown = function(dropdownId) {
                const dropdown = dropdownInstances.get(dropdownId);
                if (dropdown) dropdown.toggle();
            };
        });
    </script>
    @endpush
@endonce