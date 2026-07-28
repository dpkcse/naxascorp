document.addEventListener('alpine:init', () => {
    window.Alpine.data('publicNavigation', () => ({
        navigationOpen: false,
        open() {
            this.navigationOpen = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.drawer.focus());
        },
        close() {
            if (!this.navigationOpen) return;
            this.navigationOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.$refs.opener.focus());
        },
        init() {
            this.$watch('navigationOpen', (open) => {
                if (!open) document.body.classList.remove('overflow-hidden');
            });
            this.$el.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && this.navigationOpen) this.close();
                if (event.key !== 'Tab' || !this.navigationOpen) return;
                const focusable = [...this.$refs.drawer.querySelectorAll('a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])')];
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            });
        },
    }));
});
