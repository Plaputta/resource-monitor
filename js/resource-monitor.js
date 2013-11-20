/**
 * If the browser doesn't support window.performance.getEntries we're out.
 *
 * It also relies on that a browser that supports this method also has a native JSON implementation.
 *
 * It extracts the params from the script's URI (see index.html) and reports all resources that
 * took longer than minDuration to reportHost.
 */
(function (params) {
    if (!window.performance || !window.performance.getEntries) return;
    window.addEventListener('load', function () {
        window.setTimeout(function () {
            var entries = window.performance.getEntries();
            var report = [];
            for (var i = 0; i < entries.length; i++) {
                if (entries[i].duration < params.minDuration) continue;
                report.push({'resource':entries[i].name.split('?',2)[0],'duration':Math.round(entries[i].duration)});
            }
            if (report.length) {
                xmlhttp = new XMLHttpRequest();
                xmlhttp.open("POST",params.reportHost,true);
                xmlhttp.setRequestHeader("Content-Type","application/json");
                xmlhttp.send(JSON.stringify(report));
            }
        }, 0);
    }, false);
})((function(q) {
   if (!q) return {};
   var p = q.split(/[;&]/), r = {};
   for (var i = 0, l = p.length; i < l; i++) {
      var kv = p[i].split('=',2);
      if (!kv) continue;
      r[decodeURIComponent(kv[0])] = (kv.length == 1 ? null : decodeURIComponent(kv[1]).replace(/\+/g, ' '));
   }
   return r;
})(Array.prototype.slice.call(document.getElementsByTagName('script')).pop().src.replace(/^[^\?]+\??/,'')));