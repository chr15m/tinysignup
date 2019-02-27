// https://news.ycombinator.com/hn.js
function nu(tag, attrs, text) { var e = document.createElement(tag); for(var a in attrs) { e.setAttribute(a, attrs[a]); }; e.innerHTML = text || ""; return e; }
function chkurl(s) { return document.location.href.indexOf(s) != -1; }

var script = document.currentScript;
var href = script.src.split("?");

var div = nu("div", {"className": "tinysignup"});

if (chkurl("&v=") || chkurl("&unsubscribe=")) {
  div.innerHTML = "";
  div.appendChild(nu("div", {"className": "spinner"}));
  post(href[0], document.location.search.substring(1), function(response) {
    div.innerHTML = response;
  });
} else {
  var form = nu("form", {"method": "post", "action": href[0]});
  form.appendChild(nu("input", {"type": "hidden", "name": "list", "value": href[1].replace("list=", "")}));
  form.appendChild(nu("input", {"type": "email", "placeholder": "Email address...", "name": "email"}));
  form.appendChild(nu("button", {"type": "submit"}, "✔"));
  form.onsubmit = function(ev) {
    div.innerHTML = "";
    div.appendChild(nu("div", {"className": "spinner"}));
    submitForm(ev, form, function(response) {
      div.innerHTML = response;
    });
  };
  div.appendChild(form);
}

script.parentNode.insertBefore(div, script.nextSibling);

function submitForm(ev, form, callback) {
  ev.preventDefault();
  var data = "list=" + encodeURIComponent(form.list.value) + "&email="  + encodeURIComponent(form.email.value);
  url = form.action;
  post(url, data, callback);
}

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
