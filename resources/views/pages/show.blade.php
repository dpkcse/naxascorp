<x-layouts.public-site :settings="$settings" :site-name="$siteName" :legal-name="$legalName" :navigation="$navigation" :chrome="$chrome" :page-title="$page['og_title']" :description="$page['og_description'] ?: $description" :canonical="$canonical" :robots="$page['robots']" :image="$page['og_image'] ? asset($page['og_image']) : null">
    @if($preview)<div class="sticky top-0 z-50 bg-amber-300 px-4 py-3 text-center text-sm font-bold text-amber-950" role="status">Preview Mode — this page is not the public cached version.</div>@endif
    @include($templateView, ['page' => $page])
</x-layouts.public-site>
