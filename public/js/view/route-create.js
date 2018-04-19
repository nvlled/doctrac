
// -------------------------------------------
// id | campus | office | action
// 1  | A      | X      | [✗]
// 2  | A      | Y      | [✗]
// 3  | A      | Z      | [✗]
// 4  | B      | A      | [✗]
// -------------------------------------------
// | A | X | [add]

// TODO:
// hideTable option
// load existing route
// add some styles

function RouteCreate(graph, args) {
    var util = {
        defbool: function(b, p) {
            if (typeof b == "boolean") {
                return b;
            }
            return p;
        },
    }

    var currentOffice = args.currentOffice;
    if ( ! currentOffice) {
        throw "currentOffice is required";
    }

    args = args || {};
    var rows = args.rows || [];

    var self =  {
        currentOffice: currentOffice,
        showTable: util.defbool(args.showTable, true),
        showType:  util.defbool(args.showType,  currentOffice.level > 1),
        showAddButton:  util.defbool(args.showAddButton,  true),
        vm: null,
        graph: graph,
        rows: rows,
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

        getRowIds: function() {
            return self.rows.map(function(row) {
                return row.id;
            });
        },

        isAdded: function(office) {
            return self.rows.some(function(off) {
                return office.id == off.id;
            });
        },

        updateOfficeSelection: function() {
            var office = self.getLastOffice();
            if (self.getType() == "serial") {
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
                self.selectOfficeExcept([selOffice.id]);
            }

            if (index < 0) {
                return;
            }
            self.rows.splice(index, 1);
            self.vm.redraw();

        },

        selectCampus: function(id) {
            var campuses = self.getCampuses();
            for (var i = 0; i < campuses.length; i++) {
                if (typeof id == "function" && id(campuses[i])) {
                    self.campusIndex = i;
                    break;
                } else if (campuses[i].id == id) {
                    self.campusIndex = i;
                    break;
                }
            }
            if (self.campusIndex < 0 ||
                self.campusIndex >= campuses.length) {
                self.campusIndex = 0;
            }
        },

        selectOffice: function(id) {
            var offices = self.getOffices();
            for (var i = 0; i < offices.length; i++) {
                if (typeof id == "function" && id(offices[i])) {
                    self.officeIndex = i;
                    break;
                } else if (offices[i].id == id) {
                    self.officeIndex = i;
                    break;
                }
            }
            if (self.officeIndex < 0 ||
                self.officeIndex >= offices.length) {
                self.officeIndex = 0;
            }
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
            if (self.officeIndex < 0 ||
                self.officeIndex >= offices.length) {
                self.officeIndex = 0;
            }
        },

        getType: function() {
            return self.type;
        },

        changeType: function(name, type) {
            if (self.rows.length > 0 && self.type != name) {
                var proceed = confirm(
                    "changing the type will clear all the rows, continue?"
                );
                if (!proceed) {
                    self.type = type;
                    return;
                }
            }
            self.rows.splice(0);
            self.type = name;

            var lastOffice = self.getLastOffice();
            var currentOffice = self.currentOffice;
            if (self.getType() == "parallel") {
                if (!currentOffice.main) {
                    self.selectCampus(currentOffice.campusId);
                } else {
                    self.selectOffice(function(off) {
                        return off.records;
                    });
                }
            }
            self.vm.redraw();
        },

        getSelectedCampus: function() {
            return self.getCampuses()[self.campusIndex];
        },

        getSelectedOffice: function() {
            return self.getOffices()[self.officeIndex];
        },

        getSelectedOfficeId: function() {
            return (self.getSelectedOffice()||{}).id;
        },

        getDisabledCampuses: function() {
            return [];
        },

        getDisabledOffices: function() {
            let ids = []
            if (self.type == "serial") {
                var lastOffice = self.getLastOffice();
                ids = [lastOffice.id];
            } else if (self.type == "parallel") {
                ids = self.rows.map(function(o) { return o.id});
                ids.push(self.currentOffice.id);
            }
            return ids;
        },

        getLastOffice: function() {
            return self.rows[self.rows.length-1] || self.currentOffice;
        },

        isCampusDisabled() {
            var lastOffice = self.getLastOffice();
            var currentOffice = self.currentOffice;
            if (currentOffice && self.getType() == "parallel") {
                return !currentOffice.main;
            }
            return self.getType() == "serial"
                && lastOffice && !lastOffice.gateway;

        },

        changeCampus: function(e) {
            self.campusIndex =
                self.vm.refs.campuses.el.selectedIndex;
            self.updateOfficeSelection();
            self.vm.redraw();
        },
        changeOffice: function(e) {
            self.officeIndex = self.vm.refs.offices.el.selectedIndex;
            self.vm.redraw();
        },

        getOffices: function() {
            var lastOffice = self.getLastOffice();
            var currentOffice = self.currentOffice;
            var campus = self.getSelectedCampus();
            if (campus) {
                var offices = self.graph.getLocalOffices(campus.id);
                if ((lastOffice && lastOffice.campusId != campus.id) ||
                    currentOffice.campusId != campus.id) {
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

        isDeadEnd: function() {
            var lastOffice = self.getLastOffice();
            return self.getType() == "serial" &&
                lastOffice.campusId != self.currentOffice.campusId;
        },
    }
    if (args.selectedOffice) {
        var off = args.selectedOffice;
        self.selectCampus(off.campusId);
        self.selectOffice(off.id);
    } else {
        self.selectCampus(currentOffice.campusId);
        self.selectOfficeExcept([currentOffice.id]);
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
                    onclick: [api.changeType, name, api.type],
                    checked: api.getType() == name,
                }),
            name,
        ]);
    }

    function renderTable() {
        var rows = api.getRows();
        return el("table.route-create", [
            el("thead", [
                el("tr", [
                    el("th", "campus"),
                    el("th", "office"),
                    el("th", ""),
                ]),
            ]),
            el("tbody", {_key: new Date()},
                rows.map(function(row, index) {
                    var campus = api.getCampus(row) || {};
                    var showX = api.type == "parallel" ||
                        (api.type == "serial" && index == rows.length-1);
                    return el("tr", {_key: "off-"+row.id+"-"+(+new Date())}, [
                        el("td", campus.name),
                        el("td", row.name),
                        el("td", [
                            showX && el(
                                "a",
                                {onclick: [api.removeRow, row]},
                                "✗",
                            ) ,
                        ]),
                    ]);
                })
            ),
        ])
    }

    return function() {
        var table = api.showTable ? renderTable() : null;
        var disabledCampuses = api.getDisabledCampuses();
        var disabledOffices = api.getDisabledOffices();
        var isDeadEnd       = api.isDeadEnd();

        var panel = el("div.panel", [
            el("div", [el("em", api.message)]),
            el(
                "select.campuses[name=campuses]",
                {
                    _ref: "campuses",
                    disabled: api.isCampusDisabled() || isDeadEnd,
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
                    disabled: isDeadEnd,
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
            api.showAddButton && el("button.add", {
                onclick: api.insertRow,
                disabled: isDeadEnd,
            }, "add"),
            api.showType && el("div", [
                createRadio("serial"),
                el("span", " "),
                createRadio("parallel"),
            ]),
        ]);
        return el("div.route-create", [
            el("div", [
                el("small", "(current office: "+api.currentOffice.complete_name+")"),
            ]),
            table,
            panel,
        ]);
    }
}
