
<link rel="stylesheet" href="{{asset('css/pure-min.css')}}">

<script src="{{asset('js/lib/domvm.dev.js')}}"></script>
<script>
domvm.config({
    onevent: function(e, node, vm, data, args) {
        vm.root().redraw();
    }
});
</script>

<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{asset('js/lib/j2c.js')}}"></script>
<script src="{{asset('js/events.js')}}"></script>
<script src="{{asset('js/util.js')}}"></script>
<script src="{{asset('js/ui.js')}}"></script>
<script src="{{asset('js/api.js')}}"></script>

<div class="dom"></div>
<script src='{{asset("js/view/route-create.js")}}'></script>
<style>
</style>

<script src='{{asset("js/office-graph.js")}}'></script>

<script>
var officeGraph;
var routeCreate;
function load() {
    OfficeGraph.fetch().then(function(graph) {
        officeGraph = graph;

        var offices = graph.getOffices();
        var currentOffice = offices[4]

        console.log("current office", currentOffice);
        var api = routeCreate = RouteCreate(graph, {
            showTable: true,
            //showType: true,
            currentOffice:  currentOffice,
            rows: [
                offices[0],
                offices[3],
                offices[7],
                offices[2],
                offices[1],
            ],
        });
        var vm = api.vm;
        vm.mount(document.querySelector("div.dom"));

    }).fail(function(error) {
        throw error;
    });
}
window.addEventListener("load", load);
</script>
</section>
