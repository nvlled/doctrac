// @flow

/*::
type Office = {
    id: number,
    campusId: number,
    name: string,
    complete_name: string,
    gateway: 1|0,
    campus_code: 1|0,
}

type Campus = {
    id: number,
    code: string,
    name: string,
}

type CtorData = {
    campuses: Array<Campus>,
    offices: Array<Office>,
}

type OfficeMap = {
    [number]: Office
}

type CampusMap = {
    [number]: Campus
}

type OfficeGraphT = {
    campuses:   Array<Campus>,
    offices:    Array<Office>,
    members:  { [number] : Array<number> },
    officeIds:  OfficeMap,
    campusIds:  CampusMap,
    recordsIds: OfficeMap,
}

*/

function OfficeGraph(data /*: CtorData */) {
    /* this: OfficeGraphT */
    var self /*: OfficeGraphT */ = this;
    self.campuses = data.campuses || [];
    self.offices = data.offices || [];
    self.members  = {};
    self.officeIds = {};
    self.campusIds = {};
    self.recordsIds = {};

    self.campuses.forEach(function(campus) {
        self.campusIds[campus.id] = campus;
    });

    self.offices.forEach(function(office) {
        self.officeIds[office.id] = office;
        if (office.gateway)
            self.recordsIds[office.id] = office;

        var members = self.members;
        var campusId = office.campusId;
        if (!members[campusId])
            members[campusId] = [];
        members[campusId].push(office.id);
    });
}

OfficeGraph.fetch = function() {
    return api.office.graph().then(function(data) {
        if (data)
            return new OfficeGraph(data);
        return null;
    });
}

OfficeGraph.prototype.getCampuses = function() /*: Array<Campus> */ {
    return this.campuses;
}

OfficeGraph.prototype.getLocalOffices = function(campusId) /*: Array<Office> */ {
    var members = this.members[campusId];
    if (!members)
        return [];
    return members.map(function(id) {
        return this.getOffice(id);
    }.bind(this));
}

OfficeGraph.prototype.getOffice = function(id) /*: Office */{
    return this.officeIds[id];
}

OfficeGraph.prototype.getCampus = function(id) /*: Campus */{
    return this.campusIds[id];
}

OfficeGraph.prototype.getCampuses = function() /*: Array<Campus> */{
    return this.campuses;
}
OfficeGraph.prototype.getOffices = function() /*: Array<Office> */{
    return this.offices;
}

OfficeGraph.prototype.getRecordsOffices = function() /*: Array<Office> */{
    var ids = this.recordsIds;
    return Object.keys(this.recordsIds).map(function(k) {
        return ids[k];
    });
}

OfficeGraph.prototype.getLocalRecordsOffice = function(officeId) /*: ?Office */{
    var office = this.getOffice(officeId);
    var records /*: Array<Office> */ = this.getRecordsOffices();
    for (var i = 0; i < records.length; i++) {
        if (office.campusId != records[i].campusId)
            continue;
        return records[i];
    }
    return null;
}

OfficeGraph.prototype.nextOffices = function(officeId) /*: Array<Office> */{
    var office = this.getOffice(officeId);
    if (!office)
        return [];

    var locals = this.getLocalOffices();
    var notSelf = function(off) { return off.id != officeId };

    if (!office.gateway) {
        return locals.filter(notSelf);
    }

    var records = this.getRecordsOffices();
    return locals
        .concat(records)
        .filter(notSelf);
}
