<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ([['home', '1.0', 'daily'], ['about', '0.8', 'monthly'], ['team', '0.5', 'monthly'], ['get-involved', '0.8', 'monthly'], ['campaigns.index', '0.9', 'daily'], ['projects.index', '0.8', 'weekly'], ['events.index', '0.7', 'weekly'], ['stories.index', '0.7', 'weekly'], ['news.index', '0.8', 'daily'], ['gallery.index', '0.6', 'weekly'], ['contact.create', '0.6', 'monthly']] as [$name, $priority, $frequency])
        <url>
            <loc>{{ route($name) }}</loc>
            <changefreq>{{ $frequency }}</changefreq>
            <priority>{{ $priority }}</priority>
        </url>
    @endforeach

    @foreach ($campaigns as $campaign)
        <url>
            <loc>{{ route('campaigns.show', $campaign->slug) }}</loc>
            <lastmod>{{ $campaign->updated_at->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    @foreach ($projects as $project)
        <url>
            <loc>{{ route('projects.show', $project->slug) }}</loc>
            <lastmod>{{ $project->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    @foreach ($events as $event)
        <url>
            <loc>{{ route('events.show', $event->slug) }}</loc>
            <lastmod>{{ $event->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach

    @foreach ($stories as $story)
        <url>
            <loc>{{ route('stories.show', $story->slug) }}</loc>
            <lastmod>{{ $story->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach

    @foreach ($posts as $post)
        <url>
            <loc>{{ route('news.show', $post->slug) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
