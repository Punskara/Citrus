document.addEventListener("DOMContentLoaded", function(event) {
    var show_link = document.getElementById('showDebugBar');
    var debug_bar = document.getElementById('CitrusDebugBar');

    var buttons = debug_bar.getElementsByTagName('a');
    var panels = debug_bar.getElementsByClassName('citrusDebugPane');
    if (buttons.length > 0) for (var i in buttons) {
        var b = buttons[i];
        b.onclick = function(e) {
            e.preventDefault();
            for (var j in panels) {
                var p = panels.item(j);
                cosRemoveClass(p, 'active');
            }
            switch (this.hash) {
                case '#request':
                    var elt = document.getElementById('citrusDebugQString');
                    if (cosHasClass(elt, "active")) cosRemoveClass(elt, 'active');
                    else cosAddClass(elt, 'active');
                    break;
                case '#sql':
                    var elt = document.getElementById('citrusDebugSQLQueries');
                    if (cosHasClass(elt, "active")) cosRemoveClass(elt, 'active');
                    else cosAddClass(elt, 'active');
                    break;
                case '#timer':
                    var elt = document.getElementById('citrusDebugTimer');
                    if (cosHasClass(elt, "active")) cosRemoveClass(elt, 'active');
                    else cosAddClass(elt, 'active');
                    break;
                case '#close':
                    cosAddClass(debug_bar, "hidden");
                    break;
                default: break;
            }
        }
    }
    show_link.onclick = function(e) {
        e.preventDefault();
        var content = debug_bar.getElementsByClassName('content')[0];
        var display;
        if (content.currentStyle != undefined) {
            display = content.currentStyle.display;
        } else {
            display = window.getComputedStyle(content).getPropertyValue('display');
        }
        if (display == "none") {
            cosAddClass(content, "shown");
            cosAddClass(debug_bar, "active");
        } else {
            cosRemoveClass(content, "shown");
            cosRemoveClass(debug_bar, "active");
            for (var j in panels) {
                var p = panels.item(j);
                cosRemoveClass(p, 'active');
            }
        }
    };
});

function cosHasClass( element, token ) {
    var regexp = new RegExp("(^|\\s)" + token + "(\\s|$)");
    return regexp.test(element.className || "");
}
function cosAddClass( element, token ) {
    if (!cosHasClass(element, token)) {
        element.className += (element.className ? " " : "") + token;
    }
}
function cosRemoveClass( element, token ) {
    var regexp = new RegExp("(^|\\s)" + token + "(\\s|$)", "g");
    var cls = element.className.replace(regexp, "$2");
    if (cls.substr(0, 1) == " ") cls = cls.substr(1);
    if (cls.substr(cls.length - 1, 1) == " ") cls = cls.substr(0, cls.length - 1);
        element.className = cls;
 };
