
const process = require("process");
const {
    navigation,
    dispatch,
    dispatchParallel,
    dispatchInput,
    logout,
    login,
    sleep,
    selectMenu,
    receive,
    send,
    clickElem,
    setUser,
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);

    await setUser(page, "main-records");

    let trackingId = "";
    trackingId = await dispatchParallel(page, [
        ["urdaneta", ["records"]],
        ["alaminos", ["records"]],
    ]);

    await setUser(page, "ala-records");
    await receive(page, trackingId);
    await send(page, trackingId, [
        ["alaminos", ["registrar", "accounting"]],
    ]);

    return;
    await setUser(page, "asi-records");
    await receive(page, trackingId);
}
