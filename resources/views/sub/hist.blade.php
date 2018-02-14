

<section id="doc-history">
    tracking ID
    <input name="trackingId"
        id="doc-history-userid"
        class="half trackingId autocomplete local-save"
        placeholder="search for tracking ID or title"
        data-hidetext=true
        data-format="{title}"
        data-key="trackingId"
        data-url="/api/docs/search">

    <h3 class="title">
        <span class='contents'></span>
        (<small class='type'>*</small>)
    </h3>
    <pre class='details'></pre>
    <p>Routes</p>
    <h4 class='origin hidden'>Document origin: <span class='contents'></span></h4>
    <table class='full'>
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
    </table>
    <br>
    <div class="center">
        <div class="send-data">
            <textarea name="annotation" rows="5" class="full annots"
            placeholder="comments, notes or annotation" ></textarea>
            <br>
            Destination: <select class="offices"></select>
        </div>
        <button class='action half'>SEND / RECEIVE / ABORT SEND / </button>
    </div>
    <script src="{{asset('js/sub/routes.js')}}"></script>
</section>
