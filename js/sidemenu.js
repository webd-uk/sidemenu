var sidemenuJS = function() {
    setTimeout(function() {
        document.body.insertBefore(document.getElementsByClassName('sidemenu')[0], document.body.firstChild);
        var sidemenuCover = document.createElement('section'),
        sidemenuToggles = document.querySelectorAll('.open_sidemenu>a, .open_sidemenu>div>a'),
        sidemenuHiddenButtonWrapper = document.createElement('span'),
        sidemenuHiddenButton = document.createElement('a');
        sidemenuCover.className = 'cover';
        sidemenuHiddenButtonWrapper.id = 'hidden_open_sidemenu';
        sidemenuHiddenButtonWrapper.className = 'open_sidemenu';
        sidemenuHiddenButton.href = '#';
        sidemenuHiddenButton.title = sideMenu.arialabel;
        sidemenuHiddenButton.setAttribute('aria-label', sideMenu.arialabel);
        sidemenuHiddenButtonWrapper.insertBefore(sidemenuHiddenButton, sidemenuHiddenButtonWrapper.firstChild);
        document.body.insertBefore(sidemenuHiddenButtonWrapper, document.body.firstChild);
        document.body.insertBefore(sidemenuCover, document.body.firstChild);
        window.addEventListener('hashchange', function() {
    		if ('open_sidemenu' === window.location.hash.substr(1).split('-',1)[0]) {
    			if (typeof window.location.hash.substr(1).split('-')[1] !== 'undefined') {
    				document.getElementById('hidden_open_sidemenu').classList = 'open_sidemenu ' + window.location.hash.substr(1).substring(window.location.hash.substr(1).indexOf('-') + 1);
    			} else {
    				document.getElementById('hidden_open_sidemenu').classList = 'open_sidemenu';
    			}
    			document.querySelectorAll('#hidden_open_sidemenu>a')[0].click();
    			history.replaceState(null, null, ' ');
    		}
        }, false);
        if ('dashicon' in sideMenu) {
            Array.prototype.forEach.call(sidemenuToggles, function(sidemenuToggle) {
                switch(sideMenu.dashiconlocation) {
                    case 'before':
                        sidemenuToggle.innerHTML = '<span alt="Menu" class="dashicons dashicons-dashicons dashicons-' + sideMenu.dashicon + '"></span> ' + sidemenuToggle.innerHTML;
                        break;
                    case 'after':
                        sidemenuToggle.innerHTML = sidemenuToggle.innerHTML + ' <span alt="Menu" class="dashicons dashicons-dashicons dashicons-' + sideMenu.dashicon + '"></span>';
                        break;
                    default:
                        sidemenuToggle.innerHTML = '<span alt="Menu" class="dashicons dashicons-dashicons dashicons-' + sideMenu.dashicon + '"></span>';
                }
                sidemenuToggle.title = sideMenu.arialabel;
                sidemenuToggle.setAttribute('aria-label', sideMenu.arialabel);
            });
        }
        var current_scroll = (document.documentElement.scrollTop),
        closeSideMenu = function() {
            Array.prototype.forEach.call(document.querySelectorAll('.open_sidemenu>a, .open_sidemenu>div>a, .sidemenu .close_sidemenu'), function(sidemenuToggle) {
                sidemenuToggle.classList.remove('open');
            });
            document.body.classList.remove('sidemenu_open');
        },
        scrollClose = function() {
            var activeElement = document.activeElement;
            if ('INPUT' != activeElement.tagName.toUpperCase() && document.body.classList.contains('sidemenu_open') && Math.abs(window.pageYOffset - current_scroll) > 400) {
                closeSideMenu();
            }
        },
        coverClick = function() {
            if (document.body.classList.contains('sidemenu_open')) {
                closeSideMenu();
            }
        },
        buttonWrapper;
        if (!('scrollclose' in sideMenu) || ('scrollclose' in sideMenu && sideMenu.scrollclose == '1')) {
            window.addEventListener('scroll', scrollClose);
        }
        if (!('contentclickclose' in sideMenu) || ('contentclickclose' in sideMenu && sideMenu.contentclickclose == '1')) {
            document.querySelectorAll('body>.cover')[0].addEventListener('click', coverClick, false);
        }
        if (!('hidetooltips' in sideMenu) && !sidemenuToggles.length && document.body.classList.contains('logged-in') && ('openbuttonhelp' in sideMenu)) {
            var sidemenuHelp = document.createElement('section');
            sidemenuHelp.innerHTML = sideMenu.openbuttonhelp;
            document.getElementsByClassName('close_sidemenu')[0].parentNode.insertBefore(sidemenuHelp, document.getElementsByClassName('close_sidemenu')[0].nextSibling);
            document.body.classList.add('sidemenu_open');
            current_scroll = (document.documentElement.scrollTop);
        }
        Array.prototype.forEach.call(document.querySelectorAll('a.open_sidemenu, .open_sidemenu>a, .open_sidemenu>div>a, .sidemenu .close_sidemenu'), function(sidemenuToggle) {
            sidemenuToggle.addEventListener('click', function(buttonEvent) {
                buttonEvent.preventDefault();
                if (buttonEvent.target.classList[0] === 'open_sidemenu' || buttonEvent.target.classList[1] === 'open_sidemenu') {
                    buttonWrapper = buttonEvent.target;
                } else {
                    buttonWrapper = buttonEvent.target.parentElement;
                }
                if (buttonWrapper.classList[0] !== 'close_sidemenu' && buttonWrapper.classList[0] !== 'open_sidemenu' && buttonWrapper.classList[1] !== 'open_sidemenu') {
                    buttonWrapper = buttonWrapper.parentElement;
                }
                if (
                    buttonWrapper.classList[0] &&
                    'close_sidemenu' !== buttonWrapper.classList[0] &&
                    'wp-block-buttons' !== buttonWrapper.classList[0] &&
                    (
                        (
                            buttonWrapper.classList[1] && 
                            buttonWrapper.classList[1] !== 'menu-item' &&
                            'wp-block-sidemenu-sidemenu' !== buttonWrapper.classList[0]
                        ) || (
                            buttonWrapper.classList[2] &&
                            'wp-block-sidemenu-sidemenu' === buttonWrapper.classList[0]
                        )
                    )
                ) {
                    var showClassContent = buttonWrapper.classList[1];
                    if ('wp-block-sidemenu-sidemenu' === buttonWrapper.classList[0] && 'open_sidemenu' === buttonWrapper.classList[1] && buttonWrapper.classList[2]) {
                        showClassContent = buttonWrapper.classList[2];
                    }
                    Array.prototype.forEach.call(document.getElementsByClassName('sidemenu')[0].getElementsByClassName('menu-item'), function(sidemenuItem) {
                        sidemenuItem.style.display = 'none';
                    });
                    Array.prototype.forEach.call(document.getElementsByClassName('sidemenu')[0].getElementsByClassName('widget'), function(sidemenuItem) {
                        if (sidemenuItem.getElementsByClassName(showClassContent)[0]) {
                            sidemenuItem.style.display = 'block';
                        } else {
                            sidemenuItem.style.display = 'none';
                        }
                    });
                    Array.prototype.forEach.call(document.getElementsByClassName('sidemenu')[0].getElementsByClassName(showClassContent), function(sidemenuItem) {
                        sidemenuItem.style.display = 'block';
                    });
                } else if (buttonWrapper.classList[0] !== 'close_sidemenu') {
                    Array.prototype.forEach.call(document.getElementsByClassName('sidemenu')[0].getElementsByClassName('menu-item'), function(sidemenuItem) {
                        sidemenuItem.style.display = 'block';
                    });
                    Array.prototype.forEach.call(document.getElementsByClassName('sidemenu')[0].getElementsByClassName('widget'), function(sidemenuItem) {
                        sidemenuItem.style.display = 'block';
                    });
                }
                if (this.classList.contains('open') || (!sidemenuToggles.length && document.body.classList.contains('logged-in'))) {
                    Array.prototype.forEach.call(document.querySelectorAll('.open_sidemenu>a, .open_sidemenu>div>a, .sidemenu .close_sidemenu'), function(sidemenuToggle) {
                        sidemenuToggle.classList.remove('open');
                    });
                    document.body.classList.remove('sidemenu_open');
                } else {
                    Array.prototype.forEach.call(document.querySelectorAll('.open_sidemenu>a, .open_sidemenu>div>a, .sidemenu .close_sidemenu'), function(sidemenuToggle) {
                        sidemenuToggle.classList.add('open');
                    });
                    document.body.classList.add('sidemenu_open');
                    current_scroll = (document.documentElement.scrollTop);
                }
            }, false);
        });
        Array.prototype.forEach.call(document.querySelectorAll('.sidemenu section a'), function(sidemenuLink) {
            sidemenuLink.addEventListener('click',function() {
    			document.querySelectorAll('#hidden_open_sidemenu>a')[0].click();
    		});
        });
        if (('removetoggle' in sideMenu) && sideMenu.removetoggle !== '') {
            Array.prototype.forEach.call(document.querySelectorAll(sideMenu.removetoggle), function(removeToggle) {
                removeToggle.remove();
            });
        }
        if (('hijacktoggle' in sideMenu) && sideMenu.hijacktoggle !== '') {
            Array.prototype.forEach.call(document.querySelectorAll(sideMenu.hijacktoggle), function(hijackToggle) {
                if (('removeattribute' in sideMenu) && sideMenu.removeattribute !== '') {
                    hijackToggle.removeAttribute(sideMenu.removeattribute);
                }
                if (('reconstructtoggle' in sideMenu) && sideMenu.reconstructtoggle !== '') {
                    var toggleclone = hijackToggle.cloneNode(true);
                    hijackToggle.replaceWith(toggleclone);
                    hijackToggle = toggleclone;
                }
                hijackToggle.addEventListener('click',function() {
    				document.querySelectorAll('#hidden_open_sidemenu>a')[0].click();
    			});
            });
            var sidemenuHijackToggle = function() {
                setTimeout(function() {
                    if (typeof jQuery !== 'undefined') {
                        jQuery('.sidemenu li').each(function() { jQuery(this).unbind(); });
                        jQuery(sideMenu.hijacktoggle).unbind();
                    }
                    if (typeof astraNavMenuToggle !== 'undefined') {
                        Array.prototype.forEach.call(document.querySelectorAll(sideMenu.hijacktoggle), function(hijackToggle) {
    						hijackToggle.removeEventListener('click', astraNavMenuToggle, !1);
                        });
                    } else {
                        Array.prototype.forEach.call(document.querySelectorAll(sideMenu.hijacktoggle), function(hijackToggle) {
    						hijackToggle.onclick = '';
                        });
                    }
                }, 500);
            };
            window.addEventListener('resize', sidemenuHijackToggle);
            sidemenuHijackToggle();
        }
        if ('dropdown' in sideMenu) {
    		Array.prototype.forEach.call(document.querySelectorAll('.sidemenu .submenu-toggle'), function(submenuToggle) {
                if ('dropdowncount' in sideMenu) {
                    var countChild = document.createElement('span');
                    countChild.innerHTML = ' (' + submenuToggle.nextElementSibling.getElementsByClassName('menu-item').length + ')';
                    submenuToggle.parentNode.insertBefore(countChild, submenuToggle);
                }
    			submenuToggle.addEventListener('click', function(event) {
    			    event.preventDefault();
    				var sibling = submenuToggle.nextElementSibling;
    				while (sibling) {
    					if (sibling.matches('.sub-menu')) {
    						if (!sibling.classList.contains('open')) {
    							submenuToggle.classList.add('open');
    							sibling.classList.add('open');
    						} else {
    							submenuToggle.classList.remove('open');
    							sibling.classList.remove('open');
                                sibling.querySelectorAll('.submenu-toggle, .sub-menu').forEach(function(n) {
                                    n.classList.remove('open');
                                });
    						}
    						break;
    					}
    					sibling = sibling.nextElementSibling;   
    				}
    			});
    		});
    	}
    }, 5);
};
if (
    document.readyState === 'complete' ||
    (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
    sidemenuJS();
} else {
    document.addEventListener('DOMContentLoaded', sidemenuJS);
}
