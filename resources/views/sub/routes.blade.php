
<section id="doc-history">
    <input id="document" value="{{$doc ?? ""}}" type="hidden">
    <input id="user" value="{{$user ?? ""}}" type="hidden">

    <h2 class="title">
        <span class='contents'></span>
        (<small class='type'>*</small>)
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
        <span class='classification'></span>
    </p>
    <p class='info'>
        <strong>details:</strong>
        <span class='details'></span>
    </p>
    <p class='info attachment'><strong>attachment</strong>: <a href="#" target="_blank">filename.docx</a></p>

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
            <th>id</th>
            <th>campus</th>
            <th>office</th>
            <th>status</th>
        </tr>
        </thead>
        <tbody>
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
    <script src="{{asset('js/sub/routes.js')}}"></script>
</section>
