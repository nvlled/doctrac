
const navigation = async (page, fn) => {
    return Promise.all([
        fn(),
        page.waitForNavigation({ waitUntil: 'networkidle2', })
    ]);
}

const isVisible = (page, elem) => page.evaluate(e => {
    if (typeof e == "string")
        e = document.querySelector(e);

    if (!e)
        return false;
    const style = window.getComputedStyle(e);
    return style && style.display !== 'none'
        && style.visibility !== 'hidden'
        && style.opacity !== '0';
}, elem);

// strange, zooming in or out causes it to fail
// i should probably file a bug report

const clickElem = async elem => {
    if (!elem) {
        console.warn("cannot click null elem");
        return null;
    }
    if (typeof elem.click != "function") {
        console.log(elem);
        console.warn("elem is not clickable");
        console.trace();
        return null;
    }
    try {
        return await elem.click();
    } catch(e) {
        console.warn(e.message);
    }
    return null;
}

let common = {
    navigation,
    clickElem,
    sleep(ms) {
        return new Promise(resolve => {
            setTimeout(resolve, ms);
        });
    },

    async logout(page) {
        await page.goto("http://doctrac.local/settings");
        let btnLogout = await page.$("button.logout");
        if (btnLogout) {
            await navigation(page, _=> clickElem(btnLogout));
        }
    },

    async login(page, username="urd-records", password="x") {
        await page.goto("http://doctrac.local/login");
        let inputUsername = await page.$("input#name");
        let inputPassword = await page.$("input#password");
        let btnSubmit = await page.$("button");
        await inputUsername.type(username);
        await inputPassword.type(password);
        await navigation(page, _=> clickElem(btnSubmit));
    },

    async setUser(page, username, password) {
        if (username == await common.currentUsername(page))
            return;
        await common.logout(page);
        await common.login(page, username, password);
    },

    async currentUsername(page) {
        return await page.evaluate(() => {
            let input = document.body.querySelector("input#current-user");
            if (!input)
                return "";
            try {
                let user = JSON.parse(input.value);
                return user.username;
            } catch (e) { };
            return "";
        });
    },

    async dispatchInput(page) {
        let title = await page.$("input.title");
        let details = await page.$("textarea.details");
        let annotations = await page.$("textarea.annotations");
        if (title)
            await title.type(Math.random().toString(36).slice(2));
        if (details)
            await details.type(Math.random().toString(36).slice(2));
        if (annotations)
            await annotations.type(Math.random().toString(36).slice(2));
    },

    async selectOffices(page, type, data) {
        type = type || "serial"
        if (!data) {
            data = [
                ["urdaneta",  ["mis", "registrar"]],
            ]
        }
        let btnAdd = await page.$("button.add");
        let typeRadio = await page.$(`input[value=${type}]`);
        if (typeRadio)
            await typeRadio.click();

        if (typeof data == "string") {
            await common.selectMenu(page, "select.offices", data);
            if (btnAdd)
                await clickElem(btnAdd);
        } else {
            for (let [campus, offices] of data) {
                if (campus)
                    await common.selectMenu(page, "select.campuses", campus);
                await common.sleep(150);
                for (let office of offices) {
                    await common.selectMenu(page, "select.offices", office);
                    if (btnAdd)
                        await clickElem(btnAdd);
                }
            }
        }
    },

    async dispatch(page, data) {
        await navigation(page, _=> page.goto("http://doctrac.local/dispatch"));
        await common.dispatchInput(page);

        await common.selectOffices(page, "serial", data);
        let btnSend = await page.$("button.send");
        await navigation(page, _=> clickElem(btnSend));

        let trackingId = await page.evaluate(
            () => {
                let elem = document.body.querySelector(".trackingId");
                return elem ? elem.textContent : "";
            }
        );
        trackingId = trackingId.trim();
        console.log("trackingId", trackingId, page.url());
        return trackingId;
    },

    async dispatchParallel(page, data) {
        await page.goto("http://doctrac.local/dispatch");
        await common.dispatchInput(page);

        let btnAdd = await page.$("button.add");

        await common.selectOffices(page, "parallel", data);

        let btnSend = await page.$("button.send");
        await navigation(page, _=> clickElem(btnSend));

        let trackingId = await page.evaluate(
            () => {
                let elem = document.body.querySelector(".trackingId");
                return elem ? elem.textContent : "";
            }
        );
        trackingId = trackingId.trim();
        console.log("trackingId", trackingId, page.url());
        return trackingId;
    },

    async receive(page, trackingId) {
        await common.performAction(page, trackingId, "recv");
    },

    async send(page, trackingId, data="records") {
        if (trackingId) {
            await navigation(page, _=>
                page.goto(`http://doctrac.local/${trackingId}/routes`));
        }

        let actionLink = await page.$("a.action");
        if (!actionLink) {
            console.log("no action available for", trackingId);
            return;
        }
        await navigation(page, _=> clickElem(actionLink));

        let annotations = await page.$("textarea[name=annotation]");
        if (annotations) {
            await annotations.type(Math.random().toString(36).slice(2));
        } else {
            console.warn("no annotations found");
        }

        await common.selectOffices(page, "parallel", data);

        let sendBtn = await page.$("button.send.action");
        if (sendBtn)
            await navigation(page, _=> clickElem(sendBtn));
        else
            console.log("action not available: send");
    },

    async finalize(page, trackingId) {
        await common.performAction(page, trackingId, "finalize");
    },

    async reject(page, trackingId) {
        await common.performAction(page, trackingId, "reject");
    },

    async performAction(page, trackingId, action) {
        await navigation(page, _=>
            page.goto(`http://doctrac.local/${trackingId}/routes`));

        let actionLink = await page.$("a.action");
        if (!actionLink) {
            console.log("no action available for", trackingId, ":", action);
            return;
        }
        await navigation(page, _=> clickElem(actionLink));

        let annotations = await page.$("textarea[name=annotation]");
        if (annotations) {
            await annotations.type(Math.random().toString(36).slice(2));
        } else {
            console.warn("no annotations found");
        }

        let actionBtn = await page.$(`button.${action}.action`);
        if (actionBtn)
            await navigation(page, _=> clickElem(actionBtn));
        else
            console.log(`action not available: ${action}`);
    },

    // TODO: move to zombieteer code
    async selectMenu(page, selector, text) {
        let selValue = await page.evaluate((selector, text) => {
            let select = document.body.querySelector(selector);
            let options = select.options
            let value = null;
            for (let i = 0; i < options.length; i++) {
                var text_ = options[i].textContent.toLowerCase().trim();
                if (text_ == text) {
                    return options[i].value;
                }
            }
            return value
        }, selector, text);
        if (selValue) {
            await page.select(selector, selValue);
        }
    },
}

module.exports = common;
