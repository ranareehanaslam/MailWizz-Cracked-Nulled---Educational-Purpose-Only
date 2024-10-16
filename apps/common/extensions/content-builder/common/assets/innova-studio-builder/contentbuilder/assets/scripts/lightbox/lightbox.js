var Lightbox = (function () {
   'use strict';

   class Dom {
     createElement(tag) {
       return document.createElement(tag);
     }

     appendChild(parent, child) {
       if (parent) parent.appendChild(child);
     }

     appendHtml(parent, html) {
       if (parent) parent.insertAdjacentHTML('beforeend', html);
     }

     addEventListener(parent, type, listener) {
       if (parent) parent.addEventListener(type, listener);
     }

     addClass(element, classname) {
       if (!element) return;
       if (this.hasClass(element, classname)) return;
       if (element.classList.length === 0) element.className = classname;else element.className = element.className + ' ' + classname;
       element.className = element.className.replace(/  +/g, ' '); //else element.classList.add(classname); //error if there is -
     }

     removeClass(element, classname) {
       if (!element) return;

       if (element.classList.length > 0) {
         // element.className = element.className.replace(new RegExp('\\b'+ classname+'\\b', 'g'), '');
         // element.className = element.className.replace(/  +/g, ' ');
         let i, j, imax, jmax;
         let classesToDel = classname.split(' ');

         for (i = 0, imax = classesToDel.length; i < imax; ++i) {
           if (!classesToDel[i]) continue;
           let classtoDel = classesToDel[i]; // https://jsperf.com/removeclass-methods 

           let sClassName = '';
           let currentClasses = element.className.split(' ');

           for (j = 0, jmax = currentClasses.length; j < jmax; ++j) {
             if (!currentClasses[j]) continue;
             if (currentClasses[j] !== classtoDel) sClassName += currentClasses[j] + ' ';
           }

           element.className = sClassName.trim();
         }

         if (element.className === '') element.removeAttribute('class');
       }
     } // https://plainjs.com/javascript/attributes/adding-removing-and-testing-for-classes-9/
     // addClass(element, classname) {
     //     console.log(element.classList)
     //     if (element.classList) element.classList.add(classname);
     //     else if (!this.hasClass(element, classname)) element.className += ' ' + classname;
     // }
     // removeClass(element, classname) {
     //     if (element.classList) element.classList.remove(classname);
     //     else element.className = element.className.replace(new RegExp('\\b'+ classname+'\\b', 'g'), '');
     // }


     hasClass(element, classname) {
       if (!element) return false;

       try {
         let s = element.getAttribute('class');
         return new RegExp('\\b' + classname + '\\b').test(s);
       } catch (e) {// Do Nothing
         // console.log(element);
       } //return element.classList ? element.classList.contains(classname) : new RegExp('\\b'+ classname+'\\b').test(element.className);

     }

     moveAfter(element, targetElement) {
       targetElement.parentNode.insertBefore(element, targetElement);
       targetElement.parentNode.insertBefore(targetElement, targetElement.previousElementSibling);
     } // https://stackoverflow.com/questions/10381296/best-way-to-get-child-nodes


     elementChildren(element) {
       const childNodes = element.childNodes;
       let children = [];
       let i = childNodes.length;

       while (i--) {
         if (childNodes[i].nodeType === 1
         /*&& childNodes[i].tagName === 'DIV'*/
         ) {
           children.unshift(childNodes[i]);
         }
       }

       return children;
     }

     parentsHasClass(element, classname) {
       while (element) {
         // if(classname==='is-side') console.log(element.nodeName); // NOTE: click on svg can still returns undefined in IE11
         if (!element.tagName) return false;
         if (element.tagName === 'BODY' || element.tagName === 'HTML') return false; // if(!element.classList) {
         //     console.log('no classList');
         //     return false;
         // }

         if (this.hasClass(element, classname)) {
           return true;
         } // TODO: if(element.nodeName.toLowerCase() === 'svg') console.log(element);


         element = element.parentNode;
       }
     }

     parentsHasId(element, id) {
       while (element) {
         if (!element.tagName) return false;
         if (element.tagName === 'BODY' || element.tagName === 'HTML') return false;

         if (element.id === id) {
           return true;
         }

         element = element.parentNode;
       }
     }

     parentsHasTag(element, tagname) {
       while (element) {
         if (!element.tagName) return false;
         if (element.tagName === 'BODY' || element.tagName === 'HTML') return false;

         if (element.tagName.toLowerCase() === tagname.toLowerCase()) {
           return true;
         }

         element = element.parentNode;
       }
     }

     parentsHasAttribute(element, attrname) {
       while (element) {
         if (!element.tagName) return false;
         if (element.tagName === 'BODY' || element.tagName === 'HTML') return false;

         try {
           if (element.hasAttribute(attrname)) {
             // error on svg element
             return true;
           }
         } catch (e) {// Do Nothing
           // console.log(element);
           // return false;
         }

         element = element.parentNode;
       }
     }

     parentsHasElement(element, tagname) {
       while (element) {
         if (!element.tagName) return false;
         if (element.tagName === 'BODY' || element.tagName === 'HTML') return false;
         element = element.parentNode;
         if (!element) return false;
         if (!element.tagName) return false;

         if (element.tagName.toLowerCase() === tagname) {
           return true;
         }
       }
     }

     removeClasses(elms, classname) {
       for (let i = 0; i < elms.length; i++) {
         elms[i].classList.remove(classname);
       }
     }

     removeAttributes(elms, attrname) {
       for (let i = 0; i < elms.length; i++) {
         elms[i].removeAttribute(attrname);
       }
     }

     removeElements(elms) {
       Array.prototype.forEach.call(elms, el => {
         el.parentNode.removeChild(el);
       });
     }

     getStyle(element, property) {
       return window.getComputedStyle ? window.getComputedStyle(element, null).getPropertyValue(property) : element.style[property.replace(/-([a-z])/g, function (g) {
         return g[1].toUpperCase();
       })];
     }

   }

   const dom = new Dom();

   class Lightbox {
     constructor(opts = {}) {
       let defaults = {
         arrow: true
       };
       this.opts = Object.assign(this, defaults, opts);
       this.arrow = this.opts.arrow;
       let builderStuff = document.querySelector('#_cbhtml'); // All editing controls will be placed within <div id="_cbhtml">...</div>

       if (!builderStuff) {
         builderStuff = dom.createElement('div');
         builderStuff.id = '_cbhtml';
         builderStuff.className = 'is-ui';
         dom.appendChild(document.body, builderStuff);
       }

       this.builderStuff = builderStuff;
       let div = this.builderStuff.querySelector('#_lightbox');

       if (!div) {
         let html = `

                <div class="is-lightbox lightbox-all" tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true">
                    <button class="cmd-lightbox-close" title="Close" type="button">
                        <svg><use xlink:href="#_svgLightboxClose"></use></svg>
                    </button>

                    <div class="lightbox-content" style="width:100%;">
                    </div>

                    <button class="cmd-lightbox-prev" title="Previous" type="button" style="top:50%;left:3px;">
                        <svg><use xlink:href="#_svgLightboxLeft"></use></svg>
                    </button>
                    <button class="cmd-lightbox-next" title="Next" type="button" style="top:50%;left:auto;right:3px">
                        <svg><use xlink:href="#_svgLightboxRight"></use></svg>
                    </button>
                </div>

                <svg width="0" height="0" style="position:absolute;display:none;">
                    <defs>
                        <symbol viewBox="0 0 24 24" id="_svgLightboxClose" stroke-width="0.7" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                        </symbol>
                        <symbol viewBox="0 0 24 24" id="_svgLightboxLeft" stroke-width="0.7" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <polyline points="15 6 9 12 15 18"></polyline>
                        </symbol>
                        <symbol viewBox="0 0 24 24" id="_svgLightboxRight" stroke-width="0.7" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <polyline points="9 6 15 12 9 18"></polyline>
                        </symbol>
                    </defs>
                </svg>
            `; // Click overlay will close lightbox

         this.builderStuff.insertAdjacentHTML('afterbegin', html); // this.init();
       }

       const close = () => {
         let lightbox = document.querySelector('.is-lightbox.lightbox-all.active');
         dom.removeClass(lightbox, 'active');
         const btnClose = lightbox.querySelector('.cmd-lightbox-close');
         btnClose.style.opacity = 0;
         document.body.style.overflowY = '';
         setTimeout(() => {
           let iframe = lightbox.querySelector('iframe');

           if (iframe) {
             iframe.setAttribute('src', 'about:blank');
           }

           const div = lightbox.querySelector('.lightbox-content');
           if (lightbox.querySelector('video')) div.innerHTML = '';
           lightbox.style.display = '';
           if (this.elmFocus) this.elmFocus.focus();
         }, 300);
       };

       const prev = () => {
         const list = this.getList();
         let lightbox = document.querySelector('.is-lightbox.lightbox-all.active');
         let url = lightbox.getAttribute('data-url');
         list.forEach(item => {
           if (url === item.url) {
             const currentIndex = list.indexOf(item);
             let prevIndex;
             prevIndex = (currentIndex - 1) % list.length;

             if (prevIndex === -1) {
               prevIndex = list.length - 1;
             }

             this.openThen(list[prevIndex]);
             const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
             btnPrev.focus();
           }
         });
       };

       const next = () => {
         const list = this.getList();
         let lightbox = document.querySelector('.is-lightbox.lightbox-all.active');
         let url = lightbox.getAttribute('data-url');
         list.forEach(item => {
           if (url === item.url) {
             const currentIndex = list.indexOf(item);
             const nextIndex = (currentIndex + 1) % list.length;
             this.openThen(list[nextIndex]);
             const btnNext = lightbox.querySelector('.cmd-lightbox-next');
             btnNext.focus();
           }
         });
       };

       const handleItemKeyDown = e => {
         if (e.keyCode === 27) {
           // escape key
           close();
           e.preventDefault();
         }
         /*else if ((e.which === 9 && !e.shiftKey)) { // tab key pressed
           let lightbox = document.querySelector('.is-lightbox.active');
           const btnClose = lightbox.querySelector('.cmd-lightbox-close');
           btnClose.focus();
           e.preventDefault();
         }*/


         if (this.arrow) {
           if (e.which === 37 && !e.shiftKey) {
             // left
             prev();
           }

           if (e.which === 39 && !e.shiftKey) {
             // right
             next();
           }
         }
       };

       const lightboxes = this.builderStuff.querySelectorAll('div.is-lightbox.lightbox-all');
       lightboxes.forEach(lightbox => {
         lightbox.addEventListener('keydown', handleItemKeyDown); // Click overlay will close lightbox

         lightbox.addEventListener('click', e => {
           if (dom.parentsHasClass(e.target, 'lightbox-content')) return;
           close();
           e.preventDefault();
         });
         const btnClose = lightbox.querySelector('.cmd-lightbox-close');
         btnClose.addEventListener('click', e => {
           close();
           e.preventDefault();
           e.stopImmediatePropagation();
         });
         btnClose.addEventListener('keydown', e => {
           if (e.keyCode === 13 || e.keyCode === 32) {
             // enter or spacebar key
             close();
             e.preventDefault();
           }
         });
         const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
         if (btnPrev) btnPrev.addEventListener('click', e => {
           prev();
           e.preventDefault();
           e.stopImmediatePropagation();
         });
         if (btnPrev) btnPrev.addEventListener('keydown', e => {
           if (e.keyCode === 13 || e.keyCode === 32) {
             // enter or spacebar key
             prev();
             e.preventDefault();
           }
         });
         const btnNext = lightbox.querySelector('.cmd-lightbox-next');
         if (btnNext) btnNext.addEventListener('click', e => {
           next();
           e.preventDefault();
           e.stopImmediatePropagation();
         });
         if (btnNext) btnNext.addEventListener('keydown', e => {
           if (e.keyCode === 13 || e.keyCode === 32) {
             // enter or spacebar key
             next();
             e.preventDefault();
           }
         });
       });
     } //constructor


     init() {
       // Find all elements that have .block-click & .button-click class.
       // Then read data attributes:
       //  - data-modal-theme: dark or light
       //  - data-modal-color (background color)
       //  - data-modal-image (image url)
       //  - data-modal-video (video url)
       //  - data-modal-externalvideo (ex. youtube url)
       let blockClickables = document.querySelectorAll('.block-click,.button-click,.is-lightbox');

       if (this.parent) {
         blockClickables = this.parent.querySelectorAll('.block-click,.button-click,.is-lightbox');
       }

       blockClickables.forEach(block => {
         block.addEventListener('click', e => {
           let url;
           let extension;

           if (block.tagName.toLowerCase() === 'a') {
             url = block.getAttribute('href');
             extension = url.split('.').pop().split('?')[0].split('#')[0];
           } else if (block.getAttribute('data-modal-url')) {
             url = block.getAttribute('data-modal-url');
             extension = url.split('.').pop().split('?')[0].split('#')[0];
           } else {
             if (block.getAttribute('data-modal-image') || block.getAttribute('data-modal-video') || block.getAttribute('data-modal-externalvideo')) ; else {
               // Overlay is clicked
               return;
             }
           }

           let theme = block.getAttribute('data-modal-theme');
           if (!theme) theme = 'light';
           const color = block.getAttribute('data-modal-color');

           if (extension === 'jpg' || extension === 'jpeg' || extension === 'png' || extension === 'gif' || extension === 'webm') {
             this.openImage(url, theme, color);
           } else if (extension === 'mp4') {
             this.openVideo(url, 'dark', color);
           } else if (url.toLowerCase().indexOf('youtube.com') !== -1 || url.toLowerCase().indexOf('youtube-nocookie.com') !== -1 || url.toLowerCase().indexOf('vimeo.com') !== -1) {
             this.openExternalVideo(url, 'dark', color);
           } else {
             // Open link. But check first some attributes.
             if (block.getAttribute('data-modal-image')) {
               url = block.getAttribute('data-modal-image');
               this.openImage(url, theme, color);
             } else if (block.getAttribute('data-modal-video')) {
               url = block.getAttribute('data-modal-video');
               this.openVideo(url, 'dark', color);
             } else if (block.getAttribute('data-modal-externalvideo')) {
               url = block.getAttribute('data-modal-externalvideo');
               this.openExternalVideo(url, 'dark', color);
             } else {
               // Now open link
               window.location.href = url;
             }
           }

           e.preventDefault();
         });
       });
     } //run


     openThen(item) {
       if (item.type === 'image') {
         this.programmatically = true;
         this.openImage(item.url, item.theme, item.color);
       }

       if (item.type === 'video') {
         this.programmatically = true;
         this.openVideo(item.url, item.theme, item.color);
       }

       if (item.type === 'externalvideo') {
         this.programmatically = true;
         this.openExternalVideo(item.url, item.theme, item.color);
       }
     }

     openImage(url, theme, color) {
       if (document.activeElement) {
         this.elmFocus = document.activeElement;

         if (!this.programmatically) {
           const parent = document.activeElement.closest('[data-gallery]');
           this.parent = parent;
         }
       }

       let lightbox = this.builderStuff.querySelector('div.is-lightbox.lightbox-all'); // in case opened in an iframe (ex. preview)

       if (window.frameElement && !lightbox) {
         lightbox = parent.document.querySelector('.is-lightbox.lightbox-all');
       }

       if (!this.programmatically) {
         const list = this.getList();
         const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
         btnPrev.style.opacity = 0;
         const btnNext = lightbox.querySelector('.cmd-lightbox-next');
         btnNext.style.opacity = 0;
         btnPrev.style.display = 'none';
         btnNext.style.display = 'none';

         if (list.length > 1 && this.arrow) {
           btnPrev.style.display = 'flex';
           btnNext.style.display = 'flex';
           setTimeout(() => {
             const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
             btnPrev.style.opacity = 1;
             const btnNext = lightbox.querySelector('.cmd-lightbox-next');
             btnNext.style.opacity = 1;
           }, 500);
         }

         const btnClose = lightbox.querySelector('.cmd-lightbox-close');
         btnClose.style.opacity = 0;
         setTimeout(() => {
           dom.addClass(lightbox, 'active');
           setTimeout(() => {
             btnClose.style.opacity = 1;
           }, 450);
         }, 10);
       }

       lightbox.setAttribute('data-url', url);
       lightbox.classList.remove('lightbox-video');
       lightbox.classList.remove('lightbox-externalvideo');
       lightbox.classList.add('lightbox-image');
       if (color) lightbox.style.backgroundColor = color;
       const div = lightbox.querySelector('.lightbox-content');

       if (!this.programmatically) {
         div.innerHTML = `<img src="${url}">`;
       } else {
         div.style.transition = 'all 200ms ease';
         div.style.opacity = '0';
         setTimeout(() => {
           div.innerHTML = `<img src="${url}">`;
           div.style.opacity = '1';
         }, 200);
       }

       if (theme === 'light') {
         dom.addClass(lightbox, 'light');
         dom.removeClass(lightbox, 'dark');
       } else {
         dom.addClass(lightbox, 'dark');
         dom.removeClass(lightbox, 'light');
       }

       lightbox.style.display = 'flex';
       if (!window.frameElement) document.body.style.overflowY = 'hidden';
       lightbox.focus({
         preventScroll: true
       });
       this.programmatically = false;
     }

     openVideo(url, theme, color) {
       if (document.activeElement) {
         this.elmFocus = document.activeElement;

         if (!this.programmatically) {
           const parent = document.activeElement.closest('[data-gallery]');
           this.parent = parent;
         }
       }

       let lightbox = document.querySelector('.is-lightbox.lightbox-all'); // in case opened in an iframe (ex. preview)

       if (window.frameElement && !lightbox) {
         lightbox = parent.document.querySelector('.is-lightbox.lightbox-all');
       }

       if (!this.programmatically) {
         const list = this.getList();
         const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
         btnPrev.style.opacity = 0;
         const btnNext = lightbox.querySelector('.cmd-lightbox-next');
         btnNext.style.opacity = 0;
         btnPrev.style.display = 'none';
         btnNext.style.display = 'none';

         if (list.length > 1 && this.arrow) {
           btnPrev.style.display = 'flex';
           btnNext.style.display = 'flex';
           setTimeout(() => {
             const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
             btnPrev.style.opacity = 1;
             const btnNext = lightbox.querySelector('.cmd-lightbox-next');
             btnNext.style.opacity = 1;
           }, 450);
         }

         const btnClose = lightbox.querySelector('.cmd-lightbox-close');
         btnClose.style.opacity = 0;
         setTimeout(() => {
           dom.addClass(lightbox, 'active');
           setTimeout(() => {
             btnClose.style.opacity = 1;
           }, 450);
         }, 10);
       }

       lightbox.setAttribute('data-url', url);
       lightbox.classList.add('lightbox-video');
       lightbox.classList.remove('lightbox-externalvideo');
       lightbox.classList.remove('lightbox-image');
       if (color) lightbox.style.backgroundColor = color;
       const div = lightbox.querySelector('.lightbox-content');

       if (!this.programmatically) {
         div.innerHTML = `<video playsinline controls autoplay width="100%">
                <source src="${url}" type="video/mp4">
            </video>`;
       } else {
         div.style.transition = 'all 200ms ease';
         div.style.opacity = '0';
         setTimeout(() => {
           div.innerHTML = `<video playsinline controls autoplay width="100%">
                    <source src="${url}" type="video/mp4">
                </video>`;
           div.style.opacity = '1';
         }, 200);
       }

       if (theme === 'light') {
         dom.addClass(lightbox, 'light');
         dom.removeClass(lightbox, 'dark');
       } else {
         dom.addClass(lightbox, 'dark');
         dom.removeClass(lightbox, 'light');
       }

       lightbox.style.display = 'flex';
       if (!window.frameElement) document.body.style.overflowY = 'hidden';
       lightbox.focus({
         preventScroll: true
       });
       this.programmatically = false;
     }

     openExternalVideo(url, theme, color) {
       if (document.activeElement) {
         this.elmFocus = document.activeElement;

         if (!this.programmatically) {
           const parent = document.activeElement.closest('[data-gallery]');
           this.parent = parent;
         }
       }

       let lightbox = document.querySelector('.is-lightbox.lightbox-all'); // in case opened in an iframe (ex. preview)

       if (window.frameElement && !lightbox) {
         lightbox = parent.document.querySelector('.is-lightbox.lightbox-all');
       }

       if (!this.programmatically) {
         const list = this.getList();
         const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
         btnPrev.style.opacity = 0;
         const btnNext = lightbox.querySelector('.cmd-lightbox-next');
         btnNext.style.opacity = 0;
         btnPrev.style.display = 'none';
         btnNext.style.display = 'none';

         if (list.length > 1 && this.arrow) {
           btnPrev.style.display = 'flex';
           btnNext.style.display = 'flex';
           setTimeout(() => {
             const btnPrev = lightbox.querySelector('.cmd-lightbox-prev');
             btnPrev.style.opacity = 1;
             const btnNext = lightbox.querySelector('.cmd-lightbox-next');
             btnNext.style.opacity = 1;
           }, 500);
         }

         const btnClose = lightbox.querySelector('.cmd-lightbox-close');
         btnClose.style.opacity = 0;
         setTimeout(() => {
           dom.addClass(lightbox, 'active');
           setTimeout(() => {
             btnClose.style.opacity = 1;
           }, 450);
         }, 10);
       }

       lightbox.setAttribute('data-url', url);
       lightbox.classList.remove('lightbox-video');
       lightbox.classList.add('lightbox-externalvideo');
       lightbox.classList.remove('lightbox-image');
       if (color) lightbox.style.backgroundColor = color;

       if (theme === 'light') {
         dom.addClass(lightbox, 'light');
         dom.removeClass(lightbox, 'dark');
       } else {
         dom.addClass(lightbox, 'dark');
         dom.removeClass(lightbox, 'light');
       }

       const div = lightbox.querySelector('.lightbox-content');

       if (!this.programmatically) {
         div.innerHTML = `<div class="embed-responsive embed-responsive-16by9" style="width:100%;">
                <iframe width="560" height="315" src="about:blank" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>`;
         const iframe = lightbox.querySelector('iframe');
         url = this.getIframeVideoUrl(url);
         if (url !== '') iframe.setAttribute('src', url);
       } else {
         div.style.transition = 'all 200ms ease';
         div.style.opacity = '0';
         setTimeout(() => {
           div.innerHTML = `<div class="embed-responsive embed-responsive-16by9" style="width:100%;">
                    <iframe width="560" height="315" src="about:blank" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>`;
           div.style.opacity = '1';
           const iframe = lightbox.querySelector('iframe');
           url = this.getIframeVideoUrl(url);
           if (url !== '') iframe.setAttribute('src', url);
         }, 200);
       }

       lightbox.style.display = 'flex';
       if (!window.frameElement) document.body.style.overflowY = 'hidden'; // const div = lightbox.querySelector('.lightbox-content');
       // div.style.width=((div.offsetHeight*16)/9) + 'px';

       lightbox.focus({
         preventScroll: true
       });
       this.programmatically = false;
     }

     getIframeVideoUrl(src) {
       // https://regex101.com/library/OY96XI
       // https://stackoverflow.com/questions/3452546/how-do-i-get-the-youtube-video-id-from-a-url
       // let youRegex = /^http[s]?:\/\/(((www.youtube.com\/watch\?(feature=player_detailpage&)?)v=)|(youtu.be\/))([^#&?]*)/;
       let youRegex = /^.*(?:https?:)?(?:\/\/)?(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/\S*?[^\w\s-])((?!videoseries)[\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:['"][^<>]*>|<\/a>))[?=&+%\w.-]*/;
       let vimeoRegex = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/)|(video\/))?([0-9]+)\/?/; // let youRegexMatches = youRegex.exec(src);

       let youRegexMatches = src.match(youRegex);
       let vimeoRegexMatches = vimeoRegex.exec(src); // if (youRegexMatches !== null || vimeoRegexMatches !== null) {

       if ((youRegexMatches !== null || vimeoRegexMatches !== null) && src.indexOf('player.vimeo.com') === -1 && src.indexOf('youtube.com/embed/') === -1 && src.indexOf('youtube-nocookie.com/embed/') === -1) {
         if (youRegexMatches !== null) {
           // && youRegexMatches.length >= 7) {
           // let youMatch = youRegexMatches[6];
           let youMatch = youRegexMatches[1]; // src = 'https://www.youtube.com/embed/' + youMatch + '?rel=0&autoplay=1&color=white';

           src = 'https://www.youtube-nocookie.com/embed/' + youMatch + '?rel=0&autoplay=1&color=white';
         }

         if (vimeoRegexMatches !== null && vimeoRegexMatches.length >= 7) {
           let vimeoMatch = vimeoRegexMatches[6];
           src = 'https://player.vimeo.com/video/' + vimeoMatch;
         }
       } else {
         if (src.indexOf('youtube-nocookie.com/embed/') !== -1) {
           if (src.indexOf('?') === -1) {
             src += '?rel=0&autoplay=1&color=white';
           }
         }
       }

       return src;
     }

     getList() {
       const arrGallery = [];
       let blockClickables = document.querySelectorAll('.block-click,.button-click,.is-lightbox');

       if (this.parent) {
         blockClickables = this.parent.querySelectorAll('.block-click,.button-click,.is-lightbox');
       }

       blockClickables.forEach(block => {
         if (!this.parent) {
           if (block.closest('[data-gallery')) return;
         }

         let url;
         let extension;

         if (block.tagName.toLowerCase() === 'a') {
           url = block.getAttribute('href');
           extension = url.split('.').pop().split('?')[0].split('#')[0];
         } else if (block.getAttribute('data-modal-url')) {
           url = block.getAttribute('data-modal-url');
           extension = url.split('.').pop().split('?')[0].split('#')[0];
         }

         let theme = block.getAttribute('data-modal-theme');
         if (!theme) theme = 'light';
         const color = block.getAttribute('data-modal-color');

         if (url) {
           if (extension === 'jpg' || extension === 'jpeg' || extension === 'png' || extension === 'gif' || extension === 'webm') {
             arrGallery.push({
               type: 'image',
               url: url,
               theme: theme,
               color: color
             });
           } else if (extension === 'mp4') {
             arrGallery.push({
               type: 'video',
               url: url,
               theme: 'dark',
               color: color
             });
           } else if (url.toLowerCase().indexOf('youtube.com') !== -1 || url.toLowerCase().indexOf('youtube-nocookie.com') !== -1 || url.toLowerCase().indexOf('vimeo.com') !== -1) {
             arrGallery.push({
               type: 'externalvideo',
               url: url,
               theme: 'dark',
               color: color
             });
           }
         }

         if (block.getAttribute('data-modal-image')) {
           url = block.getAttribute('data-modal-image');
           arrGallery.push({
             type: 'image',
             url: url,
             theme: theme,
             color: color
           });
         } else if (block.getAttribute('data-modal-video')) {
           url = block.getAttribute('data-modal-video');
           arrGallery.push({
             type: 'video',
             url: url,
             theme: 'dark',
             color: color
           });
         } else if (block.getAttribute('data-modal-externalvideo')) {
           url = block.getAttribute('data-modal-externalvideo');
           arrGallery.push({
             type: 'externalvideo',
             url: url,
             theme: 'dark',
             color: color
           });
         }
       });
       return arrGallery;
     }

   } //Lightbox


   window.lightbox = new Lightbox();

   return Lightbox;

})();
