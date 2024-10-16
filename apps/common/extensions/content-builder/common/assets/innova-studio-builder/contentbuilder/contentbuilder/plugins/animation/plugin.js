/*
Scroll Animation Plugin
*/

(function () {
    if(typeof _cb === 'undefined') return;

    var button_html = '<button id="btnScrollAnim" title="'+_cb.out('Animation')+'">' +
        '<svg class="is-icon-flex" style="fill:rgba(0, 0, 0, 0.7);width:14px;height:14px;"><use xlink:href="#icon-hand-move"></use></svg>' +
    '</button>';

    _cb.addButton('animation', button_html, '#btnScrollAnim', function () {

        contentbox.openAnimationPanel();

    });

    _cb.addButton2('animation', button_html, '#btnScrollAnim', function () {

        contentbox.openAnimationPanel();

    });

})();

