
<section id="dispatch" class="container mt-3 mb-5">
<form class="form-style-1 border offset-lg-2 col-lg-8">
    <h2 class="center">Dispatch Document</h2>

    <!--probably unused -->
    <!--------------------!>

    <div class="row ">
        <div class="col">
        <input class="form-control title" type="text" name="title" placeholder="document name or title">
        </div>
    </div>
    <div class="row ">
        <div class="col">
        <textarea class="form-control details" name="details" rows="7" placeholder="document details"></textarea>
        </div>
    </div>
    <div class="row ">
        <div class="col">
        <textarea class="form-control annotations" name="annotations" rows="4" placeholder="notes/annotations"></textarea>
        </div>
    </div>

    <div class="row">
        <label class="col-form-label col-lg-3 col-sm-12">classification level</label>
        <div class="col-lg-5 col-md-12">
            <select class='custom-select classification form-control'>
                @foreach (\App\Enums::$classification as $level)
                    <option value='{{$level}}'>{{$level}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <label class="col-lg-3 col-md-12" for="customFile">choose attachment</label>
        <div class="col-lg-5 col-md-12">
            <input name="attachment" type="file" class="form-control-file" id="customFile">
        </div>
    </div>

    <div class="row route-create">
        <div class="offset-1 col-10 bg-light p-3">
            <h3>Office destinations</h3>
            <div class="dom p-2"></div>
        </div>
        <div class="col-3 text-center d-flex flex-column align-self-end justify-content-start">
        </div>
    </div>

    <div class="row mt-2 text-center">
    <ul class="col-12 center errors text-danger"></ul>
    </div>

    <div class="row text-center d-flex align-items-center justify-content-center">
        @include("sub.loading")
    </div>
    <div class="row mt-1 ">
        <button class="offset-4 col-5 btn btn-primary half send action">send </button>
    </div>

    <div class="row ">
        <small class="offset-3 col-7 text-secondary">
        *note:
        serial: documents are passed from one office to another
        parallel: documents are passed to all the offices at the same time
        </small>
    </div>
</form>
<script src='{{asset("js/office-graph.js")}}'></script>
<script src='{{asset("js/view/route-create.js")}}'></script>
<script src='{{asset("js/sub/office-selection.js")}}'></script>
<script src="{{asset('js/sub/dispatch.js')}}"></script>
<style></style>
<link rel="stylesheet" href="/css/route-create.cssXX">
</section>


