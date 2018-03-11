UI = UI || {};

UI.OfficeSelection = function(sel, args) {
    this.$node = $(sel);
    this.$officeSel = this.$node.find("select.offices");
    this.$campusSel = this.$node.find("select.campuses");

    this.$btnAdd = this.$node.find("button.add");
    this.$input = this.$node.find("input.officeId");
    this.$table = this.$node.find("table");
    var args = args || {};
    this.type = args.type || "serial";
    this.gateway = args.gateway;
    this.officeId = args.officeId;
    this.campusId = args.campusId;

    if (args.hideTable) {
        this.$table.hide();
        this.$btnAdd.hide();
    }
    if ( ! this.gateway) {
        this.$campusSel.attr("disabled", true);
    }

    var self = this;
    this.$campusSel.change(function(e) {
        self.fetchOffices();
    });

    this.$btnAdd.click(this.loadData.bind(this));
    combobox.load(this.$campusSel)
        .then(function() {
            UI.setSelectedValue(this.$campusSel, this.campusId);
            this.$campusSel.change();
        }.bind(this));
}

UI.OfficeSelection.prototype = {
    disable: function() {
        this.$campusSel.attr("disabled", false);
        this.$officeSel.attr("disabled", false);
    },

    getOfficeId: function() {
        var off = this.getOffice();
        if (off)
            return off.id;
        return null;
    },

    getOffice: function() {
        return combobox.getOffice(this.$officeSel);
    },

    setOffice: function(office) {
        if (!office)
            return;
        UI.setSelectedValue(this.$campusSel, office.campusId);
        this.fetchOffices()
            .then(function() {
                UI.setSelectedValue(this.$officeSel, office.officeId);
            }.bind(this));
    },

    clear: function() {
        this.clearValue();
        this.clearSelections();
        this.checkDestinations();
        this.updateOfficeSelection();
    },

    value: function() {
        return this.$input.val();
    },

    clearValue: function() {
        this.$input[0].clear();
    },

    getSelectedIds: function() {
        var officeIds = [];
        this.$node.find("tbody tr").each(function(i) {
            var id = $(this).data("officeId");
            officeIds.push(id);
        });
        return officeIds;
    },

    getSelectedOffices: function() {
        var offices = [];
        this.$table.find("tbody tr").each(function(i) {
            var office = $(this).data("object");
            if (office)
                offices.push(office);
        });
        return offices;
    },

    clearSelections: function() {
        this.$table.find("tbody").html("");
    },

    getOffice: function() {
        return combobox.getObject(this.$officeSel);
    },

    showError: function(msg) {
        this.$node.find(".add-error").text(msg);
    },

    clearError: function(msg) {
        this.$node.find(".add-error").text("");
    },

    fetchOffices: function() {
        var self = this;
        var campusId = this.$campusSel.val();
        return api.campus.offices({campusId: campusId})
            .then(function(offices) {
                if (campusId != self.campusId) {
                    offices = offices.filter(function(off) {
                        return off.gateway;
                    });
                }
                combobox.loadData(self.$officeSel, offices);
                self.updateOfficeSelection();
            });
    },

    isAdded: function(officeId) {
        // TODO: use a set or map instead
        var ids = this.getSelectedIds();
        return ids.some(function(x) {
            return x == officeId;
        });
    },

    loadOffices: function(offices) {
        if (!offices)
            return;
        offices.forEach(function(office) {
            if (this.type == "parallel" && this.isAdded(office.id))
                return;
            var $tr = util.jq([
                "<tr>",
                //" <td class='id'></td>",
                " <td class='campus'></td>",
                " <td class='name'></td>",
                " <td class='action'>",
                "   <a href='#' class='del'>X</a>",
                "</td>",
                "</tr>",
            ]);

            $tr.data("object", office);
            $tr.data("officeId", office.id);
            //$tr.find(".id").text(office.id);
            $tr.find(".campus").text(office.campus_name);
            $tr.find(".name").text(office.name);
            $tr.find(".del").click(function(e) {
                e.preventDefault();
                $tr.remove();
                this.checkDestinations();
                this.fetchOffices();
            }.bind(this));

            this.$table.append($tr);
            this.checkDestinations();
            this.updateOfficeSelection();

        }.bind(this));
    },

    updateOfficeSelection: function() {
        var officeId = null;
        if (this.type == "serial") {
            var offices = this.getSelectedOffices();
            var office = offices[offices.length-1];
            officeId = office ? office.id : this.officeId;
        } else {
            officeId = this.officeId;
        }

        var $officeSel = this.$officeSel;
        $officeSel.find("option")
            .each(function() {
                var $option = $(this);
                if ($option.val() == officeId) {
                    UI.selectOther($officeSel, officeId);
                    $option.attr("disabled", true);
                } else {
                    $option.attr("disabled", false);
                }
            });
    },

    checkDestinations: function() {
        var offices = this.getSelectedOffices();
        var office = offices[offices.length-1];

        this.$campusSel.attr("disabled", false);
        this.$officeSel.attr("disabled", false);

        if ( ! this.gateway) {
            this.setCampusId(this.campusId);
            this.$campusSel.attr("disabled", true);
        }

        if (this.type == "serial") {
            if (office && office.gateway && this.campusId != office.campusId) {
                this.$input.attr("disabled", true);
                this.$btnAdd.attr("disabled", true);
                this.$officeSel.attr("disabled", true);
                this.$campusSel.attr("disabled", true);
                this.setCampusId(this.campusId);
            } else {
                this.$input.attr("disabled", false);
                this.$btnAdd.attr("disabled", false);
                this.$officeSel.attr("disabled", false);
                if (office && !office.gateway)
                    this.$campusSel.attr("disabled", true);
                else
                    this.$campusSel.attr("disabled", false);
            }
        }
    },

    loadData: function() {
        this.clearError();

        var office = this.getOffice();

        if (!office) {
            this.showError("office not found");
            return;
        }
        this.clearValue();
        this.loadOffices([office]);
        this.$input.blur();
    },


    setCampusId: function(id) {
        UI.setSelectedValue(this.$campusSel, id);
    },

}
