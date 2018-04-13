
// -------------------------------------------
// id | campus | office | action
// 1  | A      | X      | [✗]
// 2  | A      | Y      | [✗]
// 3  | A      | Z      | [✗]
// 4  | B      | A      | [✗]
// -------------------------------------------
// | A | X | [add]


function RouteCreate(graph, args) {
    args = args || {};
    var self =  {
        vm: null,
        graph: graph,
        rows: [],
        type: args.type || "serial",
        officeIndex: args.officeIndex || 0,
        campusIndex: args.campusIndex || 0,
        message: "",

        getCampus: function(row) {
            return self.graph.getCampus(row.campusId);
        },
        getRows: function() {
            return self.rows;
        },

        isAdded: function(office) {
            return self.rows.some(function(off) {
                return office.id == off.id;
            });
        },

        updateOfficeSelection: function() {
            var office = self.getLastOffice();
            if (self.getType == "serial") {
                self.selectOfficeExcept([office.id]);
            } else {
                self.selectOfficeExcept(self.rows.map(function(o) {
                    return o.id;
                }));
            }
        },

        insertRow: function(e) {
            self.message = "";
            var lastOffice = self.getLastOffice();

            var office = self.getSelectedOffice();

            if (self.type == "serial") {
                if (lastOffice && office.id == lastOffice.id) {
                    self.message = "cannot add office in succession";
                    console.log("**", self.message);
                } else {
                    self.rows.push(office);
                }
            } else {
                if (self.isAdded(office)) {
                    self.message = "office is already added";
                } else {
                    self.rows.push(office);
                }
            }
            self.updateOfficeSelection();

            // # serial
            // after add
            // if lastrow is not records, disable campus selection
            // disable office option that is equal to last row

            // # parallel
            // disable all options that are already added

            // actually, in order to avoid invalid state,
            // I should just allow deletion of the last row only.
            // * Set starting office

            self.vm.redraw();
        },

        removeRow: function(row) {
            var index = -1;
            self.rows.forEach(function(row_, i) {
                if (row == row_)
                    index = i;

            });
            if (self.type == "serial") {
                var offices = self.getOffices();
                var selOffice = self.getSelectedOffice();
                self.selectOfficeExcept([row.id]);
            }

            if (index < 0) {
                return;
            }
            self.rows.splice(index, 1);
            self.vm.redraw();

        },

        selectOfficeExcept: function(ids) {
            var selOffice = self.getSelectedOffice();
            var isFiltered = function(id) {
                return ids.some(function(id_) {
                    return id == id_;
                });
            }
            if (selOffice && !isFiltered(selOffice.id))
                return;
            var offices = self.getOffices();
            for (var i = 0; i < offices.length; i++) {
                if (! isFiltered(offices[i].id)) {
                    self.officeIndex = i;
                    break;
                }
            }
            if (self.officeIndex < 0 || self.officeIndex >= offices.length)
                self.officeIndex = 0;
        },

        getType: function() {
            return self.type;
        },

        changeType: function(name) {
            if (self.rows.length > 0 && self.type != name) {
                let proceed = confirm(
                    "changing the type will clear all the rows, continue?"
                );
                if (!proceed)
                    return;
            }
            self.rows.splice(0);
            self.type = name;
        },

        getSelectedCampus: function() {
            return self.getCampuses()[self.campusIndex];
        },

        getSelectedOffice: function() {
            return self.getOffices()[self.officeIndex];
        },

        getDisabledCampuses: function() {
            return [];
        },
        getDisabledOffices: function() {
            if (self.type == "serial" && self.rows.length > 0) {
                var lastOffice = self.getLastOffice();
                return [lastOffice.id];
            } else if (self.type == "parallel") {
                return self.rows.map(function(o) { return o.id});
            }
            return [];
        },

        getLastOffice: function() {
            return self.rows[self.rows.length-1];
        },

        changeCampus: function(e) {
            self.campusIndex = self.vm.refs.campuses.el.selectedIndex;
            self.updateOfficeSelection();
        },
        changeOffice: function(e) {
            self.officeIndex = self.vm.refs.offices.el.selectedIndex;
        },

        getOffices: function() {
            var lastOffice = self.getLastOffice();
            var campus = self.getSelectedCampus();
            console.log("campusIndex", self.campusIndex);
            if (campus) {
                var offices = self.graph.getLocalOffices(campus.id);
                if (lastOffice && lastOffice.campusId != campus.id) {
                    offices = offices.filter(function(off) {
                        return off.gateway;
                    });
                }
                return offices;
            }
            return [];
        },
        getCampuses: function() {
            return self.graph.getCampuses();
        },
    }
    self.vm = domvm.createView(RouteCreateView, self);
    return self;
}

function RouteCreateView(vm, api) {
    var el = domvm.defineElement;
    var tx = domvm.defineText;
    var cm = domvm.defineComment;
    var sv = domvm.defineSvgElement;
    var vw = domvm.defineView;
    var iv = domvm.injectView;
    var ie = domvm.injectElement;

    // ideally, I'd want to define my css styles here
    //var style = {
    //    "table.route-create": {
    //        width: "100%",
    //    },
    //}

    function createRadio(name) {
        return el("label", [
            el("input",
                {
                    name: "type",
                    value: name,
                    type: "radio",
                    onclick: [api.changeType, name],
                    checked: api.getType() == name,
                }),
            name,
        ]);
    }

    return function() {
        var rows = api.getRows();
        var table = el("table.route-create", [
            el("thead", [
                el("tr", [
                    el("th", "id"),
                    el("th", "campus"),
                    el("th", "office"),
                    el("th", "action"),
                ]),
            ]),
            el("tbody", {_key: new Date()},
                rows.map(function(row, index) {
                    var campus = api.getCampus(row) || {};
                    var isLast =
                        index == rows.length - 1 && rows.length != 1;
                    return el("tr", {_key: "off-"+row.id+"-"+(+new Date())}, [
                        el("td", row.id),
                        el("td", campus.name),
                        el("td", row.name),
                        el("td", [
                            isLast && el(
                                "button",
                                {onclick: [api.removeRow, row]},
                                "✗",
                            ) ,
                        ]),
                    ]);
                })
            ),
        ]);

        var disabledCampuses = api.getDisabledCampuses();
        var disabledOffices = api.getDisabledOffices();

        var lastOffice = api.getLastOffice();
        var isCampusSelectedDisabled =
            api.getType() == "serial"
            && lastOffice && !lastOffice.gateway;

        var panel = el("div.panel", [
            el("div", api.message),
            el(
                "select.campuses[name=campuses]",
                {
                    _ref: "campuses",
                    disabled: isCampusSelectedDisabled,
                    onchange: [api.changeCampus]
                },
                api.getCampuses().map(function(obj, idx) {
                    return el("option", {
                        value: obj.id,
                        selected: idx == api.campusIndex,
                    }, obj.name);
                })
            ),
            el(
                "select.offices[name=offices]",
                {
                    _ref: "offices",
                    onchange: [api.changeOffice]
                },
                api.getOffices().map(function(obj, idx) {
                    return el("option", {
                        value: obj.id,
                        disabled: disabledOffices.indexOf(obj.id) >= 0,
                        selected: idx == api.officeIndex,
                    }, obj.name);
                })
            ),
            el("button", {onclick: api.insertRow}, "add"),
            el("div", [
                createRadio("serial"),
                el("span", " "),
                createRadio("parallel"),
            ]),
        ]);
        return el("div.route-create", [
            table,
            panel,
        ]);
    }
}
