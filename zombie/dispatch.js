
const process = require("process");

// a login script
module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    await page.goto("http://doctrac.local/dispatch");
    let title = await page.$("input.title");
    let details = await page.$("textarea.details");
    let annotations = await page.$("textarea.annotations");
    await title.type("document title " +
        Math.random().toString(36));
    await details.type(Math.random().toString(36));
    await annotations.type(Math.random().toString(36));
}
