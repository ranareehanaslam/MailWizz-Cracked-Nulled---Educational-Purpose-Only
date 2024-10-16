/*
    Search & Replace Plugin
*/

(function () {
    if(typeof _cb === 'undefined') return;

    var js1 = 'https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-core.min.js';
    var js2 = 'https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-textrange.min.js'

    // _cb.getScript(js1);
    // _cb.getScript(js2);
    _cb.getScripts([js1,js2]); //Get js1 first, then after loaded, get js2

    var html = '<div class="is-modal is-modal-content searchreplace" tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true">' +
                    '<div class="is-modal-bar is-draggable" style="position: absolute;top: 0;left: 0;width: 100%;z-index:1;line-height:32px;height:32px;">' + _cb.out('Search & Replace') +
                        '<div class="is-modal-close" style="z-index:1;width:32px;height:32px;position:absolute;top:0px;right:0px;box-sizing:border-box;padding:0;line-height:32px;font-size: 12px;text-align:center;cursor:pointer;">&#10005;</div>' +
                    '</div>' +
                    '<iframe style="position: absolute;top: 0;left: 0;width:100%;height:100%;border:none;border-top:32px solid transparent;margin:0;box-sizing:border-box;" src="about:blank"></iframe>' +
                '</div>' +
                '<svg width="0" height="0" style="position:absolute;display:none;">' +
                    '<defs>' +
                        '<symbol viewBox="0 0 512 512" id="ion-ios-search-strong"><path d="M344.5 298c15-23.6 23.8-51.6 23.8-81.7 0-84.1-68.1-152.3-152.1-152.3C132.1 64 64 132.2 64 216.3c0 84.1 68.1 152.3 152.1 152.3 30.5 0 58.9-9 82.7-24.4l6.9-4.8L414.3 448l33.7-34.3-108.5-108.6 5-7.1zm-43.1-166.8c22.7 22.7 35.2 52.9 35.2 85s-12.5 62.3-35.2 85c-22.7 22.7-52.9 35.2-85 35.2s-62.3-12.5-85-35.2c-22.7-22.7-35.2-52.9-35.2-85s12.5-62.3 35.2-85c22.7-22.7 52.9-35.2 85-35.2s62.3 12.5 85 35.2z"></path></symbol>' +
                    '</defs>' +
                '</svg>';

    _cb.addHtml(html);

    var css = '<style>#_cbhtml .is-modal.searchreplace {' +
            'z-index:10004;position:fixed;width:500px;height:265px;top:50%;left:50%;margin-top:-155px;margin-left:-250px;' +
        '}' +
        '</style>';

    _cb.addCss(css);

    var button = '<button class="searchreplace-button" title="Search & Replace" style="font-size:15px;vertical-align:bottom;">' +
                    '<svg class="is-icon-flex" style="width:17px;height:17px;"><use xlink:href="#ion-ios-search-strong"></use></svg>' +
                '</button>';

    _cb.addButton('searchreplace', button, '.searchreplace-button', function () {

        var modal = document.querySelector(".is-modal.searchreplace");
        _cb.showModal(modal);

        var btnClose = modal.querySelector('.is-modal-close');
        btnClose.addEventListener('click', function(e){
            _cb.hideModal(modal);
        });

        // var scriptPath = _cb.getScriptPath();
        // modal.querySelector('iframe').src = scriptPath + 'plugins/searchreplace/searchreplace.html';

        // var wraper = _cb.getScope();
        // $wraper.focusEnd();
        
        iframe = modal.querySelector('iframe');
        doc = iframe.contentWindow.document;
        doc.open();
        doc.write(getHTML());
        doc.close();

    });

    if(_cb.iframe) {
        var iframeHtml = `
            <script src="https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-core.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-textrange.min.js"></script>
            <script>
            window.findOne = (target, caseSensitive, within, startNode, startPos)=>{

                if (rangy.supported) {
                    var range = rangy.createRange();
                    var searchScopeRange = rangy.createRange();

                    searchScopeRange.selectNodeContents(within);

                    if (startNode != null && startPos != null) {
                        searchScopeRange.setStart(startNode, startPos);
                    }

                    var options = {
                        caseSensitive: caseSensitive,
                        wholeWordsOnly: true,
                        withinRange: searchScopeRange
                    };

                    if (target !== "") {
                        let result = (range.findText(target, options));

                        selectRange(range.startContainer, range.endContainer, range.startOffset, range.endOffset);
                        
                        return result;
                    }
                }

                function selectRange(startNode, endNode, startPos, endPos) {
                    var range = document.createRange()
                    range.setStart(startNode, startPos);
                    range.setEnd(endNode, endPos);

                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
            </script>
        `;


        const contentStuff = _cb.doc.querySelector('#_cbhtml');
        const range = _cb.doc.createRange(); 
        range.setStart(contentStuff, 0);
        contentStuff.appendChild(range.createContextualFragment(iframeHtml) );  
    }
    /*
    _cb.addButton2('searchreplace', button, '.searchreplace-button', function () {

        var modal = document.querySelector(".is-modal.searchreplace");
        modal.className = modal.className + ' active';

        var btnClose = modal.querySelector('.is-modal-close');
        btnClose.addEventListener('click', function(e){
            _cb.hideModal(modal);
        });

        // var scriptPath = _cb.getScriptPath();
        // modal.querySelector('iframe').src = scriptPath + 'plugins/searchreplace/searchreplace.html';

        // var wraper = _cb.getScope();
        // $wraper.focusEnd();

        iframe = modal.querySelector('iframe');
        doc = iframe.contentWindow.document;
        doc.open();
        doc.write(getHTML());
        doc.close();

    });
    */
    
    function getHTML() {

        const html = `
        <!DOCTYPE HTML>
        <html>
        <head>
            <meta charset="utf-8">
            <title></title>
            <style>
                body {margin:20px 20px 0;overflow-x:hidden;overflow-y:auto;font-family:sans-serif;
                    font-size: 12px;
                    letter-spacing: 1px;
                    font-weight: 300;}
                .container {}
                .container > div {text-align:center;font-size:24px;cursor:pointer;margin: 0;display:inline-block;float:left;width:25%;height:80px;line-height:80px;border:#eee 1px solid;box-sizing:border-box;}
                .clearfix:before, .clearfix:after {content: " ";display: table;}
                .clearfix:after {clear: both;}
                .clearfix {*zoom: 1;}
                
                .inptext {
                    width: 90%;
                    height: 45px;
                    box-sizing: border-box;
                    margin: 0;
                    font-family: sans-serif;
                    font-size: 15px;
                    letter-spacing: 1px;
                    padding: 0;
                    padding-left: 8px;
                    display: inline-block;
                    border: none;
                    border-radius: 0;
                    color: ${_cb.styleInputColor};
                    background-color: ${_cb.styleInputBackground};
                    border-bottom: ${_cb.styleInputBorderBottom};
                }
                #chkMatchCase {opacity:0.9}
                button {
                    width: 55px;
                    height: 45px;
                    background-color: ${_cb.styleButtonClassicBackground};
                    color: ${_cb.styleButtonClassicColor};
                    border: transparent 1px solid;
                    border-bottom: ${_cb.styleInputBorderBottom};
                    font-family: sans-serif;
                    font-size: 12px;
                    letter-spacing: 1px;
                    font-weight: 300;
                    opacity: 1;
                    line-height: 1;
                    display: inline-block;
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                    cursor: pointer;
                    text-transform: none;
                    text-align: center;
                    position: relative;
                    border-radius: 0;
                    user-select: none;
                    -moz-user-select: none;
                    -khtml-user-select: none;
                    -webkit-user-select: none;
                    -o-user-select: none;
                    white-space: nowrap;
                    display: flex;
                    align-items: center;
                    justify-content: center;       
                }
                .inptext:focus {outline:none}
                button:focus {outline:none;}
                .input-replaceall {
                    border: none;
                }
                button:hover {
                    background-color: ${_cb.styleButtonClassicBackgroundHover};
                }

                label {
                    color: ${_cb.styleLabelColor};
                }
            </style>
        </head>
        <body>
        <form autocomplete="off">
        <div style="display:flex;justify-content:space-between;">
            <input id="txtSearch" tabindex="1" class="inptext" type="text" style="width:100%;" autocomplete="off" />
            <button class="input-search" style="width:130px;">Search Next</button>
        </div>
        <div style="margin:10px 0">
            <label for="txtReplaceWith">Replace with:</label>
        </div>
        <div style="display:flex;justify-content:space-between;">
            <input id="txtReplaceWith" tabindex="2" class="inptext" type="text" style="width:100%;" autocomplete="off" />
            <button class="input-replace" style="width:130px;">Replace</button>
        </div>
        <div style="display:flex;justify-content:space-between;margin:17px 0;">
            <div>
                <label id="lblMatchCase" for="chkMatchCase">
                    <input type="checkbox" name="chkMatchCase" id="chkMatchCase"> Match case
                </label>
                <div id="divStatus" style="margin-top:12px;"></div>
            </div>
            <button class="input-replaceall" style="width:130px;">Replace All</button>
        </div>
        </form>
        
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script>
            var nReplaceCount = 0;
         
            jQuery(document).ready(function () {
        
                jQuery('#txtSearch').focus();
        
                jQuery('.input-replaceall').click(function (e) {
                    
                    e.preventDefault();

                    jQuery('#divStatus').html('');
        
                    var target = jQuery('#txtSearch').val();
                    var replaceWith = jQuery('#txtReplaceWith').val();
                    if (jQuery("#chkMatchCase").prop("checked")) {
                        var bMatchCase = true;
                    } else {
                        var bMatchCase = false;
                    }
        
                    var within = parent._cb.getScope();
        
                    var sel = parent._cb.win.getSelection();
        
                    var r1 = parent.document.createRange();
                    r1.selectNodeContents(within);
        
                    // https://stackoverflow.com/questions/32192664/how-to-select-a-given-string-repeatedly-within-the-text-of-a-contenteditable-ele
                    if (sel.toString().search(target) > -1) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.focusNode, sel.focusOffset);
                        if (ok) {
                            var rng = parent._cb.win.getSelection().getRangeAt(0);
                            rng.deleteContents();
                            rng.insertNode(document.createTextNode(replaceWith));
                            nReplaceCount++;
                        }
                        jQuery('.input-replaceall').trigger('click');
                    } else if (sel.toString().length > 0) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.focusNode, sel.focusOffset);
                        if (ok) {
                            var rng = parent._cb.win.getSelection().getRangeAt(0);
                            rng.deleteContents();
                            rng.insertNode(document.createTextNode(replaceWith));
                            nReplaceCount++;
                        }
                        jQuery('.input-replaceall').trigger('click');
                    } else if (sel.rangeCount) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, r1.commonAncestorContainer, r1.startOffset);
                        if (ok) {
                            var rng = parent._cb.win.getSelection().getRangeAt(0);
                            rng.deleteContents();
                            rng.insertNode(document.createTextNode(replaceWith));
                            nReplaceCount++;
                            jQuery('.input-replaceall').trigger('click');
                        } else {
                            jQuery('#divStatus').html(nReplaceCount + ' occurrence(s) replaced.');
                            nReplaceCount = 0;
                        }
                    } 
                });
        
                jQuery('.input-search').click(function (e) {

                    e.preventDefault();
        
                    var target = jQuery('#txtSearch').val();
                    var replaceWith = jQuery('#txtReplaceWith').val();
                    if (jQuery("#chkMatchCase").prop("checked")) {
                        var bMatchCase = true;
                    } else {
                        var bMatchCase = false;
                    }
        
                    var within = parent._cb.getScope();
        
                    var sel = parent._cb.win.getSelection();
        
                    var r1 = parent._cb.doc.createRange();
                    r1.selectNodeContents(within);
                    
                    jQuery('#divStatus').html('');

                    if (sel.toString().search(target) > -1) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.focusNode, sel.focusOffset);
                        if (ok) {
                            jQuery('#divStatus').html('');
                        } else {
        
                        }
                    } else if (sel.toString().length > 0) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.focusNode, sel.focusOffset);
                        if (ok) {
                            jQuery('#divStatus').html('');
                        } else {
                            jQuery('#divStatus').html('Passed the end of the content.');
                            var ok = parent._cb.win.findOne(target, bMatchCase, within, r1.commonAncestorContainer, r1.startOffset);
                            if (ok) {
                                jQuery('#divStatus').html('');
                            } else {
                                jQuery('#divStatus').html('The specified text was not found.');
                            }
                        }
                    } else if (sel.rangeCount) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.anchorNode, sel.anchorOffset);
                        if (ok) {
                            jQuery('#divStatus').html('');
                        } else {
                            var ok = parent._cb.win.findOne(target, bMatchCase, within, r1.commonAncestorContainer, r1.startOffset);
                            if (ok) {
                                jQuery('#divStatus').html('');
                            } else {
                                jQuery('#divStatus').html('The specified text was not found.');
                            }
                        }
                    }

                });
        
                jQuery('.input-replace').click(function (e) {
                    
                    e.preventDefault();
        
                    var target = jQuery('#txtSearch').val();
                    var replaceWith = jQuery('#txtReplaceWith').val();
                    if (jQuery("#chkMatchCase").prop("checked")) {
                        var bMatchCase = true;
                    } else {
                        var bMatchCase = false;
                    }
        
                    var within = parent._cb.getScope();
        
                    var sel = parent._cb.win.getSelection();
        
                    var r1 = parent.document.createRange();
                    r1.selectNodeContents(within);
        
                    var text = getSelected(parent._cb.win);
                    if (text) {
                        if ((((text + '').toLowerCase() == target.toLowerCase()) && bMatchCase==false) ||
                            ((text == target) && bMatchCase == true)) {
                            var rng = parent._cb.win.getSelection().getRangeAt(0);
                            rng.deleteContents();
                            rng.insertNode(document.createTextNode(replaceWith));
                        }
                    }
        
                    if (sel.rangeCount) {
                        var ok = parent._cb.win.findOne(target, bMatchCase, within, sel.anchorNode, sel.anchorOffset);
                        if (ok) {
                            jQuery('#divStatus').html('');
                        } else {
                            var ok = parent._cb.win.findOne(target, bMatchCase, within, r1.commonAncestorContainer, r1.startOffset);
                            // if (ok) {
                            //     jQuery('#divStatus').html('');
                            // } else {
                            //     jQuery('#divStatus').html('The specified text was not found.');
                            // }
                        }
                    }
                });
        
            });
        
            function getSelected(window) {
                if (window.getSelection) { return window.getSelection(); }
                else if (document.getSelection) { return document.getSelection(); }
                else {
                    var selection = document.selection && document.selection.createRange();
                    if (selection.text) { return selection.text; }
                    return false;
                }
                return false;
            }

            document.addEventListener('keydown', (e)=>{
                if(e.keyCode === 27) { // escape key

                    var modal = parent.document.querySelector(".is-modal.searchreplace");
                    parent._cb.hideModal(modal);
                }
            });
        
        </script>
        </body>
        </html>
        
        `;

        return html;
    }

})();


