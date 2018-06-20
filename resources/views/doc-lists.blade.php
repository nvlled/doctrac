

@extends("layout")

@section("contents")
@php
    $docs = $docs ?? collect();
    $page = $page ?? 1;
    $pageInfo = paginate($docs, $page);
    $currentName = $currentName ?? "";
@endphp
<section id="doc-lists" class="container">
    <div class="row">
        <ul class="list-names nav nav-tabs">
            @foreach ($listNames as $name)
                <li class="nav-item">
                    <a class="nav-link {{textIf($name == $currentName, 'active')}}"
                    href="?name={{$name}}">{{$name}}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="row">
        <div class="col-11">
            <ol start="{{$pageInfo->startNo}}" class="docs list-group">
                @foreach ($pageInfo->items as $i=>$doc)
                    <li class="list-group-item">
                        <a href="{{$doc->document_link}}">
                            <span class="d-inline-block pr-2">{{$i+1}}.</span>
                            <strong>{{$doc->document_title}}</strong>
                            <small>({{$doc->trackingId}})</small>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
    @if (isEmpty($docs))
        <div class="center"><em class="block mag20">(none)</em></center>
    @endif
    <div class="page-nav">
        @if ($pageInfo->numPages > 1)
            <ul class="pagination">
            @foreach (range(1, $pageInfo->numPages) as $p)
                <li class="page-item {{textIf($p == $page, "active")}}"><a class="page-link"
                        href="?name={{$currentName}}&page={{$p}}">{{$p}}</a>
                </li>
            @endforeach
            </ul>
        @endif
    </div>
    <link rel="stylesheet" href="{{asset('css/doc-lists.css')}}">
</section>
@endsection
