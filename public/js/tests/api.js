

var tests = {
    userList: function(log) {
        api.user.fetch().then(function(resp) {
            //log(resp);
            //Object.keys(resp).forEach(function(k) {
            //    //resp[k]
            //});
        });
    },
    userLogin: async function(log) {
        var userId = 1;
        var resp = await api.user.setSelf({ userId: userId, });
        var respId = resp && resp.id;
        console.assert(respId == userId, "user set self failed", userId, respId);
    },
    documentSend: function(log) {
        api.doc.send({
            title: "aaaa",
            officeIds
        });
    },
}
Object.keys(tests).forEach(function(name) {
    var fn = tests[name];
    fn.name = name;
});


function runTests(...tests) {
    for (let fn of tests) {
        fn(function(...args) {
            console.log(fn.name + ": ", ...args);
        });
    }
}

async function run() {
    var user;
    try {
        user = await api.user.self();
    } catch(e) { }

    if (!user) {
        try {
            user = await api.dev.createUser();
        } catch (e) { }
    }

    try {
        await testDataSetup.all({
            showWarning: true,
        });
    } catch (e) {
        console.log(e);
    }
    await testDataInit.all({
        showWarning: true,
    });

    // !!!!!!1
    //api.user.setSelf({
    //    userId: users.
    //});
}

run();
