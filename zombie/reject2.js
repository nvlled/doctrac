const process = require("process");
const {
    navigation,
    dispatch,
    logout,
    login,
    sleep,
    selectMenu,
    receive,
    finalize,
    reject,
    send,
    setUser,
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);

    console.log("dispatching from records");
    await setUser(page, "urd-records");
    let trackingId = await dispatch(page, [
        ["alaminos", ["records"]],
    ]);

    await setUser(page, "ala-records");
    await receive(page, trackingId);
    await send(page, trackingId, "registrar");

    await setUser(page, "ala-registrar");
    await receive(page, trackingId);
    await reject(page, trackingId);

    await setUser(page, "ala-records");
    await receive(page, trackingId);
    await reject(page, trackingId);
}
