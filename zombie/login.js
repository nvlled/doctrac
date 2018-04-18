const process = require("process");
const {login, logout} = require("./common");

// a login script
module.exports = async function({browser, getPage}) {
    let page = await getPage(process.env.PWD);
    let username = process.env.username || "urd-records";
    let password = process.env.password || "x";
    await logout(page);
    await login(page, username, password);
}
