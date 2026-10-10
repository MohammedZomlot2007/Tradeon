<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ $base }}/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    <url><loc>{{ $base }}/explore</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
    <url><loc>{{ $base }}/businesses</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
    <url><loc>{{ $base }}/manufacturers</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
    <url><loc>{{ $base }}/suppliers</loc><changefreq>daily</changefreq><priority>0.7</priority></url>
    <url><loc>{{ $base }}/requests</loc><changefreq>daily</changefreq><priority>0.7</priority></url>
    @foreach($companies as $company)
    <url><loc>{{ $base }}/company/{{ $company->slug }}</loc><lastmod>{{ $company->updated_at?->toDateString() }}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>
    @endforeach
</urlset>
