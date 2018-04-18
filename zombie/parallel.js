
const process = require("process");
const {
    navigation,
    dispatch,
    dispatchInput,
    logout,
    login,
    sleep,
    selectMenu,
    receive,
    send,
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);

    if ( ! process.env.nologin) {
        await logout(page);
        await login(page, "urd-records", "x");
    }

    await page.goto("http://doctrac.local/dispatch");
    let trackingId = await dispatchInput(page);
    await page.click("input[value=parallel]");
    await selectMenu(page, "select.offices", "registrar");
    await page.click("button.add");
    await selectMenu(page, "select.offices", "accounting");
    await page.click("button.add");
    await selectMenu(page, "select.offices", "mis");
    await page.click("button.add");

    //actionLink = await page.$("a.action");
    //await navigation(page, _=> actionLink.click());
    //recvBtn = await page.$("button.recv.finalize");
    //await recvBtn.click();
}
