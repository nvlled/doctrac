
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
        noSelect: args.noSelect,
        vm: null,
        graph: graph,
        rows: rows,
        type: args.type || "serial",
        officeIndex: args.officeIndex || 0,
        campusIndex: args.campusIndex || 0,
        message: "",
        routes: [],
        onChangeOffice: args.onChangeOffice || function() {},
        officeLink: args.officeLink || null,

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
            e.preventDefault();
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
            if (name == type)
                return;
            if (self.rows.length > 0 && self.type != name) {
                var proceed = confirm(
                    "changing the type will clear all the rows, continue?"
                );
                if (!proceed) {
                    self.type = type;
                    self.vm.redraw();
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
            //var currentOffice = self.currentOffice;
            //if (currentOffice && self.getType() == "parallel") {
            //    return !currentOffice.main;
            //}
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
            if (typeof self.onChangeOffice == "function") {
                var off = self.getOffices()[self.officeIndex];
                self.onChangeOffice(off);
            }
        },

        getOffices: function() {
            var lastOffice = self.getLastOffice();
            var currentOffice = self.currentOffice;
            var campus = self.getSelectedCampus();

            var offices = [];
            if (campus) {
                offices = self.graph.getLocalOffices(campus.id);
                if ((lastOffice && lastOffice.campusId != campus.id) ||
                    currentOffice.campusId != campus.id) {
                    offices = offices.filter(function(off) {
                        return off.gateway;
                    });
                }
            }

            if (self.officeLink && !self.currentOffice.gateway) {
                var link = self.officeLink;
                offices = offices.filter(function(off) {
                    return off.gateway
                        || off.id == link.prevId
                        || off.id == link.nextId;
                });
            }

            return offices;
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
        return el("div.form-check.form-check-inline", [
            el("input.form-check-input#radio-"+name,
                {
                    name: "type",
                    value: name,
                    type: "radio",
                    onclick: [api.changeType, name, api.type],
                    checked: api.getType() == name,
                }),
            el("label.form-check-label", {"for": "radio-"+name}, name),
        ]);
        //return el("label", [
        //    el("input.form-control",
        //        {
        //            name: "type",
        //            value: name,
        //            type: "radio",
        //            onclick: [api.changeType, name, api.type],
        //            checked: api.getType() == name,
        //        }),
        //    el("span", " "),
        //    name,
        //]);
    }

    function renderTable() {
        var rows = api.getRows();
        return el((api.type == "serial" ? "ol" : "ul") + ".list.list-group",
            rows.map(function(row, index) {
                var campus = api.getCampus(row) || {};
                var showX = api.type == "parallel" ||
                    (api.type == "serial" && index == rows.length-1);
                return el("li.list-group-item", {_key: "off-"+row.id+"-"+(+new Date())}, [
                    el("span.m-1", campus.name),
                    el("span.m-1", row.name),
                    el("span.m-1", [
                        showX && el(
                            "button.close w-1",
                            {
                                type: "button",
                                "aria-label": "Close",
                                onclick: [api.removeRow, row],
                            },
                            [el("span", {"aria-hidden": "true"}, "✗")],
                        ) ,
                        //<button type="button" class="close" aria-label="Close">
                        //  <span aria-hidden="true">&times;</span>
                        //</button>
                    ]),
                ]);
            })
        )
        //return el("table.route-create", [
        //    el("thead", [
        //        el("tr", [
        //            el("th", "campus"),
        //            el("th", "office"),
        //            el("th", ""),
        //        ]),
        //    ]),
        //    el("tbody", {_key: new Date()},
        //        rows.map(function(row, index) {
        //            var campus = api.getCampus(row) || {};
        //            var showX = api.type == "parallel" ||
        //                (api.type == "serial" && index == rows.length-1);
        //            return el("tr", {_key: "off-"+row.id+"-"+(+new Date())}, [
        //                el("td", campus.name),
        //                el("td", row.name),
        //                el("td", [
        //                    showX && el(
        //                        "a",
        //                        {onclick: [api.removeRow, row]},
        //                        "✗",
        //                    ) ,
        //                ]),
        //            ]);
        //        })
        //    ),
        //])
    }

    return function() {
        var table = api.showTable ? renderTable() : null;
        var disabledCampuses = api.getDisabledCampuses();
        var disabledOffices = api.getDisabledOffices();
        var isDeadEnd       = api.isDeadEnd();

        var panel = el("div.panel", [
        ]);
        return el("div.route-create.row", [
            el("div.col-12", [
                table,
            ]),
            el("div.messages.col-10.text-center", [el("em", api.message)]),
            el("div.m-3.col-10", [
                el("div.sel.row", [
                    el(
                        "select.offset-0.col-4.form-control.campuses[name=campuses]",
                        {
                            _ref: "campuses",
                            disabled: api.noSelect || api.isCampusDisabled() || isDeadEnd,
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
                        "select.col-4.form-control.offices[name=offices]",
                        {
                            _ref: "offices",
                            disabled: api.noSelect || isDeadEnd,
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
                    el("div.button.col.2", [
                        api.showAddButton && el("button.add.btn.btn-default", {
                            onclick: api.insertRow,
                            disabled: isDeadEnd,
                        }, "Add"),
                    ]),
                ]),
            ]),
            el("div.radio.offset-1.col-10", [
                api.showType && el("div", [
                    createRadio("serial"),
                    el("span", " "),
                    createRadio("parallel"),
                ]),
            ]),
        ]);
    }
}
