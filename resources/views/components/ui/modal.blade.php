@props([
    'id' => 'modal',
    'size' => 'md',
    'dismissible' => true,
    'show' => false,
    'backdrop' => true,
    'backdropBlur' => true,
    'closeOnBackdrop' => true,
    'closeOnEscape' => true,
    'title' => null,
    'description' => null
])

@php
// Size classes for modal content
$sizeClasses = match($size) {
    'sm' => 'max-w-sm',
    'lg' => 'max-w-4xl',
    'xl' => 'max-w-6xl',
    'full' => 'max-w-full mx-4',
    default => 'max-w-lg' // md
};

// Base classes for modal container
$modalClasses = 'fixed inset-0 z-50 flex items-center justify-center p-4';

// Backdrop classes
$backdropClasses = 'fixed inset-0 bg-black/50 transition-opacity duration-300';
if ($backdropBlur) {
    $backdropClasses .= ' backdrop-blur-sm';
}

// Content classes
$contentClasses = 'relative w-full ' . $sizeClasses . ' bg-background rounded-lg shadow-lg border transform transition-all duration-300 scale-95 opacity-0';

// Show/hide classes
$showClasses = $show ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none';
$backdropShowClasses = $show ? 'opacity-100' : 'opacity-0 pointer-events-none';

// Generate unique modal ID
$modalId = $id . '_' . uniqid();
@endphp

{{-- Modal Container --}}
<div id="{{ $modalId }}" 
     class="{{ $modalClasses }} {{ $showClasses }}"
     role="dialog" 
     aria-modal="true"
     @if($title) aria-labelledby="{{ $modalId }}_title" @endif
     @if($description) aria-describedby="{{ $modalId }}_description" @endif
     style="display: {{ $show ? 'flex' : 'none' }};"
     data-modal-id="{{ $modalId }}"
     data-dismissible="{{ $dismissible ? 'true' : 'false' }}"
     data-close-on-backdrop="{{ $closeOnBackdrop ? 'true' : 'false' }}"
     data-close-on-escape="{{ $closeOnEscape ? 'true' : 'false' }}"
     {{ $attributes->except(['id', 'size', 'dismissible', 'show', 'backdrop', 'backdropBlur', 'closeOnBackdrop', 'closeOnEscape', 'title', 'description']) }}>
    
    {{-- Backdrop --}}
    @if($backdrop)
        <div class="{{ $backdropClasses }} {{ $backdropShowClasses }}" 
             data-modal-backdrop="{{ $modalId }}"></div>
    @endif
    
    {{-- Modal Content --}}
    <div class="{{ $contentClasses }} {{ $showClasses }}" 
         data-modal-content="{{ $modalId }}"
         role="document">
        
        {{-- Header Section --}}
        @if($title || $dismissible)
            <div class="flex items-center justify-between p-6 border-b border-border">
                <div class="flex-1">
                    @if($title)
                        <h2 id="{{ $modalId }}_title" class="text-lg font-semibold leading-none tracking-tight">
                            {{ $title }}
                        </h2>
                    @endif
                    @if($description)
                        <p id="{{ $modalId }}_description" class="text-sm text-muted-foreground mt-1">
                            {{ $description }}
                        </p>
                    @endif
                </div>
                
                @if($dismissible)
                    <button type="button" 
                            class="ml-4 inline-flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                            data-modal-close="{{ $modalId }}"
                            aria-label="Close modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif
        
        {{-- Body Section --}}
        <div class="p-6">
            {{ $slot }}
        </div>
        
        {{-- Footer Section (if provided via named slot) --}}
        @isset($footer)
            <div class="flex items-center justify-end gap-2 p-6 border-t border-border">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>