// https://stackoverflow.com/questions/32192664/how-to-select-a-given-string-repeatedly-within-the-text-of-a-contenteditable-ele
function findOne(target, caseSensitive, within, startNode, startPos) {

    if (rangy.supported) {
        var range = rangy.createRange();
        var searchScopeRange = rangy.createRange();

        searchScopeRange.selectNodeContents(within);

        if (startNode != null && startPos != null) {
            searchScopeRange.setStart(startNode, startPos);
        }

        var options = {
            caseSensitive: caseSensitive,
            wholeWordsOnly: true,
            withinRange: searchScopeRange
        };

        if (target !== "") {
            let result = (range.findText(target, options));

            selectRange(range.startContainer, range.endContainer, range.startOffset, range.endOffset);
            
            return result;
            // var text = getSelected(parent);
            // if (text != '') {
            //     return true;
            // } else {
            //     return false;
            // }
        }
    }

    function selectRange(startNode, endNode, startPos, endPos) {
        var range = document.createRange()
        range.setStart(startNode, startPos);
        range.setEnd(endNode, endPos);

        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    function getSelected(window) {
        if (window.getSelection) { return window.getSelection(); }
        else if (document.getSelection) { return document.getSelection(); }
        else {
            var selection = document.selection && document.selection.createRange();
            if (selection.text) { return selection.text; }
            return false;
        }
        return false;
    }

}

// https://stackoverflow.com/questions/1181700/set-cursor-position-on-contenteditable-div/3323835
// $.fn.focusEnd = function () {
//     $(this).focus();
//     var tmp = $('<span />').appendTo($(this)),
//         node = tmp.get(0),
//         range = null,
//         sel = null;

//     if (document.selection) {
//         range = document.body.createTextRange();
//         range.moveToElementText(node);
//         range.select();
//     } else if (window.getSelection) {
//         range = document.createRange();
//         range.selectNode(node);
//         sel = window.getSelection();
//         sel.removeAllRanges();
//         sel.addRange(range);
//     }
//     tmp.remove();
//     return this;
// }