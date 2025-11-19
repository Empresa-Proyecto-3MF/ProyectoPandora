(function(){
    var global = typeof window !== 'undefined' ? window : this;
    var payload = global.APP_I18N || {};
    var locale = payload.locale || 'es';
    var messages = payload.messages || {};

    function format(str, params){
        if (!params) { return str; }
        return str.replace(/\{(\w+)\}/g, function(match, key){
            return Object.prototype.hasOwnProperty.call(params, key) ? params[key] : match;
        });
    }

    function translate(key, params, fallback){
        var value = messages.hasOwnProperty(key) ? messages[key] : undefined;
        if (value === undefined || value === null) {
            return fallback !== undefined ? fallback : key;
        }
        return params ? format(value, params) : value;
    }

    global.__ = translate;
    global.I18n = global.I18n || {};
    global.I18n.t = translate;
    global.I18n.locale = locale;
    global.I18n.messages = messages;
})();