{{-- JavaScript for Modal Functionality --}}
@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            class Modal {
                constructor(element) {
                    this.element = element;
                    this.modalId = element.dataset.modalId;
                    this.dismissible = element.dataset.dismissible === 'true';
                    this.closeOnBackdrop = element.dataset.closeOnBackdrop === 'true';
                    this.closeOnEscape = element.dataset.closeOnEscape === 'true';
                    this.isOpen = false;
                    this.previousFocus = null;
                    
                    this.init();
                }
                
                init() {
                    // Close button event
                    const closeBtn = this.element.querySelector(`[data-modal-close="${this.modalId}"]`);
                    if (closeBtn) {
                        closeBtn.addEventListener('click', () => this.close());
                    }
                    
                    // Backdrop click event
                    if (this.closeOnBackdrop) {
                        const backdrop = this.element.querySelector(`[data-modal-backdrop="${this.modalId}"]`);
                        if (backdrop) {
                            backdrop.addEventListener('click', () => this.close());
                        }
                    }
                    
                    // Escape key event
                    if (this.closeOnEscape) {
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape' && this.isOpen) {
                                this.close();
                            }
                        });
                    }
                    
                    // Focus trap
                    this.element.addEventListener('keydown', (e) => this.handleFocusTrap(e));
                }
                
                open() {
                    if (this.isOpen) return;
                    
                    this.previousFocus = document.activeElement;
                    this.isOpen = true;
                    
                    // Show modal
                    this.element.style.display = 'flex';
                    this.element.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    this.element.classList.add('opacity-100', 'scale-100');
                    
                    // Show backdrop
                    const backdrop = this.element.querySelector(`[data-modal-backdrop="${this.modalId}"]`);
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0', 'pointer-events-none');
                        backdrop.classList.add('opacity-100');
                    }
                    
                    // Show content
                    const content = this.element.querySelector(`[data-modal-content="${this.modalId}"]`);
                    if (content) {
                        content.classList.remove('opacity-0', 'scale-95');
                        content.classList.add('opacity-100', 'scale-100');
                    }
                    
                    // Focus management
                    setTimeout(() => {
                        const firstFocusable = this.getFirstFocusableElement();
                        if (firstFocusable) {
                            firstFocusable.focus();
                        }
                    }, 100);
                    
                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                    
                    // Dispatch open event
                    this.element.dispatchEvent(new CustomEvent('modal:open', { detail: { modalId: this.modalId } }));
                }
                
                close() {
                    if (!this.isOpen || !this.dismissible) return;
                    
                    this.isOpen = false;
                    
                    // Hide modal with animation
                    this.element.classList.remove('opacity-100', 'scale-100');
                    this.element.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    
                    // Hide backdrop
                    const backdrop = this.element.querySelector(`[data-modal-backdrop="${this.modalId}"]`);
                    if (backdrop) {
                        backdrop.classList.remove('opacity-100');
                        backdrop.classList.add('opacity-0', 'pointer-events-none');
                    }
                    
                    // Hide content
                    const content = this.element.querySelector(`[data-modal-content="${this.modalId}"]`);
                    if (content) {
                        content.classList.remove('opacity-100', 'scale-100');
                        content.classList.add('opacity-0', 'scale-95');
                    }
                    
                    // Hide modal after animation
                    setTimeout(() => {
                        this.element.style.display = 'none';
                    }, 300);
                    
                    // Restore focus
                    if (this.previousFocus) {
                        this.previousFocus.focus();
                    }
                    
                    // Restore body scroll
                    document.body.style.overflow = '';
                    
                    // Dispatch close event
                    this.element.dispatchEvent(new CustomEvent('modal:close', { detail: { modalId: this.modalId } }));
                }
                
                toggle() {
                    if (this.isOpen) {
                        this.close();
                    } else {
                        this.open();
                    }
                }
                
                getFirstFocusableElement() {
                    const focusableElements = this.element.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    return focusableElements[0];
                }
                
                getLastFocusableElement() {
                    const focusableElements = this.element.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    return focusableElements[focusableElements.length - 1];
                }
                
                handleFocusTrap(e) {
                    if (!this.isOpen || e.key !== 'Tab') return;
                    
                    const firstFocusable = this.getFirstFocusableElement();
                    const lastFocusable = this.getLastFocusableElement();
                    
                    if (e.shiftKey) {
                        // Shift + Tab
                        if (document.activeElement === firstFocusable) {
                            e.preventDefault();
                            lastFocusable.focus();
                        }
                    } else {
                        // Tab
                        if (document.activeElement === lastFocusable) {
                            e.preventDefault();
                            firstFocusable.focus();
                        }
                    }
                }
            }
            
            // Initialize all modals
            const modals = document.querySelectorAll('[data-modal-id]');
            const modalInstances = new Map();
            
            modals.forEach(modalElement => {
                const modal = new Modal(modalElement);
                modalInstances.set(modal.modalId, modal);
            });
            
            // Global modal functions
            window.openModal = function(modalId) {
                const modal = modalInstances.get(modalId);
                if (modal) modal.open();
            };
            
            window.closeModal = function(modalId) {
                const modal = modalInstances.get(modalId);
                if (modal) modal.close();
            };
            
            window.toggleModal = function(modalId) {
                const modal = modalInstances.get(modalId);
                if (modal) modal.toggle();
            };
            
            // Handle modal triggers
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('[data-modal-trigger]');
                if (trigger) {
                    e.preventDefault();
                    const targetModalId = trigger.dataset.modalTrigger;
                    window.openModal(targetModalId);
                }
            });
        });
    </script>
    @endpush
@endonce