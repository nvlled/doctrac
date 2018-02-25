UI = UI || {};

UI.OfficeSelection = function(sel) {
    this.$node = $(sel);
    this.$input = this.$node.find("input");
    this.$table = this.$node.find("table");
    this.officeId = null;
    this.$input.on("complete", this.loadData.bind(this));
}

UI.OfficeSelection.prototype = {
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
        return this.$input.data("object");
    },

    showError: function(msg) {
        this.$node.find(".add-error").text(msg);
    },

    clearError: function(msg) {
        this.$node.find(".add-error").text("");
    },

    loadData: function() {
        this.$input.blur();
        this.clearError();

        var value = this.value();
        if (!value)
            return;

        var office = this.getOffice();

        if (!office) {
            this.showError("office not found");
            return;
        }
        this.clearValue();

        var $tr = util.jq([
            "<tr>",
            //" <td class='id'></td>",
            " <td class='name'></td>",
            " <td class='action'>",
            "   <a href='#' class='del'>X</a>",
            "</td>",
            "</tr>",
        ]);

        $tr.data("object", office);
        $tr.data("officeId", office.id);
        //$tr.find(".id").text(office.id);
        $tr.find(".name").text(office.campus_name + " " + office.name);
        $tr.find(".del").click(function(e) {
            e.preventDefault();
            $tr.remove();
            this.updateOfficeId();
            this.checkDestinations();
        }.bind(this));

        this.$table.append($tr);
        this.updateOfficeId(office.id);
        this.checkDestinations();
    },

    updateOfficeId: function(id) {
        if (id == null) {
            var office = this.$table.find("tbody tr").last().data("object");
            id = office ? office.id : this.officeId;
        }
        this.setOfficeId(id);
    },

    checkDestinations: function() {
        var offices = this.getSelectedOffices();
        var office = offices[offices.length-1];
        if (office && office.gateway && this.officeId != office.id) {
            this.$input.attr("disabled", true);
        } else {
            this.$input.attr("disabled", false);
        }
    },

    setOfficeId: function(id) {
        var data = this.getData();
        data.officeId = id;
        console.assert(id == this.getData().officeId, "data not set");
    },

    getData: function() {
        var data = this.$input.data("params");
        if (!data) {
            data = {};
            this.$input.data("params", data);
        }
        return data;
    },
}

