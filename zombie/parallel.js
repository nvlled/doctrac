
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
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);

    if ( ! process.env.nologin) {
        await logout(page);
        await login(page, "urd-records", "x");
    }


    let trackingId = await dispatchParallel(page, [
        "registrar", "accounting", "mis",
    ]);

    await logout(page);
    await login(page, "urd-mis");
    await receive(page, trackingId);

    await logout(page);
    await login(page, "urd-registrar");
    await receive(page, trackingId);
    page.goto(`http://doctrac.local/${trackingId}/routes`);

    //actionLink = await page.$("a.action");
    //await navigation(page, _=> actionLink.click());
    //recvBtn = await page.$("button.recv.finalize");
    //await recvBtn.click();
}
