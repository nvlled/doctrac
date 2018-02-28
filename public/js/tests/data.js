
const USER_PASS = "x";

var testData = (function () {
    var campuses = {
        urd: {
            name: "urdaneta",
        },
        ala: {
            name: "alaminos",
        },
        lin: {
            name: "lingayen",
        },
    }

    var offices = {
        urdmis: {
            name: "MIS",
            campus: campuses.urd,
        },
        urdrec: {
            name: "Records",
            campus: campuses.urd,
            gateway: 1,
        },
        urdreg: {
            name: "Registrar",
            campus: campuses.urd,
        },
        alamis: {
            name: "MIS",
            campus: campuses.ala,
        },
        alarec: {
            name: "Records",
            campus: campuses.ala,
            gateway: 1,
        },
        alareg: {
            name: "Registrar",
            campus: campuses.ala,
        },
        linmis: {
            name: "MIS",
            campus: campuses.lin,
        },
        linrec: {
            name: "Records",
            campus: campuses.lin,
            gateway: 1,
        },
        linreg: {
            name: "Registrar",
            campus: campuses.lin,
        },
    }

    var users = {
        /* office accounts will be added here */
    };

    return {
        campuses,
        offices,
        users,
    }
})();

var testDataSetup = {
    all: async function() {
        // TODO: this.dropCampuses();
        // TODO: this.dropOffices();
        // TODO: this.dropUsers();
        await this.campuses();
        await this.offices();
        await this.users();
    },

    campuses: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, camp] of Object.entries(testData.campuses)) {
            camp.code = code;

            let resp = await api.campus.add(camp);
            if (!resp || resp.error) {
                if (showWarning)
                    console.warn("failed to add campus", camp);
                if (abortOnError)
                    return;
            } else {
                camp.id = resp.id;
            }
        }
    },

    offices: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, off] of Object.entries(testData.offices)) {
            off.campus_code = off.campus.code; 
            off.code = code;
            let resp = await api.office.add(off);
            if (!resp || resp.error) {
                if (showWarning)
                    console.warn("failed to add office", off);
                if (abortOnError)
                    return;
            } else {
                off.id = resp.id;
            }
        }
    },

    users: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, off] of Object.entries(testData.offices)) {
            var user = {
                username:    off.code,
                password:    USER_PASS,
                firstname:   off.name,
                lastname:    off.campus.name,
                positionId:  0,
                privilegeId: 0,
                officeId:    off.id,
            };

            let resp = await api.user.add(user);
            if (!resp || resp.error) {
                if (showWarning)
                    console.warn("failed to add user", user);
                if (abortOnError)
                    return;
            } else {
                user.id = resp.id;
                testData.users[user.username] = user;
            }
        }
    },
}

var testDataInit = {
    all: async function() {
        await this.campuses();
        await this.offices();
        await this.users();
    }, 

    campuses: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, camp] of Object.entries(testData.campuses)) {
            camp.code = code;
            let resp = await api.campus.get({code});
            if (resp && !resp.error) {
                camp.id = resp.id;
            }
        }
    },

    offices: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, off] of Object.entries(testData.offices)) {
            off.campus_code = off.campus.code; 
            off.campusId = off.campus.id;
            off.code = code;
            let resp = await api.office.get(off);
            if (resp && !resp.error) {
                off.id = resp.id;
            }
        }
    },

    users: async function(opts = {}) {
        let {
            abortOnError,
            showWarning=false,
        } = opts;

        for (let [code, off] of Object.entries(testData.offices)) {
            let resp = await api.user.get({id: off.code});
            if (resp && !resp.error) {
                testData.users[resp.username] = resp;
            }
        }
    },
}
