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
}= require("./common");

module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    page.setDefaultNavigationTimeout(15500);


    console.log("dispatching from records");
    await logout(page);
    await login(page, "urd-records", "x");

    let trackingId = await dispatch(page);

    console.log("receiving from mis");
    await logout(page);
    await login(page, "urd-mis", "x");

    await receive(page, trackingId);

    //actionLink = await page.$("a.action");
    //await navigation(page, _=> actionLink.click());
    //recvBtn = await page.$("button.recv.finalize");
    //await recvBtn.click();
}
