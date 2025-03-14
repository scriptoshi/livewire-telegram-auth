<div class="w-full">
    <!-- Hidden div for Telegram script -->
    <div wire:ignore></div>
    
    <button
        id="telegram-login-button"
        type="button"
        x-data="{
            isLoaded: @entangle('isLoaded'),
            error: @entangle('error'),
            init() {
                // Create and load Telegram widget script
                const script = document.createElement('script');
                script.async = true;
                script.src = 'https://telegram.org/js/telegram-widget.js?22';
                script.onload = () => {
                    this.isLoaded = true;
                };
                document.head.appendChild(script);
            },
            handleAuth() {
                if (!window.Telegram || !window.Telegram.Login) {
                    this.error = 'Telegram API not loaded';
                    return;
                }
                
                this.isLoaded = false;
                window.Telegram.Login.auth(
                    { bot_id: '{{ $this->botId }}', request_access: true },
                    (data) => {
                        this.isLoaded = true;
                        if (!data) {
                            this.error = 'Remote Telegram Error';
                            return;
                        }
                        $wire.telegramAuth(data);
                    }
                );
            }
        }"
        x-on:click="handleAuth"
        class="relative group w-full text-sky-500 border border-sky-500 hover:bg-sky-500 hover:text-white transition-colors py-2 px-4 rounded-md flex items-center justify-center"
    >
        <template x-if="!isLoaded">
            <svg class="animate-spin h-5 w-5 absolute left-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </template>
        <template x-if="isLoaded">
            <svg class="h-5 w-5 absolute left-5 group-hover:bg-sky-500 rounded-full bg-white transition-colors" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
        </template>
        
        <template x-if="error">
            <span class="text-red-500" x-text="error"></span>
        </template>
        <template x-if="!error">
            <span>{{__('Continue With Telegram')}}</span>
        </template>
    </button>
</div>
