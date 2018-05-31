
<section id="dispatch">
    <form class="form-style-1">
    <h2 class="center">Dispatch Document</h2>
    <p class='user-name hidden'></p>
    <h3 class='user-office hidden'></h3>
    <input type="text" name="title" class="title" placeholder="document name or title">
    <textarea name="details" rows="7" class="details"
        placeholder="document details"></textarea>
    <textarea name="annotations" rows="4" class="annotations"
        placeholder="notes/annotations"></textarea>

    <p>
        Classification level:
        <select class='classification'>
            @foreach (\App\Enums::$classification as $level)
            <option value='{{$level}}'>{{$level}}</option>
            @endforeach
        </select>
    </p>

    <p class="">
        Attachment:
        <input name="attachment" type="file">
    </p>

    <div class="route-create">
        <h3>Office destinations</h3>
        <div class="dom"></div>
    </div>

    <div class="center">
        <ul class="center errors"></ul>
        <p class='message' style='color: #050'><p>
        @include("sub.loading")
        <button class="half send action pure-button pure-button-primary">Send</button>
    </div>

    <p style="font-size: 15px; color: gray">
    *note: <br>
    serial: documents are passed from one office to another<br>
    parallel: documents are passed to all the offices at the same time
    </p>
    </form>
    <script src='{{asset("js/office-graph.js")}}'></script>
    <script src='{{asset("js/view/route-create.js")}}'></script>
    <script src='{{asset("js/sub/office-selection.js")}}'></script>
    <script src="{{asset('js/sub/dispatch.js')}}"></script>
    <style>
    section#dispatch {
    }
    section#dispatch form {
        max-width: 650px;
    }
    section#dispatch form > .route-create {
        border-left: 10px solid #f415;
        padding: 5px;
        padding-left: 20px;
        background-color: #3447;
    }
    button.send {
        width: 300px;
        height: 50px;
    }
    </style>
    <link rel="stylesheet" href="/css/route-create.css">
</section>

