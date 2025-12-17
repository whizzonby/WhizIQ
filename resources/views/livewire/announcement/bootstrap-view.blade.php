<div x-data="{ isVisible: true }" x-show="isVisible">
    @if ($announcement !== null)
        <div style="background-color: #b9e9ff; color: #3f5d6b; padding: 0.75rem 0; text-align: center;">
            <div class="container">
                <div class="d-flex align-items-center justify-content-center" style="gap: 1rem; position: relative;">
                    <div style="flex: 1; text-align: center; font-size: 0.875rem; line-height: 1.5;">
                        {!! str($announcement->content)->sanitizeHtml() !!}
                    </div>
                    @if ($announcement->is_dismissible)
                        <button 
                            type="button" 
                            class="btn btn-link p-0 border-0" 
                            style="color: #3f5d6b; text-decoration: none; padding: 0.25rem 0.5rem; min-width: auto; position: absolute; right: 0; opacity: 0.7;" 
                            aria-label="Close"
                            @click="isVisible = false"
                            wire:click="dismiss({{ $announcement->id }})"
                        >
                            <i class="fas fa-times" style="font-size: 0.875rem;"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

