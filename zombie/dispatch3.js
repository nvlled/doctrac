const process = require("process");
const {
    navigation,
    dispatch,
    logout,
    login,
    sleep,
    selectMenu,
    receive,
    send,
    setUser,
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);


    console.log("dispatching from records");
    await setUser(page, "urd-records");
    let trackingId = await dispatch(page);

    console.log("receiving from mis");
    await setUser(page, "urd-mis");
    await receive(page, trackingId);
    await send(page, trackingId, "registrar");

    console.log("receiving from registrar");
    await setUser(page, "urd-registrar");
    await receive(page, trackingId);
    await send(page, trackingId, "urd-records");

    console.log("receiving from registrar");
    await setUser(page, "urd-records");
    await receive(page, trackingId);
    //await finalize(page, trackingId);

    //await logout(page);
    //await login(page, "urd-records", "x");
    //await receive(page, trackingId);

    //actionLink = await page.$("a.action");
    //await navigation(page, _=> actionLink.click());
    //recvBtn = await page.$("button.recv.finalize");
    //await recvBtn.click();
}
