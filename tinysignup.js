// https://news.ycombinator.com/hn.js
function nu(tag, attrs, text) { var e = document.createElement(tag); for(var a in attrs) { e.setAttribute(a, attrs[a]); }; e.innerHTML = text || ""; return e; }
function chkurl(s) { return document.location.href.indexOf(s) != -1; }

var script = document.currentScript;
var href = script.src.split("?");
var q = parseQuery(href[1]);

function config(key, defaultvalue) {
  var defaultvalue = defaultvalue || null;
  return script.getAttribute("data-" + key) || defaultvalue;
}

if (config("div")) {
  var div = document.getElementById(config("div"));
} else {
  var div = nu("div", {"class": "tinysignup"});
}

if (chkurl("&v=") || chkurl("&unsubscribe=")) {
  div.innerHTML = "";
  div.appendChild(nu("div", {"class": "spinner top"}));
  post(href[0], document.location.search.substring(1), function(response) {
    div.innerHTML = "";
    div.appendChild(nu("div", {"class": "message top"}, response));
  });
  // put feedback at the top of the page
  document.body.insertAdjacentElement("afterbegin", div);
} else {
  if (config("form")) {
    var form = document.getElementById(config("form"));
    if (!form.getAttribute("action")) form.setAttribute("action", href[0]);
    if (!form.getAttribute("method")) form.setAttribute("method", "post");
  } else {
    var form = nu("form", {"method": "post", "action": href[0]});
    form.appendChild(nu("p", {}, q["message"] || config("message") || "Sign up to my mailing list:"));
    form.appendChild(nu("input", {"type": "hidden", "name": "list", "value": q["list"]}));
    form.appendChild(nu("input", {"type": "email", "placeholder": "Email address...", "name": "email"}));
    form.appendChild(nu("button", {"type": "submit"}, "✔"));
    div.appendChild(form);
  }
  form.onsubmit = function(ev) {
    if (form.email.value) {
      div.innerHTML = "";
      div.appendChild(nu("div", {"class": "spinner"}));
      submitForm(ev, form, function(response) {
        div.innerHTML = "";
        div.appendChild(nu("div", {"class": "message"}, response));
      });
    } else {
      ev.preventDefault();
    }
  };
  // put feedback where the script tag is
  if (!config("div")) {
    script.parentNode.insertBefore(div, script.nextSibling);
  }
}

function submitForm(ev, form, callback) {
  ev.preventDefault();
  var data = "list=" + encodeURIComponent(form.list.value) + "&email="  + encodeURIComponent(form.email.value);
  url = form.action;
  post(url, data, callback);
}

// https://gist.github.com/Xeoncross/7663273
function post(url, data, callback) {
  try {
    x = new(this.XMLHttpRequest || ActiveXObject)('MSXML2.XMLHTTP.3.0');
    x.open(data ? 'POST' : 'GET', url, 1);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    x.onreadystatechange = function () {
      x.readyState > 3 && callback && callback(x.responseText, x);
    };
    x.send(data);
    console.log("posted:", data, "to", url);
  } catch (e) {
    window.console && console.log(e);
  }
}

// https://stackoverflow.com/a/13419367/2131094
function parseQuery(queryString) {
    var query = {};
    var pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
    }
    return query;
}
