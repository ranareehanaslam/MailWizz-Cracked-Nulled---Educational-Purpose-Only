/*
    Hello World Plugin
*/

(function () {
    if(typeof _cb === 'undefined') return;

    var button_html = '<button id="my_helloworld_button" title="Hello World" style="text-transform:none">hello</button>';

    _cb.addButton('helloworld', button_html, '#my_helloworld_button', function () {

        alert('Hello World');

    });
    _cb.addButton2('helloworld', button_html, '#my_helloworld_button', function () {

        alert('Hello World');

    });

})();