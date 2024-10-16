/**
 * 
 * @param params
 * @returns {{getHtml: (function(): *), shouldOpen: (function(): boolean), getCss: (function(): string), isEnabled: (function(): boolean|*), toggle: ((function(): boolean)|*), getFullHtml: (function(): *), getInstance: (function(): Window.TemplateBuilder), close: methods.close, open: methods.open, getJson: (function(): *)}}
 * @constructor
 */
window.TemplateBuilderHandler = function(params){

    /**
     * Defaults
     * @type {{builderId: string, options: {}, instance: {}, enabled: boolean, json: {}}}
     */
    const defaults = {
        builderId : '',
        options   : {},
        instance  : {},
        enabled   : false,
        json      : {}
    };

    const builder = $.extend({}, defaults, params);
    builder.instance = new TemplateBuilder(builder.options);

    const $builderWrapper = $('#builder_' + builder.builderId);

    const methods = {
        open: function(){

            $builderWrapper.trigger('templateBuilderHandler.beforeOpen');

            $builderWrapper
                .closest('.form-group')
                .css({
                    position: 'relative'
                });

            $builderWrapper.css({
                width       : '100%',
                height      : $('#cke_' + builder.builderId).height() + 5,
                position    : 'absolute',
                background  : '#fff',
                border      : '1px solid #c2c2c2',
                top         : '32px'
            });

            if (!$.isEmptyObject(builder.json)) {
                if (typeof(builder.json) === 'object') {
                    methods.getInstance().mountBuilder(builder.json);
                } else {
                    methods.getInstance().setJson(builder.json);
                }
            } else {
                methods.getInstance().mountBuilder();
            }

            Cookies.set('builder_status', 'open', { expires: 365, path: '/' });
            builder.enabled = true;

            $builderWrapper.trigger('templateBuilderHandler.afterOpen');
        },
        close: function(){
            $builderWrapper.trigger('templateBuilderHandler.beforeClose');

            builder.json = methods.getInstance().getJson();
            
            $builderWrapper.attr('style', '');
            Cookies.set('builder_status', 'closed', { expires: 365, path: '/' });
            builder.enabled = false;

            $builderWrapper.trigger('templateBuilderHandler.afterClose');

            methods.getInstance().unmountBuilder();
        },
        toggle: function(){
            if (!builder.builderId) {
                return false;
            }
            if (builder.enabled) {
                methods.close();
            } else {
                methods.open();
            }
            return false;
        },
        shouldOpen: function(){
            return Cookies.get('builder_status') === 'open';
        },
        getInstance: function(){
            return builder.instance;
        },
        getJson: function () {
            return methods.getInstance().getJson();
        },
        getHtml: function () {
            return methods.getInstance().getHtml();
        },
        getFullHtml: function() {
            return methods.getInstance().getHtml();
        },
        getCss: function() {
            return '';
        },
        isEnabled: function() {
            return builder.enabled;
        }
    };

    return methods;
};
