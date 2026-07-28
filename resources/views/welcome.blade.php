<x-layouts.public-site
    :settings="$settings"
    :site-name="$siteName"
    :legal-name="$legalName"
    :navigation="$navigation"
    :page-title="$pageTitle"
    :description="$description"
    :canonical="$canonical"
>
    <section class="relative overflow-hidden bg-public-navy py-20 text-white sm:py-28 lg:py-32">
        <div class="public-container relative grid items-center gap-12 lg:grid-cols-[1.15fr_.85fr]">
            <div class="max-w-3xl">
                <p class="public-eyebrow text-blue-200">Naxora public design system</p>
                <h1 class="mt-5 text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl">Premium corporate website foundation</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">Designed for business, enterprise, and government organizations, with an accessible and performance-conscious foundation.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-public.button href="#foundation" variant="light">Explore the foundation</x-public.button>
                    <x-public.button href="#contact-preview" variant="outline-dark">Preview form controls</x-public.button>
                </div>
            </div>
            <x-public.card variant="dark" title="Configuration status" description="Content modules will be configured in later phases. This preview demonstrates reusable presentation components only.">
                <x-slot:icon><span class="flex size-11 items-center justify-center rounded-lg bg-blue-500/20 text-xl" aria-hidden="true">✓</span></x-slot:icon>
                <x-slot:action><span class="text-sm font-semibold text-blue-200">No sample records or fabricated metrics</span></x-slot:action>
            </x-public.card>
        </div>
    </section>

    <x-public.breadcrumb :items="[['label' => 'Design system preview']]" />

    <x-public.section id="foundation">
        <x-public.section-heading eyebrow="Reusable foundations" title="A consistent system for future public content" description="These neutral primitives are ready for later CMS modules without presenting placeholder content as published information." />
        <x-public.grid columns="3" class="mt-10">
            <x-public.card variant="feature" title="Accessible by default" description="Landmarks, focus visibility, semantic states, reduced motion, and keyboard-ready navigation are built into the shell." />
            <x-public.card variant="feature" title="Enterprise presentation" description="A restrained navy and blue palette, clear hierarchy, and generous spacing support formal corporate communication." />
            <x-public.card variant="feature" title="Performance conscious" description="System fonts, local Vite assets, static Blade rendering, and no remote media or public API calls keep the foundation lean." />
        </x-public.grid>
    </x-public.section>

    <x-public.section variant="alternate">
        <x-public.section-heading eyebrow="Component states" title="Honest, reusable interface feedback" description="Status patterns communicate meaning with text and structure, never color alone." />
        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            <div class="grid gap-4">
                <x-public.alert type="info" title="Information">Public content editing is planned for a later phase.</x-public.alert>
                <x-public.alert type="success" title="Foundation ready">Core presentation components are available for integration.</x-public.alert>
                <x-public.alert type="warning" title="Coming soon">Navigation destinations remain disabled until their modules exist.</x-public.alert>
            </div>
            <x-public.empty-state title="Content modules not configured" description="This is an intentional preview state. No articles, case studies, clients, or statistics have been created." />
        </div>
    </x-public.section>

    <x-public.section id="contact-preview">
        <div class="grid items-start gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <x-public.section-heading alignment="left" eyebrow="Form foundation" title="Controls prepared for future workflows" description="This non-submitting preview shows the accessible field system. Server-side persistence and validation will be added with their respective modules." />
            <form class="grid gap-5 rounded-public border border-public-border bg-white p-6 shadow-public sm:p-8" aria-label="Public form component preview" x-on:submit.prevent>
                <x-public.validation-summary :errors="[]" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-public.form.input name="preview_name" label="Name" required autocomplete="name" />
                    <x-public.form.input name="preview_email" type="email" label="Work email" required autocomplete="email" />
                </div>
                <x-public.form.select name="preview_interest" label="Area of interest" :options="['' => 'Select an option', 'foundation' => 'Website foundation', 'future' => 'Future CMS modules']" />
                <x-public.form.textarea name="preview_message" label="Message" helper="Preview only; information entered here is not submitted." />
                <x-public.form.checkbox name="preview_consent" label="I understand this form is a non-submitting design preview." />
                <div class="flex flex-wrap gap-3"><x-public.button type="button">Button preview</x-public.button><x-public.button type="button" variant="secondary">Secondary action</x-public.button></div>
            </form>
        </div>
    </x-public.section>

    <x-public.cta eyebrow="Prepared for growth" heading="A stable public foundation for the phases ahead" description="Dynamic navigation, branding, and footer management are intentionally deferred to Phase 7." variant="gradient">
        <x-slot:primary><x-public.button href="#foundation" variant="light">Review components</x-public.button></x-slot:primary>
        <x-slot:secondary><x-public.button href="{{ route('login') }}" variant="outline-dark">Administrator login</x-public.button></x-slot:secondary>
    </x-public.cta>
</x-layouts.public-site>
