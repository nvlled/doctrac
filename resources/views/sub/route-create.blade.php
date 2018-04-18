

<link rel="stylesheet" href="{{asset('css/util.css')}}">
<section id="dispatch">


<div class="mag10">
    <table class="route">
        <thead>
        </thead>
        <tbody>
        </tbody>
    </table>

    <div>
        <label><input type="radio" name="rtype"
                      value="serial" checked> serial</label>
        <label><input type="radio" name="rtype"
                      value="parallel"> parallel</label>
    </div>

    <select name="campuses"></select>
    <select name="offices"></select>
    <button name="add">add</button>
</div>

<hr>
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
        var api = routeCreate = RouteCreate(graph, {
            showTable: true,
            showType: true,
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
