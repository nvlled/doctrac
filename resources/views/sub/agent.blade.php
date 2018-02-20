
<section id="agent">
    <h3 class='center'>(<span class='office-id'>0000</span>)
        <span class='office-name'>office name</span></h3>
    <p class='center'><span class='user-name'>user name</span></p>
    <div>
        <h4>Incoming</h4>
        <ul id="incoming">
            <em>(none)</em>
        </ul>
    </div>
    <div>
        <h4>Processing</h4>
        <ul id="held">
            <em>(none)</em>
        </ul>
    </div>
    <div>
        <h4>Delivering</h4>
        <ul id="dispatched">
            <em>(none)</em>
        </ul>
    </div>

    <div>
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

