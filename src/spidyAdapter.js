"use strict";

var spidy = require('spidy');
var args = process.argv.slice(2);


// PARSE THE INPUT /////////////////////
//
//
if (args.length == 0 ) {
    console.error('A json representation of the request is required.');
    phantom.exit(1);
}

try {
    var inputData = JSON.parse(args[0]);
} catch (e) {
    console.error("unable to parse json string: ");
    console.error(e);
    process.exit(1);
}

if (!inputData) {
    console.error('Invalid input data. A valid json is required');
    process.exit(1);
}

if (!inputData.url) {
    console.error('No url was specified');
    process.exit(1);
}

var url = inputData.url;
var method = inputData.method || "GET";
var headers = inputData.headers || {};
var data = inputData.data || null;
var proxy = inputData.proxy || null;
//
//
// PARSE THE INPUT /////////////////////




// CONFIGURE THE PAGE //////////////////
//
//

var settings = {
    method: method,
    headers: headers,
    body: data,
    proxy: proxy
};

// TODO: viewport size (jsdom needs a patch)
// viewportsize = inputData.viewportsize || {width: 1680, height: 1050};

if (headers['User-Agent']) {
    settings.userAgent = headers['User-Agent'];
}


if (!headers['Accept']) {
    headers['Accept'] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8"
}
//
//
// CONFIGURE THE PAGE //////////////////




spidy.request(url, settings, function (err, window, response) {
    if (err) {
        console.error('Error: could not fetch the page for the url: "' + url + '". Reason: ' + err);
        process.exit(1);
    } else {
        // TODO: patch spidy to allow access to request data
        //var headers = {};
        //for (var i=0; i<pageHeaders.length; i++) {
        //    headers[pageHeaders[i].name] = pageHeaders[i].value;
        //}


        var contentType = response.headers['content-type'] || response.headers['Content-Type'];
        var content;
        if (contentType && contentType == 'application/json') {
            content = window.document.body.textContent;
        } else {
            content = window.document.documentElement.innerHTML;
        }

        var data = {
            url     : response.url,
            content : content,
            status  : response.statusCode,
            headers : response.headers
        };
        console.log(JSON.stringify(data));
    }
});


