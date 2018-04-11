

<link rel="stylesheet" href="{{asset('css/util.css')}}">
<section id="dispatch">


<div class="mag10">
    <div>
        <label><input type="radio" name="rtype" 
                      value="serial" checked> serial</label>
        <label><input type="radio" name="rtype" 
                      value="parallel"> parallel</label>
    </div>

    <select name="campuses"></select>
    <select name="offices"></select>
    <button>add</button>

</div>

<script src='{{asset("js/office-graph.js")}}'></script>

<script>
function loadList(node, data) {
    var $node = $(node);
    $node.html("");
    (data||[]).forEach(function(c) {
        var $opt = $("<option>"); 
        $opt.attr("value", c.id);
        $opt.text(c.name);
        $node.append($opt);
    });
}

function RouteCreate(args) {
    this.type = "serial";
    this.offices = [];
    this.graph = args.graph;
    this.cmbCampuses = args.cmbCampuses;
    this.cmbOffices = args.cmbOffices;

    this.cmbCampuses.change(function() {
        var $option = this.cmbCampuses.find("option:checked");
        if ($option.length == 0)
            return;
        var campusId = $option.val();
        this.changeCampus(campusId);
    }.bind(this));

    loadList(this.cmbCampuses, this.graph.getCampuses());
    this.cmbCampuses.change();
}

RouteCreate.prototype = Object.assign(RouteCreate.prototype, {
    changeCampus: function(campusId) {
        loadList(this.cmbOffices, this.graph.getLocalOffices(campusId));
    },
    changeOffice: function(campusId) {
    },

    setCampus: function(id) {
    },
    setOffice: function(id) {
    },

    push: function(officeId) {
    },
    pop: function() {
    },
});

function load() {
    OfficeGraph.fetch().then(function(graph) {
        var $cmbCampuses = $("select[name=campuses]");
        var $cmbOffices = $("select[name=offices]");

        var routeCreate = new RouteCreate({
            graph: graph,
            cmbCampuses: $cmbCampuses,
            cmbOffices: $cmbOffices,
        });
    });
}
window.addEventListener("load", load);
</script>
</section>
