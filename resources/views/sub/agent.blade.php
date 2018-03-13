
<section id="agent">
    <div class="center">
        <h3 class=''>
            <span class='office-name'>office name</span>
        </h3>
        <span class="subtext">(<small class='subtext office-id'></small>)</span>
    </div>
    <p class="info">
        <strong>show</strong>
        <label><input checked type="radio" name="all"> all</label>
        <label><input type="radio" name="list-type" value="incoming"> incoming</label>
        <label><input type="radio" name="list-type" value="delivering"> delivering</label>
        <label><input type="radio" name="list-type" value="processing"> processing</label>
        <label><input type="radio" name="list-type" value="final"> final</label>
    </p>
    <hr>

    <div class="hidden list">
        <h4>Incoming</h4>
        <ul id="incoming">
            <em>(none)</em>
        </ul>
    </div>

    <div class="hidden list">
        <h4>Processing</h4>
        <ul id="processing">
            <em>(none)</em>
        </ul>
    </div>

    <div class="hidden list">
        <h4>Delivering</h4>
        <ul id="delivering">
            <em>(none)</em>
        </ul>
    </div>

    <div class="hidden list">
        <h4>Forwarded</h4>
        <ul id="forwarded">
            <em>(none)</em>
        </ul>
    </div>

    <div class="hidden list">
        <h4>Received/Final</h4>
        <ul id="final">
            <em>(none)</em>
        </ul>
    </div>

    <div id="view-document">
        <h2><span class='title'>doc title</span> (<small class='trackingId'>tracking ID</small>)</h2>
        <p class=''>status: <span class='status'>document details</span></p>
        <pre>details: <span class='details'></span></pre>
        <pre>annotations: <span class='annotations'></span></pre>

        <div class=''>
            <button class='action hidden'>send/receive</button>
        </div>
        <pre>seen by: <span class='seen-by'></span></pre>
        <hr>
        <p>activity log: </p>
        <ul class='activities'>
        </ul>
    </div>

    <script src="{{asset('js/sub/agent.js')}}"></script>
</section>
