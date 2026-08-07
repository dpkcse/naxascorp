<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>{{ $root }}/</loc></url><url><loc>{{ $root }}/solutions</loc></url><url><loc>{{ $root }}/products</loc></url>
<url><loc>{{ $root }}/industries</loc></url>
@foreach($industries as $industry)<url><loc>{{ $root }}/industries/{{ $industry->slug }}</loc><lastmod>{{ $industry->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($products as $product)<url><loc>{{ $root }}/products/{{ $product->slug }}</loc><lastmod>{{ $product->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($solutions as $solution)<url><loc>{{ $root }}/solutions/{{ rawurlencode($solution->slug) }}</loc><lastmod>{{ $solution->updated_at->toAtomString() }}</lastmod></url>@endforeach @foreach($pages as $page)<url><loc>{{ $root }}/{{ rawurlencode($page->slug) }}</loc><lastmod>{{ $page->updated_at->toAtomString() }}</lastmod></url>@endforeach</urlset>
