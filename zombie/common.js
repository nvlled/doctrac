
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

    async dispatch(page, nroutes=3) {
        await page.goto("http://doctrac.local/dispatch");
        await common.dispatchInput(page);

        let btnAdd = await page.$("button.add");
        for (let i = 0; i < nroutes; i++)
            await clickElem(btnAdd);

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

    async dispatchParallel(page, offices) {
        await page.goto("http://doctrac.local/dispatch");
        await common.dispatchInput(page);

        let btnAdd = await page.$("button.add");

        await page.click("input[value=parallel]");
        for (let off of offices) {
            await common.selectMenu(page, "select.offices", off);
            await clickElem(btnAdd);
        }

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
        await navigation(page, _=> page.goto(`http://doctrac.local/${trackingId}/routes`));
        let actionLink = await page.$("a.action");
        if (!actionLink) {
            console.log("no action available for", trackingId);
            return;
        }

        await navigation(page, _=> clickElem(actionLink, page));
        let recvBtn = await page.$("button.recv.action");

        if (recvBtn)
            await navigation(page, _=> clickElem(recvBtn));
        else
            console.log("action not available: send");
    },

    async send(page, trackingId, office="records") {
        await navigation(page, _=> page.goto(`http://doctrac.local/${trackingId}/routes`));
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

        await common.selectMenu(page, "select.offices",office);
        let sendBtn = await page.$("button.send.action");
        if (sendBtn)
            await navigation(page, _=> clickElem(sendBtn));
        else
            console.log("action not available: send");
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
