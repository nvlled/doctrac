UI = UI || {};

UI.OfficeSelection = function(sel, args) {
    this.$node = $(sel);
    this.$officeSel = this.$node.find("select.offices");
    this.$campusSel = this.$node.find("select.campuses");

    this.$btnAdd = this.$node.find("button.add");
    this.$input = this.$node.find("input.officeId");
    this.$table = this.$node.find("table");
    var args = args || {};
    this.officeId = args.officeId;
    this.campusId = args.campusId;

    if (args.hideTable) {
        this.$table.hide();
        this.$btnAdd.hide();
    }
    if ( ! args.gateway) {
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
                UI.setSelectedValue(this.$officeSel, office.id);
            }.bind(this));
    },

    clear: function() {
        this.clearValue();
        this.clearSelections();
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

    loadOffices: function(offices) {
        if (!offices)
            return;
        offices.forEach(function(office) {
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
        var offices = this.getSelectedOffices();
        var office = offices[offices.length-1];
        var officeId = office ? office.id : this.officeId;

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

    checkDestinations: function() {
        var offices = this.getSelectedOffices();
        var office = offices[offices.length-1];

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

    },

    setCampusId: function(id) {
        UI.setSelectedValue(this.$campusSel, id);
    },

}
