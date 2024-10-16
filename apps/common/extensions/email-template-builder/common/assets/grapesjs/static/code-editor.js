/**
 *
 * @param params
 * @returns {{shouldOpen: (function(): boolean), isEnabled: (function(): boolean|*), toggle: ((function(): boolean)|*), getFullHTML: (function(): string), getInstance: (function(): *), close: methods.close, open: methods.open}}
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
        json      : {},
    };

    const builder = $.extend({}, defaults, params);

    builder.instance = grapesjs.init(builder.options);
    builder.instance.setComponents({});

    // // Register the load default template button
    const panelManager = builder.instance.Panels;
    panelManager.addButton('options', [
        {
            id: 'load-default-template-button',
            className: 'glyphicon glyphicon-upload',
            command: function(editor) {
                if (confirm(params.options.loadDefaultTemplateButton.confirmText)) {
                    editor.runCommand('core:canvas-clear');
                    editor.setComponents(params.options.defaultTemplate);
                }
            },
            attributes: {title: params.options.loadDefaultTemplateButton.title},
            active: false,
        }
    ]);

    // Remove the fullscreen button since it is causing issues
    panelManager.removeButton('options', 'fullscreen');

    // This trick will prevent the form submitting on importing a template
    builder.instance.on('run:gjs-open-import-template', opt => {
        $('.gjs-btn-import').attr('type', 'button');
    });

    const $builderWrapper = $('#builder_' + builder.builderId);

    const methods = {
        open: function(){
            $builderWrapper.trigger('templateBuilderHandler.beforeOpen');

            $builderWrapper.show();

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

            Cookies.set('builder_status', 'open', { expires: 365, path: '/' });
            builder.enabled = true;

            $builderWrapper.trigger('templateBuilderHandler.afterOpen');
        },
        close: function(){
            $builderWrapper.trigger('templateBuilderHandler.beforeClose');

            $builderWrapper.attr('style', '');
            $builderWrapper.hide();

            Cookies.set('builder_status', 'closed', { expires: 365, path: '/' });
            builder.enabled = false;

            $builderWrapper.trigger('templateBuilderHandler.afterClose');
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
        isEnabled: function(){
            return builder.enabled;
        },
        getFullHTML: function() {
            const headComponentsTypes = ['meta', 'link', 'title', 'style', 'script', 'noscript', 'base'];
            const components = methods.getInstance().getComponents();
            let styles = methods.getInstance().getCss();
            const grapesjsDefaultStyle = '* { box-sizing: border-box; } body {margin: 0;}';
            const grapesjsHasNewCss = styles !== grapesjsDefaultStyle;
            // We strip the grapesjs default css if only that is received
            if (grapesjsHasNewCss) {
                styles = `<style>${styles}</style>`;
            } else {
                styles = '';
            }

            let headHtml = '';
            let bodyHtml = '';
            components.each((component) => {
                if (headComponentsTypes.indexOf(component.attributes.tagName) > -1) {
                    headHtml += component.toHTML();
                } else {
                    bodyHtml += component.toHTML();
                }
            });

            return `<!DOCTYPE html>
                <html>
                    <head>
                        ${styles}
                        ${headHtml}
                    </head>
                    <body>${bodyHtml}</body>
                </html>
            `;
        },
        getHtml: function () {
            const headComponentsTypes = ['meta', 'link', 'title', 'style', 'script', 'noscript', 'base'];
            let headHtml = '';
            let bodyHtml = '';
            methods.getInstance().getComponents().each((component) => {
                if (headComponentsTypes.indexOf(component.attributes.tagName) > -1) {
                    headHtml += component.toHTML();
                } else {
                    bodyHtml += component.toHTML();
                }
            });
            return bodyHtml;
        },
        getCss: function() {
            return methods.getInstance().getCss();
        },
        getJson: function() {
            return {
                html: methods.getHtml(),
                fullHtml: methods.getFullHTML(),
                css: methods.getCss(),
            };
        },
    };

    return methods;
};
