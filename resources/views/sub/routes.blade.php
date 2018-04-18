
<section id="doc-history">
    <input id="document" value="{{$doc ?? ""}}" type="hidden">
    <input id="user" value="{{$user ?? ""}}" type="hidden">

    <h2 class="title">
        <span class='contents'>{{$doc->title}}</span>
        (<small class='type'>{{$doc->type}}</small>)
    </h2>
    <p class="info"><strong>state: </strong>
        <span class="doc-state {{$doc->state}}">
            {{$doc->state}}
        </span>
    </p>
    <p class="info"><strong>tracking ID:</strong>
        <span class="trackingId">
            {{$doc->trackingId}}
        </span>
    </p>
    <p class='info'>
        <strong>classification level:</strong>
        <span class='classification'>{{$doc->classification}}</span>
    </p>
    <p class='info'>
        <strong>details:</strong>
        <span class='details'>{{$doc->details}}</span>
    </p>
    <p class='info {{!$action ? "hidden" : ""}}'>
        <strong>action:</strong>
        <a href="{{$routeLink}}" class='action'>
            {{$action}}
        </a>
    </p>

    <p class='info attachment {{hiddenIf(!$doc->attachment)}}'>
        <strong>attachment</strong>:
        <a href="{{$doc->attachment_url}}" target="_blank">{{$doc->attachment_filename}}</a>
    </p>

    <table class='full'>
        <colgroup>
        <col span="1" style="width: inherit">
        <col span="1" style="width: 10px;">
        <col span="1" style="width: 10px;">
        <col span="1" style="width: 30px;">
        <col span="1" style="width: 45%;">
        </colgroup>
        <thead>
        <tr>
            <th>office name</th>
            <th>status</th>
            <th>approval</th>
            <th>time elapsed</th>
            <th>annotations</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($routes as $route)
        <tr>
            @php $office = optional($route->office) @endphp
            <td><a href="{{$route->link}}">{{$office->complete_name}}</a></td>
            <td>{{$route->status}}</td>
            <td>{{$route->approvalState}}</td>
            <td>{{$route->time_elapsed}}</td>
            <td>{{$route->annotations}}</td>
        </tr>
        @endforeach
        </tbody>
        <style>
        td {
            vertical-align: text-top;
        }
        td.time_elapsed {
            font-size: 13px;
            text-align: center;
        }
        td.approvalState {
            text-align: center;
            font-size: 25px;
        }
        </style>
    </table>
</section>
