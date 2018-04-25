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


    await logout(page);
    await login(page, "urd-records", "x");
    let ps = [];
    for(let i = 0; i < 20; i++) {
        console.log("dispatching from records", i);
        ps.push(await dispatch(page));
    }
    await Promise.all(ps);

    //await logout(page);
    //await login(page, "urd-records", "x");
    //await receive(page, trackingId);

    //actionLink = await page.$("a.action");
    //await navigation(page, _=> actionLink.click());
    //recvBtn = await page.$("button.recv.finalize");
    //await recvBtn.click();
}
