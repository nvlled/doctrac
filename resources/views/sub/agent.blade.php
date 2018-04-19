
<section id="agent">
    <div class="center">
        <h3 class=''>
            @php
            $user   = optional(Auth::user());
            $office = optional(Auth::user())->office;
            @endphp
            <span class='office-name'>{{$office ? $office->complete_name : ""}} </span>
        </h3>
        <span class="subtext">(<small class='subtext office-id'>{{$user->username}}</small>)</span>
    </div>
    <p class="info radios">
        <strong>show</strong>
        <label><input type="radio" name="list-type" value="all"> all</label>
        <label><input type="radio" name="list-type" value="incoming"> incoming</label>
        <label><input type="radio" name="list-type" value="delivering"> delivering</label>
        <label><input type="radio" name="list-type" value="processing"> processing</label>
        <label><input type="radio" name="list-type" value="final"> final</label>
    </p>
    <hr>

    <div class="main list">
        @include("sub.loading")
        <em class="none hidden">(none)</em>
        <ul id="main">
        </ul>
    </div>

    <script src="{{asset('js/sub/agent.js')}}"></script>
</section>
