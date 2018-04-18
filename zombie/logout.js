
const process = require("process");

// a logout script
module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    await page.goto("http://doctrac.local/settings");
    let btnLogout = await page.$("button.logout");
    await btnLogout.click();
}
