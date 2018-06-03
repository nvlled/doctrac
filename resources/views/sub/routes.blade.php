
<section id="doc-history">
    <input id="document" value="{{$doc ?? ""}}" type="hidden">
    <input id="user" value="{{$user->toJson()}}" type="hidden">

    <h2 class="title center">
        <span class='contents'>{{$doc->title}}</span>
        <br>
        <small class='trackingId'>({{$doc->trackingId}})</small>
    </h2>

    <div class="left-col">
        <p class="info"><strong>state: </strong>
        <span class="doc-state {{$doc->state}}">
            {{$doc->state}}
        </span>
        </p>
        <p class="info"><strong>type:</strong>
        <span class="type">
            {{$doc->type}}
        </span>
        </p>
        <p class='info'>
        <strong>classification:</strong>
        <span class='classification'>{{$doc->classification}}</span>
        </p>
    </div>



    <div class="right-col">
        <p class='info {{!$action ? "hidden" : ""}}'>
        <strong>action:</strong>
        <a href="{{$routeLink}}" class='action'>
            {{$action}}
        </a>
        </p>

        <div class='info attachment'>
            <strong>attachment</strong>:
            <a href="{{$doc->attachment_url}}" 
               target="_blank">{{$doc->attachment_filename}}
            </a>
            <span class="{{hiddenIf(!$user || !$user->ownsDocument($doc))}}">
                <ul class="errors inline indent15"></ul>
                <!-- TODO--!>
                <button class="change small">change</button>
                <input class="hidden" type="file" name="attachment">
                <button class="hidden upload small">upload new file</button>
                <script>
                </script>
            </span>
        </div>
        <p class='info'>
            <strong>details:</strong>
            <span class='details'>{{$doc->details}}</span>
        </p>
    </div>

    <table class='full routes'>
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
            <th>actions taken</th>
            <th>time elapsed</th>
            @if ($doc->type == "serial")
                <th>status</th>
                <th>annotations</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
        $root = @$routes[0];
        @endphp
        @foreach ($routes as $route)
        <tr>
            @php
            $office = optional($route->office);
            @endphp
            <td class='{{textIf($doc->type == "parallel", "indent-{$route->depth}")}}'>
                <a href="{{$route->link}}">{{$office->complete_name}}</a>
            </td>
            <td>{{$route->actionTaken}}</td>
            <td>{{$route->time_elapsed}}</td>
            @if ($doc->type == "serial")
                <td>{{$route->approvalState}}</td>
                <td>{{$route->annotations}}</td>
            @endif
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
    <input id="trackingId" type="hidden" value="{{$doc->trackingId}}">
    <script>
    window.addEventListener("load", function() {
        var $inputFile = $("input[name=attachment");
        var $uploadBtn = $("button.upload");
        var trackingId = $("input#trackingId").val().trim();
        var $fileLink  = $("div.attachment a");
        var channel = UI.createChannel("doc."+trackingId);
        var $inputFile = $(".info.attachment input[name=attachment]");
        var $changeBtn = $(".info.attachment button.change");

        $changeBtn.click(function(e) {
            e.preventDefault();
            $inputFile.click();
            console.log("X");
        });
        $inputFile.change(function() {
            if ($inputFile[0].files.length > 0) {
                $uploadBtn
                    .removeClass("hidden")
                    .attr("disabled", false)
                    .show();
            } else {
                $uploadBtn.addClass("hidden").hide();
            }
        });

        if (!channel)
            return;

        $uploadBtn.click(function(e) {
            var file = $inputFile[0].files[0];
            UI.uploadFile(trackingId, $uploadBtn, file)
                .then(function(href) {
                    UI.flashMessage("file uploaded: " + file.name, "file-upload");
                    $fileLink.text(file.name).attr("href", href);
                    $uploadBtn.addClass("hidden").hide();
                });
        });

        channel.listen("DocUpdate", function(e) {
            console.log("document update", e);
            var msg = $("<a href='#'>document updated, click to reload</a>")[0];
            msg.onclick = function(e) {
                e.preventDefault();
                location.reload();
            };
            UI.flashMessage(msg, "doc-update");
        });
    });
    </script>
    <style>
    #site-wrapper .site-contents > section {
        max-width: 900px;
    }
    #doc-history {
        padding: 10px;
        display: grid;
        grid-tempate-columns: repeat(2, 1fr);
        max-width: 850px;
    }
    #doc-history .title {
        grid-row: 1 / 2;
        grid-column: 1 / 3;
    }
    #doc-history .left-col {
        grid-row: 2 / 3;
        grid-column: 1 / 2;
    }
    #doc-history .right-col {
        grid-row: 2 / 3;
        grid-column: 2 / 3;
    }
    #doc-history table {
        grid-row: 3 / 4;
        grid-column: 1 / 3;
    }

    #doc-history .trackingId {
        font-size: 16px;
        color: var(--primary-color);
    }

    </style>
    </table>
</section>
