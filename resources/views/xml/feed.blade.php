<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:georss="http://www.georss.org/georss"
	xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
	>

    <channel>
        <title>Angola Emprego</title>
        <atom:link href="https://angolaemprego.com/feed/" rel="self" type="application/rss+xml" />
        <link>https://angolaemprego.com/</link>
        <description>Vagas de emprego, estágio e bolsas de estudo</description>
        <lastBuildDate>{{ date_format(new DateTime($posts[0]['created_at']), DATE_ATOM) }}</lastBuildDate>
        <language>pt-PT</language>
        <sy:updatePeriod>hourly</sy:updatePeriod>
        <sy:updateFrequency>1</sy:updateFrequency>
        <generator>https://wordpress.org/?v=6.4.2</generator>

        <image>
            <url>{{ asset('storage/images/logo-full.jpg') }}</url>
            <title>Angola Emprego</title>
            <link>{{ url('/') }}</link>
            <width>32</width>
            <height>32</height>
        </image>

        <site xmlns="com-wordpress:feed-additions:1">227777157</site>

        @foreach($posts as $post)
            <item>
                <title>{{$post->title}}</title>
                <link>{{ url('/'. $post->slug) }}</link>
                <dc:creator><![CDATA[Edivaldo Jorge]]></dc:creator>
                <pubDate>{{ date_format(new DateTime($post['created_at']), DATE_ATOM) }}</pubDate>
                <category><![CDATA[Emprego]]></category>
                <category><![CDATA[Estágio]]></category>
                <guid isPermaLink="false">{{ url('/'. $post->slug) }}</guid>
                <description><![CDATA[<p>{!! \Illuminate\Support\Str::limit(strip_tags($post->description), 402, $end='...') !!}</p><p>O conteúdo <a href="{{ url('/'. $post->slug) }}">{{$post->title}}</a> aparece primeiro em <a href="{{ url('/') }}">Angola Emprego</a>.</p>
                ]]></description>
                <content:encoded><![CDATA[{{$post->description}}]]></content:encoded>
                @if($post->image)
                    <media:content url="{{ asset('storage/' . $post->image) }}" medium="image" />
                @endif
                <post-id xmlns="com-wordpress:feed-additions:1">{{$post->id}}</post-id>
            </item>
        @endforeach
    </channel>
</rss>
