

@extends("layout")

@section("contents")
@php
    $docs = $docs ?? collect();
    $page = $page ?? 1;
    $pageInfo = paginate($docs, $page);
    $currentName = $currentName ?? "all";
@endphp
<section id="doc-lists">
    <ul class="list-names inline">
        @foreach ($listNames as $name)
            <li>
                <a class="{{textIf($name == $currentName, 'bracket')}}"
                   href="?name={{$name}}">{{$name}}</a>
            </li>
        @endforeach
    </ul>
    <ol start="{{$pageInfo->startNo}}" class="docs">
        @foreach ($pageInfo->items as $doc)
            <li>
                <a href="{{$doc->document_link}}">
                    {{$doc->document_title}}
                    <small>({{$doc->trackingId}})</small>
                </a>
            </li>
        @endforeach
    </ol>
    @if (isEmpty($docs))
        <div class="center"><em class="block mag20">(none)</em></center>
    @endif
    <div class="page-nav">
        @if ($pageInfo->numPages > 1)
            <ul class="inline">
            @foreach (range(1, $pageInfo->numPages) as $p)
                <li><a class="{{textIf($p == $page, "bracket")}}"
                        href="?name={{$currentName}}&page={{$p}}">{{$p}}</a>
                </li>
            @endforeach
            </ul>
        @endif
    </div>
    <link rel="stylesheet" href="{{asset('css/doc-lists.css')}}">
</section>
@endsection
