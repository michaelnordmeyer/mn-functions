// https://boost.re/javascripts/read.js
var getLocation = function(href) {
    var l = document.createElement("a");
    l.href = href;
    return l;
};
var reported = false;
var path = getLocation(window.location.href).pathname;
var startTime = localStorage.getItem(path) ? parseInt(localStorage.getItem(path)) : 0;

function readerTracker(readTime) {
    var timeInterval = 2000;
    var body = document.body;
    var html = document.documentElement;
    var y = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
    var whereToLook = document.getElementsByTagName('body')[0] ? 'body' : 'html';
    var textBrut = document.getElementsByTagName(whereToLook)[0].innerHTML ? document.getElementsByTagName(whereToLook)[0].innerHTML.replace(/<!--[\s\S]*?-->/g,'').replace(/\<(script|noscript|style|form|header|footer|nav)[\s\S]*?\>.*?\<\/(script|noscript|style|form|header|footer|nav)\>/g,'').replace(/\<.*?\>/g,'').replace(/\&nbsp\;/g,' ').replace(/\s+/g,' ') : '';
    var readingTime = textBrut.split(' ').length / 330;
    var pathName = getLocation(window.location.href).pathName + '?read=true';
    var w = window;
    var d = document;
    var e = d.documentElement;
    var g = d.getElementsByTagName('body')[0];
    var x = w.innerWidth || e.clientWidth || g.clientWidth;
    var z = w.innerHeight || e.clientHeight || g.clientHeight;
    var intervalReading;

    window.onblur = function() {
        if (intervalReading) {
            clearInterval(intervalReading);
            intervalReading = null;
        }
    }

    window.onfocus = function() { readerTracker(readTime); }

    window.onbeforeunload = function() {
        try {
            localStorage.setItem(path, readTime);
        } catch(ev) {
            console.log("Storage failed: " + ev);
        }
    }

    if (!reported) {
        intervalReading = setInterval(function() {
            readTime = parseInt(readTime) + parseInt(timeInterval);
            var scrollTop = document.body.scrollTop || document.documentElement.scrollTop || window.scrollY;
            var querySelectorVar = document.querySelector('[id^=discussion],[id=shareBottom],#comments,#commentarea,[name=comments],[class^=comment_form],[class^=entry-comments],.FBComments,fb\\:comments,div[id^=coment],#forum,[id^=disqus_thread],#related-content,#RelatedPostsContainer,.post-latest,#notas-rel,div[class=post-footer],div[class=morearticles],footer')

            if (querySelectorVar) {
                var fullArticleHeight = parseInt(querySelectorVar.offsetTop) - parseInt(z);
            } else {
                var fullArticleHeight = parseInt(y);
            }

            if (readTime / 60000 >= parseInt(readingTime) * 0.75 && scrollTop >= fullArticleHeight * 0.85) {
                if ("ga" in window) {
                    ga('send', 'pageview', pathName);
                    ga('send', 'event', 'read', 'read', pathName);
                } else if ("_gaq" in window) {
                    _gaq.push(['_trackPageview', pathName]);
                    _gaq.push(['_trackEvent', 'read', 'read', pathName])
                }

                clearInterval(intervalReading);
                intervalReading = null;
                reported = true;

                try {
                    localStorage.setItem(path, 0);
                } catch (ev) {
                    console.log("Storage failed: " + ev);
                }
            }
        }, timeInterval);
    }
}

function startTracking() {
    if (document.hasFocus()) {
        readerTracker(startTime);
    } else {
        window.onfocus = function() { readerTracker(startTime); }
    }
}

window.addEventListener("load", function(event) { startTracking(); } );
