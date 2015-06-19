
if (!window.console || !console.firebug){
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    window.console = {
            "_tttt_":1//dummy, para que el sistema de templates no rompa el codigo
    };

    for (var i = 0; i < names.length; ++i){
        window.console[names[i]] = function() {
            var t=1;//dummy, para que el sistema de templates no rompa el codigo
        }
    }
}
