/// <reference path="system" />
/// <reference path="form" />

/*
window.onerror = function(msg, file, line, col, error) {
 // callback is called with an Array[StackFrame]
 StackTrace.fromError(error).then(callback).catch(errback);
};
window.onerror = function () {
     report: function StackTrace$$report(stackframes, url) {
     return new Promise(function (resolve, reject) {
     var req = new XMLHttpRequest();
     req.onerror = reject;
     req.onreadystatechange = function onreadystatechange() {
     if (req.readyState === 4) {
     if (req.status >= 200 && req.status < 400) {
     resolve(req.responseText);
     } else {
     reject(new Error('POST to ' + url + ' failed with status: ' + req.status));
     }
     }
     };
     req.open('post', url);
     req.setRequestHeader('Content-Type', 'application/json');
     req.send(JSON.stringify({stack: stackframes}));
     });
     }
};
*/
