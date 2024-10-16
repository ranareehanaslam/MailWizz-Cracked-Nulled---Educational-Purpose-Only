/**
 * 
 * @param params
 * @returns {{getHtml: (function(): Promise<unknown>), shouldOpen: (function(): boolean), getCss: (function(): Promise<unknown>), isEnabled: (function(): boolean|*), toggle: ((function(): Promise<boolean>)|*), getFullHtml: (function(): Promise<unknown>), close: (function(): Promise<unknown>), open: (function(): Promise<unknown>)}}
 * @constructor
 */
window.TemplateBuilderHandler = function(params){

    /**
     * Defaults
     * @type {{builderId: string, options: {}, instance: boolean, enabled: boolean, html: string, css: string}}
     */
    const defaults = {
        builderId : '',
        options   : {},
        instance  : false,
        enabled   : false,
        html      : '',
        css       : '',
        fullHtml  : ''
    };

    const builder = $.extend({}, defaults, params);

    window.Stripo = window.Stripo || {};
    window.Stripo.init(builder.options);

    const stripoReady = () => {
        return window.Stripo.loaded &&
            window.StripoApi &&
            $('#' + builder.options.settingsId).html().length > 0 &&
            $('#' + builder.options.previewId).html().length > 0;
    };

    const $builderWrapper = $('#builder_' + builder.builderId);
    
    const executeHandler = async (handler, intervalFrequency) => {
        return new Promise(resolve => {
            const interval = setInterval(async () => {
                if (!stripoReady()) {
                    return;
                }
                clearInterval(interval);

                resolve(await handler());
            }, intervalFrequency || 50);
        });
    };

    const methods = {
        open: async () => {
            const handler = async () => {
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
                    overflow    : 'scroll',
                    position    : 'absolute',
                    background  : '#fff',
                    border      : '1px solid #c2c2c2',
                    top         : '32px'
                });
                
                Cookies.set('builder_status', 'open', { expires: 365, path: '/' });
                builder.enabled = true;

                $builderWrapper.trigger('templateBuilderHandler.afterOpen');
            };

            return executeHandler(handler);
        },
        close: async () => {

            const handler = async () => {
                if (!builder.enabled) {
                    return;
                }

                $builderWrapper.trigger('templateBuilderHandler.beforeClose');

                $builderWrapper.attr('style', '');
                $builderWrapper.hide();

                Cookies.set('builder_status', 'closed', { expires: 365, path: '/' });
                builder.enabled = false;

                $builderWrapper.trigger('templateBuilderHandler.afterClose');
            };

            return executeHandler(handler);
        },
        toggle: async () => {
            if (!builder.builderId) {
                return false;
            }
            if (builder.enabled) {
                await methods.close();
            } else {
                await methods.open();
            }
            return false;
        },
        shouldOpen: () => {
            return Cookies.get('builder_status') === 'open';
        },
        isEnabled: () => {
            return builder.enabled;
        },
        getInstance: () => {
            return null;
        },
        getHtml: async () => {
            return executeHandler(() => {
                return new Promise(resolve => {
                    window.StripoApi.getTemplate((html, css) => {
                        resolve(html);
                    })
                });
            });
        },
        getCss: async () => {
            return executeHandler(() => {
                return new Promise(resolve => {
                    window.StripoApi.getTemplate((html, css) => {
                        resolve(css);
                    })
                });
            }, 50);
        },
        getFullHtml: async () => {
            return executeHandler(() => {
                return new Promise(resolve => {
                    window.StripoApi.compileEmail((error, html, ampHtml, ampErrors) => {
                        resolve(html);
                    })
                });
            });
        },
        getJson: async () => {
            return executeHandler(() => {
                return new Promise(resolve => {
                    resolve({
                        html: methods.getHtml(),
                        fullHtml: methods.getFullHtml(),
                        css: methods.getCss(),
                    });
                });
            }) ;
        },
    };

    return methods;
};
