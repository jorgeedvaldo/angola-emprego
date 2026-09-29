<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:media="http://search.yahoo.com/mrss/"
    >
<channel>
    <title>{{ $titulo }}</title>
    <atom:link href="{{ $proprio }}" rel="self" type="application/rss+xml" />
    <link>{{ $ligacao }}</link>
    <description>{{ $descricao }}</description>
    <lastBuildDate>{{ $construido->toRssString() }}</lastBuildDate>
    <language>pt-PT</language>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>
    <generator>Angola Emprego</generator>
@foreach($itens as $item)
    <item>
        <title>{{ $item['titulo'] }}</title>
        <link>{{ $item['ligacao'] }}</link>
        <guid isPermaLink="true">{{ $item['ligacao'] }}</guid>
        <pubDate>{{ $item['data']->toRssString() }}</pubDate>
        <dc:creator><![CDATA[Angola Emprego]]></dc:creator>
@foreach($item['categorias'] as $categoria)
        <category><![CDATA[{!! $categoria !!}]]></category>
@endforeach
        <description><![CDATA[{!! $item['resumo'] !!}]]></description>
        <content:encoded><![CDATA[{!! $item['conteudo'] !!}]]></content:encoded>
@if($item['imagem'])
        <media:content url="{{ $item['imagem'] }}" medium="image" />
@endif
    </item>
@endforeach
</channel>
</rss>
