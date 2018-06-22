

@extends("layout")

@section("contents")
@php
    $data = $data ?? optional();
@endphp

<section id="offices" class="container">
<h2 class="text-center">Office & Campus Management</h2>
@php
    $tab = request()->tab ?? "offices"
@endphp

<div class="row">
    <div class="">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{textIf($tab == 'offices', 'active')}}" href="?tab=offices">Offices</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{textIf($tab == 'campuses', 'active')}}" href="?tab=campuses">Campuses</a>
            </li>
        </ul>
    </div>
    <div class="col-12 card card-body">
        @if ($tab == "offices")
            @include("incl/admin-offices")
        @else
            @include("incl/admin-campuses")
        @endif
    </div>
</div>
</section>
@endsection

