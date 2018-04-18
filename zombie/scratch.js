module.exports = async function({browser, currentPage, pageId}) {
    for (let page of await browser.pages()) {
        console.log("***", await page.title(), pageId(page));
    }

    let page = await currentPage();
    const aHandle = await page.evaluateHandle(() => document.body);

    let id = null;
    const resultHandle = await page.evaluateHandle(body => {
        body.innerHTML = "blah";
        let c = document.createElement("input");
        c.type = 'hidden';
        c.value = "aabb";
        body.appendChild(c);
    }, aHandle);
    console.log("id", id);
}
