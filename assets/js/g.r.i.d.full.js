/* **************************************************
 * G.R.I.D : g.r.i.d.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

// ------------------------------------------------------------
// G.R.I.D jQuery GLOBAL VARIABLE
// Used to handle G.R.I.D jQuery Plugins.
// ------------------------------------------------------------
jQuery.grid = {
    detach: function(element, plugin) {
        var $element = jQuery(element);
        if (typeof plugin === 'object') {
            for (var p in plugin) {
                $element[p]('detach');
                $element.removeData('grid.'+p);
            }
        }
        else if (typeof plugin === 'string') {
            $element[plugin]('detach');
            $element.removeData('grid.'+plugin);
        }
    }
};

// ------------------------------------------------------------
// G.R.I.D GLOBAL VARIABLE
// Used to define breakpoints, check viewport's size and some
// device and browser parameters (touchscreen, CSS transitions)
// ------------------------------------------------------------
var GRID = function () {
    "use strict";

	// Check if the device has a mouse.
	// First we'll assume the device has no mouse until we
	// detect any mouse movement
	var deviceHasMouse = false;
	$('body').one('mousemove', function() {
		deviceHasMouse = true;
	});

    // Compatibility for devices running Windows Phone 8
    // with IE 10.
    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
        var msViewportStyle = document.createElement('style');
        msViewportStyle.appendChild(
            document.createTextNode(
                '@-ms-viewport{width:auto!important}'
            )
        );
        document.querySelector('head').appendChild(msViewportStyle)
    }

    var g = {

        breakPoints: {
            mobile: 480,
            tablet_narrow: 768,
            tablet_wide: 1024
        },

        onBreakPointChangeCallbacks: {
            mobile: [],
            tablet_narrow: [],
            tablet_wide: []
        },

        init : function (options) {
            var defaults = {
                breakPoints: {
                    mobile: g.breakPoints.mobile,
                    tablet_narrow: g.breakPoints.tablet_narrow,
                    tablet_wide: g.breakPoints.tablet_wide
                }
            };

            g.params = $.extend({}, defaults, options);

            g.breakPoints = g.params.breakPoints;

            var $window   = $(window),
                $body     = $(document.body);

            $body.addClass('grid-is-loading');

            $window.on('load.grid', function() {
                $body.removeClass('grid-is-loading');
            });

            $window.on('resize.grid', function() {
                var $html = $('html');
                var lp = $html.data('grid.lastBreakPoint');
                var cp = (function() {
                    var ret;
                    for (var point in g.params.breakPoints) {
                        if (g.params.breakPoints.hasOwnProperty(point)) {
                            if (g.is(point)) {
                                ret = point;
                                break;
                            }
                        }
                    }
                    return ret;
                })();

                if (lp !== cp) {
                    $html.data('grid.lastBreakPoint', cp);
                    g.onBreakPointChangeCallbacks[cp].forEach(function (cb) {
                        cb();
                    });
                }
            });
        },

        onBreakPointChange: function(screen, callback) {
            g.onBreakPointChangeCallbacks[screen].push(callback);
        },

        is: function(screen) {
            return ($(document.body).outerWidth() <= g.params.breakPoints[screen]);
        },

        isTouchScreen: function() {
            return !!('ontouchstart' in window);
        },

        isPureTouchScreen: function() {
            return (!deviceHasMouse && g.isTouchScreen());
        },

        browserSupportsTransitions: function() {
            var b = document.body || document.documentElement,
                s = b.style,
                p = 'transition',
                v = ['Moz', 'Webkit', 'Khtml', 'O', 'ms'];

            if(typeof s[p] === 'string') { return true; }

            p = p.charAt(0).toUpperCase() + p.substr(1);
            for(var i = 0, l = v.length; i < l; i++) {
                if(typeof s[v[i] + p] === 'string') { return true; }
            }
            return false;
        }

    };

    return g;

}();
/* **************************************************
 * G.R.I.D : affix.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * **************************************************
 * Inspired by Affix 3.3.5
 * Developed by the Bootstrap Team
 * Licensed under MIT https://github.com/twbs/bootstrap/blob/master/LICENSE
 * *************************************************/


+function($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Affix = function(element, options) {
        this.options = options;
        this.$element = $(element);
        this.affixed = null;
        this.unpin = null;
        this.pinnedOffset = null;

        this.$target = $(this.options.target)
            .on('scroll.grid.affix', $.proxy(this.checkPosition, this))
            .on('click.grid.affix', $.proxy(this.checkPositionWithEventLoop, this));

        this.checkPosition();
    };

    Affix.VERSION = '2.1.0';

    Affix.RESET = 'affix affix-top affix-bottom';

    Affix.DEFAULTS = {
        offset: 0,
        target: window,
        top: 0,
        bottom: 0
    };

    Affix.prototype.detach = function () {
        this.$element.removeClass(Affix.RESET);
        this.$target
            .off('scroll.grid.affix')
            .off('click.grid.affix');
    };

    Affix.prototype.getState = function(scrollHeight, height, offsetTop, offsetBottom) {
        var scrollTop = this.$target.scrollTop();
        var position = this.$element.offset();
        var targetHeight = this.$target.height();

        if (offsetTop !== null && this.affixed === 'top') return scrollTop < offsetTop ? 'top' : false;

        if (this.affixed === 'bottom') {
            if (offsetTop !== null) return (scrollTop + this.unpin <= position.top) ? false : 'bottom';
            return (scrollTop + targetHeight <= scrollHeight - offsetBottom) ? false : 'bottom';
        }

        var initializing = this.affixed === null;
        var collideTop = initializing ? scrollTop : position.top;
        var collideHeight = initializing ? targetHeight : height;

        if (offsetTop !== null && scrollTop <= offsetTop) return 'top';
        if (offsetBottom !== null && (collideTop + collideHeight >= scrollHeight - offsetBottom)) return 'bottom';

        return false;
    };

    Affix.prototype.getPinnedOffset = function() {
        if (this.pinnedOffset) return this.pinnedOffset;
        this.$element.removeClass(Affix.RESET).addClass('affix');
        var scrollTop = this.$target.scrollTop();
        var position = this.$element.offset();
        return (this.pinnedOffset = position.top - scrollTop);
    };

    Affix.prototype.checkPositionWithEventLoop = function() {
        setTimeout($.proxy(this.checkPosition, this), 1);
    };

    Affix.prototype.checkPosition = function() {
        if (!this.$element.is(':visible')) return;

        var height = this.$element.outerHeight();
        var offset = this.options.offset;
        var offsetTop = offset.top;
        var offsetBottom = offset.bottom;
        var scrollHeight = Math.max($(document).outerHeight(), $(document.body).outerHeight());

        if (typeof offset !== 'object') offsetBottom = offsetTop = offset;
        if (typeof offsetTop === 'function') offsetTop = offset.top(this.$element);
        if (typeof offsetBottom === 'function') offsetBottom = offset.bottom(this.$element);

        var affix = this.getState(scrollHeight, height, offsetTop, offsetBottom);

        if (this.affixed !== affix) {
            if (this.unpin !== null) this.$element.css('top', '');

            var affixType = 'affix' + (affix ? '-' + affix : '');
            var e = $.Event(affixType + '.grid.affix');

            this.$element.trigger(e);

            if (e.isDefaultPrevented()) return;

            this.affixed = affix;
            this.unpin = affix === 'bottom' ? this.getPinnedOffset() : null;

            this.$element
                .removeClass(Affix.RESET)
                .addClass(affixType)
                .trigger(affixType.replace('affix', 'affixed') + '.grid.affix');
        }

        if (affix === 'bottom') {
            this.$element.offset({
                top: scrollHeight - height - offsetBottom
            });
        }
    };

    Affix.prototype.setTop = function(top) {
        if (!(typeof top === 'undefined')) {
            this.options.offset.top = bottom;
        }
    };

    Affix.prototype.setBottom = function(bottom) {
        if (!(typeof bottom === 'undefined')) {
            this.options.offset.bottom = bottom;
        }
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.affix');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Affix.DEFAULTS, options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.affix', (data = new Affix(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.affix || $.fn.affix;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.affix             = Plugin;
    $.grid.affix.Constructor = Affix;

    // Affix jQuery Extension
    // ------------------------------------------------------------
    $.fn.affix               = Plugin;
    $.fn.affix.Constructor   = Affix;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.affix.noConflict = function() {
        $.grid.affix = old;
        return this
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : alert.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Alert = function Alert(element, options) {
        this.$wrapper = $(element);
        this.options  = options;
        this.$text    = null;
        this.$alert   = null;
        this.$close   = null;

        this.options.text && this.show();
    };

    Alert.VERSION  = '2.1.0';

    Alert.DEFAULTS = {
        element: undefined,
        type: "info",
        text: null,
        speed: 500,
        animation: "fade",
        intensity: "20px",
        cleanBefore: true,
        position: "before",
        withClose: false,
        hideAfter: false,
        timeOut: 5000,
        onOpen: null,
        onHide: null
    };

    Alert.prototype._init = function() {
        this.$text  = $('<span class="grid-alert-text"></span>').html(this.options.text);
        this.$alert = $('<div class="grid-alert"></div>').addClass('grid-alert-' + this.options.type).append(this.$text);
    };

    Alert.prototype.setType = function(type) {
        this.options.type = type || this.options.type;
        return this;
    };

    Alert.prototype.setText = function(text) {
        this.options.text = text || this.options.text;
        return this;
    };

    Alert.prototype.setSpeed = function(speed) {
        this.options.speed = speed || this.options.speed;
        return this;
    };

    Alert.prototype.setAnimation = function(anim) {
        this.options.animation = anim || this.options.animation;
        return this;
    };

    Alert.prototype.setIntensity = function(intensity) {
        this.options.intensity = intensity || this.options.intensity;
        return this;
    };

    Alert.prototype.setCleanBefore = function(clean) {
        this.options.cleanBefore = clean || this.options.cleanBefore;
        return this;
    };

    Alert.prototype.setPosition = function(position) {
        this.options.position = position || this.options.position;
        return this;
    };

    Alert.prototype.setWithClose = function(close) {
        this.options.withClose = close || this.options.withClose;
        return this;
    };

    Alert.prototype.setHideAfter = function(hide) {
        this.options.hideAfter = hide || this.options.hideAfter;
        return this;
    };

    Alert.prototype.setTimeOut = function(timeOut) {
        this.options.timeOut = timeOut || this.options.timeOut;
        return this;
    };

    Alert.prototype.onOpen = function(callback) {
        this.options.onOpen = callback || this.options.onOpen;
        return this;
    };

    Alert.prototype.onHide = function(callback) {
        this.options.onHide = callback || this.options.onHide;
        return this;
    };

    Alert.prototype.close = function() {
        this.$wrapper.fadeOut(this.options.speed, this.options.onHide);
        return this;
    };

    Alert.prototype.shake = function(anim) {
        var that = this;
        var n = {}, r = {}, p = {};
        var side = '';

        anim = anim || this.options.animation;

        if (anim == "shake-horizontal") {
            side = 'left';
        }
        else if (anim == "shake-vertical") {
            side = 'top';
        }

        n[side] = "-" + this.options.intensity;
        p[side] = this.options.intensity;
        r[side] = 0;

        this.$wrapper.animate(n,
            this.options.speed,
            function() {
                that.$wrapper.animate(p,
                    that.options.speed,
                    function() {
                        that.$wrapper.animate(n,
                            that.options.speed,
                            function() {
                                that.$wrapper.animate(p,
                                    that.options.speed,
                                    function() {
                                        that.$wrapper.animate(r,
                                            that.options.speed,
                                            function() {
                                                that.options.onFinish && that.options.onFinish();
                                            }
                                        );
                                    }
                                );
                            }
                        );
                    }
                );
            }
        );

        return this;
    };

    Alert.prototype.show  = function() {
        var hide_alert;

        this._init();

        if (this.options.withClose) {
            (this.$alert.find('.grid-alert-close').length > 0) || (this.$close = $('<span class="grid-alert-close"></span>').prependTo(this.$alert));
            this.$close.on('click.grid.alert', $.proxy(this.close, this));
        }

        if (this.options.type === "loading") {
            (this.$alert.find('.grid-alert-loader').length > 0) || ($('<span class="grid-alert-loader"></span>').prependTo(this.$alert));
        }

        this.$wrapper.fadeOut('fast', (function(that) {
            return function() {
                if (that.options.cleanBefore === true) {
                    that.$wrapper.empty();
                }

                if (that.options.position === "before") {
                    that.$wrapper.prepend(that.$alert);
                }
                else if (that.options.position === "after") {
                    that.$wrapper.append(that.$alert);
                }

                if (that.options.animation === "fade") {
                    that.$wrapper.fadeIn( that.options.speed, that.options.onOpen );
                }
                else if (that.options.animation === "shake-horizontal" || that.options.animation === "shake-vertical") {
                    that.$wrapper.fadeIn('fast', function() {
                        that.shake();
                        that.options.onOpen && that.options.onOpen();
                    });
                }

                if (that.options.hideAfter === true) {
                    clearTimeout(hide_alert);
                    hide_alert = setTimeout(function() {
                        that.close();
                    }, that.options.timeOut);
                }
            }
        })(this));

        return this;
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.alert');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Alert.DEFAULTS, options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.alert', (data = new Alert(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.alert || $.fn.alert;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.alert             = Plugin;
    $.grid.alert.Constructor = Alert;

    // Alert jQuery Extension
    // ------------------------------------------------------------
    $.fn.alert               = Plugin;
    $.fn.alert.Constructor   = Alert;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.alert.noConflict  = function() {
        $.grid.alert = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : carousel.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Carousel = function Carousel(element, options) {
        this.$element    = $(element);
        this.options     = options;
        this.$wrapper    = $('<div class="grid-carousel-wrapper"></div>');
        this.$forward    = $('<span class="grid-carousel-forward"></span>');
        this.$backward   = $('<span class="grid-carousel-backward"></span>');
        this.$reel       = this.$element.children('.grid-carousel-reel');
        this.$items      = this.$reel.children('.grid-carousel-item');
        this.pos         = 0;
        this.leftLimit   = 0;
        this.rightLimit  = 0;
        this.itemWidth   = 0;
        this.reelWidth   = 0;
        this.timerId     = 0;
        this.onTriggered = false;
        this.currentItem = 0;

        this.options.controlHover || this.$element.addClass('grid-carousel-controls-not-hover');

        this._init();
    };

    Carousel.VERSION  = '2.1.0';

    Carousel.DEFAULTS = {
        speed: 4,
        fadeIn: true,
        fadeDelay: 250,
        controlHover: true,
        slideOn: 'mouseover',
        pushItem: 1
    };

    Carousel.prototype.setSpeed = function(speed) {
        this.options.speed = speed || this.options.speed;
        this._init();
        return this;
    };

    Carousel.prototype.setFadeIn = function(fadeIn) {
        this.options.fadeIn = fadeIn || this.options.fadeIn;
        this._init();
        return this;
    };

    Carousel.prototype.setFadeDelay = function(fadeDelay) {
        this.options.fadeDelay = fadeDelay || this.options.fadeDelay;
        this._init();
        return this;
    };

    Carousel.prototype.setControlHover = function(controlHover) {
        this.options.controlHover = controlHover || this.options.controlHover;
        this._init();
        return this;
    };

    Carousel.prototype.setSlideOn = function(slideOn) {
        this.options.slideOn = slideOn || this.options.slideOn;
        this._init();
        return this;
    };

    Carousel.prototype._init = function() {
        var that = this;

        this.$forward
            .appendTo(this.$element)
            .hide();

        this.$backward
            .appendTo(this.$element)
            .hide();

        this.$reel.wrap(this.$wrapper);

        if (this.options.fadeIn) {
            this.$items.addClass('grid-carousel-items-is-loading');
            $(window).on('scroll.grid.carousel', $.proxy(this._checkVisible, this)).trigger('scroll.grid.carousel');
        }

        if (this.options.slideOn === 'mouseover') {
            this.$forward
                .on('mouseenter.grid.carousel', $.proxy(this.slideRight, this))
                .on('mouseleave.grid.carousel', function() {
                    window.clearInterval(that.timerId);
                });

            this.$backward
                .on('mouseenter.grid.carousel', $.proxy(this.slideLeft, this))
                .on('mouseleave.grid.carousel', function() {
                    window.clearInterval(that.timerId);
                });
        }
        else if (this.options.slideOn === 'click') {
            this.$forward
                .on('click.grid.carousel', $.proxy(this.pushRight, this));

            this.$backward
                .on('click.grid.carousel', $.proxy(this.pushLeft, this));
        }

        this._initEvents();
    };

    Carousel.prototype._initEvents = function () {
        var that = this;

        this.reelWidth = this.$reel[0].scrollWidth;
        this._onChange();
        this._update();

        $(window).on('resize.grid.carousel', function () {
            that.reelWidth = that.$reel[0].scrollWidth;
            that._onChange();
            that._update();
        });
    };

    Carousel.prototype._update = function () {
        this.rightLimit = (- 1 * this.reelWidth) + this.$element.width();
        this.leftLimit = 0;
        this._updatePos(0);
    };


    Carousel.prototype._updatePos = function (pos) {
        this.pos = (typeof pos === 'undefined') ? this.pos : pos;
        this.$reel.animate({'left': this.pos}, this.options.speed);
    };


    Carousel.prototype._onChange = function () {
        if (GRID.isPureTouchScreen()) {
            this.$reel
                .css('overflow-y', 'hidden')
                .css('overflow-x', 'scroll')
                .scrollLeft(0);
            this.$forward.hide();
            this.$backward.hide();
        }
        else {
            this.$reel
                .css('overflow', 'visible')
                .scrollLeft(0);
            this.$forward.show();
            this.$backward.show();
        }
    };

    Carousel.prototype._checkVisible = function() {
        var that         = this;
        var offsetTop    = $(window).scrollTop() - this.$element.offset().top + $(window).height() - 50;
        var offsetBottom = offsetTop - ($(window).height() + this.$element.outerHeight()) + 50;

        if ((offsetTop > 0 && offsetTop < $(window).height()) || (offsetBottom < 0 && offsetBottom > -$(window).height())) {
            if (!this.onTriggered) {
                var timerId,
                    limit = this.$items.length - Math.ceil(this.$element.outerWidth(!0) / this.itemWidth);

                timerId = setInterval(function () {
                    var x  = that.$items.filter('.grid-carousel-items-is-loading'),
                        xf = x.first();
                    if (x.length <= limit) {
                        window.clearInterval(timerId);
                        that.$items.removeClass('grid-carousel-items-is-loading');
                        return ;
                    }
                    xf.removeClass('grid-carousel-items-is-loading');
                }, that.options.fadeDelay);
                this.onTriggered  = true;
            }
        }
    };

    Carousel.prototype.slideLeft = function () {
        var that = this;

        this.timerId = window.setInterval(function() {
            that.pos += that.options.speed;

            if (that.pos >= that.leftLimit) {
                window.clearInterval(that.timerId);
                that.pos = that.leftLimit;
            }

            that._updatePos();
        }, 10);

        return this;
    };

    Carousel.prototype.slideRight = function () {
        var that = this;

        this.timerId  = window.setInterval(function() {
            that.pos -= that.options.speed;

            if (that.pos <= that.rightLimit) {
                window.clearInterval(that.timerId);
                that.pos  = that.rightLimit;
            }

            that._updatePos();
        }, 10);

        return this;
    };

    Carousel.prototype.pushLeft = function () {
        var itemWidth = this.$items.eq(this.currentItem).outerWidth(!0);
        this.pos += (itemWidth + 3) * this.options.pushItem;

        if (this.pos >= this.leftLimit) {
            this.pos  = this.leftLimit;
        }
        else {
            this.currentItem += this.options.pushItem;
        }

        this._updatePos();

        return this;
    };


    Carousel.prototype.pushRight = function () {
        var itemWidth = this.$items.eq(this.currentItem).outerWidth(!0);
        this.pos -= (itemWidth + 3) * this.options.pushItem;

        if (this.pos <= this.rightLimit) {
            this.pos  = this.rightLimit;
        }
        else {
            this.currentItem -= this.options.pushItem;
        }

        this._updatePos();

        return this;
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.carousel');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Carousel.DEFAULTS, options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.carousel', (data = new Carousel(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.carousel || $.fn.carousel;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.carousel             = Plugin;
    $.grid.carousel.Constructor = Carousel;

    // Carousel jQuery Extension
    // ------------------------------------------------------------
    $.fn.carousel               = Plugin;
    $.fn.carousel.Constructor   = Carousel;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.carousel.noConflict  = function() {
        $.grid.carousel = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : dropdown.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Dropdown = function Dropdown(element, options) {
        this.$nav        = $(element);
        this.options     = options;
        this.$menu       = this.$nav.children('ul');
        this.$items      = this.$menu.children('li');
        this.$dropdown   = [];
        this.$subdrop    = [];
        this.hideTimeout = null;
        this.showTimeout = null;
        this._init();
    };

    Dropdown.VERSION = '2.1.0';

    Dropdown.DEFAULTS = {
        animation: 'slide',
        speed: 500,
        offset: 0,
        timeOut: 0,
        position: 'bottom',
        alignment: 'center',
        showOn: 'mouseover'
    };

    Dropdown.prototype._init = function(element, parent, level) {
        var that = this;

        this.$items.each(function(index) {
            var $this = $(this);
            that.$dropdown[index] = $this.children('ul')
                .hide(0)
                .clone()
                .appendTo(document.body)
                .addClass('grid-dropdown grid-dropdown-level-0 grid-dropdown-position-'+that.options.position+' grid-dropdown-alignment-'+that.options.alignment)
                .hide(0);

            $(window).on('scroll.grid.dropdown.'+index, function() {
                that.$dropdown[index].css({
                    'position': 'absolute'
                }).offset(that._calculateOffset(index));
            });

            var event;
            if (that.options.showOn === 'click') {
                event = 'click.grid.dropdown';
            } else {
                event = 'mouseenter.grid.dropdown';
            }

            $this.on(event, function () {
                that.showTimeout = setTimeout(function() {
                    that.$dropdown[index].css({
                        'position': 'absolute'
                    }).offset(that._calculateOffset(index));
                    that.show(that.$dropdown[index]);
                    $(window).trigger('scroll.grid.dropdown.' + index);
                }, that.options.timeOut);
            });

            $this.on('mouseleave.grid.dropdown', function() {
                that.hideTimeout = setTimeout(function() {
                    that.hide(that.$dropdown[index]);
                    $(that.$subdrop[index]).each(function(idx) {
                       that.hide(that.$subdrop[index][idx]);
                    });
                }, 250);
            });

            that.$dropdown[index].on('mouseenter.grid.dropdown', function() {
                clearTimeout(that.hideTimeout);
                that.showTimeout = setTimeout(function() {
                    that.show(that.$dropdown[index]);
                }, that.options.timeOut);
            });

            that.$dropdown[index].on('mouseleave.grid.dropdown', function() {
                that.hideTimeout = setTimeout(function() {
                    that.hide(that.$dropdown[index]);
                    $(that.$subdrop[index]).each(function(idx) {
                       that.hide(that.$subdrop[index][idx]);
                    });
                }, 250);
            });

            that.$dropdown[index].children('li').each(function(idx) {
                var $this = $(this);

                if (!that.$subdrop[index]) { that.$subdrop[index] = []; }
                that.$subdrop[index][idx] = $this.children('ul')
                    .hide(0)
                    .clone()
                    .appendTo(document.body)
                    .addClass('grid-dropdown grid-dropdown-level-1')
                    .hide(0);

                $(window).on('scroll.grid.dropdown.'+index+'.'+idx, function() {
                    that.$subdrop[index][idx].css({
                        'position': 'absolute'
                    }).offset(that._calculateSubOffset(index, idx));
                });

                $this.on(event, function() {
                    that.showTimeout = setTimeout(function() {
                        that.$subdrop[index][idx].css({
                            'position': 'absolute'
                        }).offset(that._calculateSubOffset(index, idx));
                        $(that.$subdrop[index]).each(function() {
                            that.hide($(this));
                        });
                        that.show(that.$subdrop[index][idx]);
                        $(window).trigger('scroll.grid.dropdown.'+index+'.'+idx);
                    }, that.options.timeOut);
                });

                that.$subdrop[index][idx].on('mouseenter.grid.dropdown', function() {
                    that.$dropdown[index].trigger('mouseenter.grid.dropdown');
                    $this.trigger('mouseenter.grid.dropdown');
                    clearTimeout(that.hideTimeout);
                    that.show(that.$subdrop[index][idx]);
                });

                that.$subdrop[index][idx].on('mouseleave.grid.dropdown', function() {
                    that.hideTimeout = setTimeout(function() {
                        that.hide(that.$dropdown[index]);
                        that.hide(that.$subdrop[index][idx]);
                    }, 250);
                });
            });
        });
    };

    Dropdown.prototype._calculateOffset = function(index) {
        var aligntop       = this.$items.eq(index).offset().top;
        var alignleft      = 0;
        var dropdownwidth  = 0;
        var dropdownheight = 0;
        var liwidth        = 0;
        var liheight       = 0;
        var offset         = 0;

        if (this.options.position === 'bottom') {
            aligntop += this.$items.eq(index).outerHeight(!0) + parseInt(this.$dropdown[index].css('margin-top')) + this.options.offset;
            if (this.options.alignment === 'left') {
                alignleft =  this.$items.eq(index).offset().left;
            }
            if (this.options.alignment === 'center') {
                dropdownwidth = this.$dropdown[index].outerWidth();
                liwidth       = this.$items.eq(index).outerWidth();
                offset        = (liwidth - dropdownwidth) / 2;
                alignleft     = this.$items.eq(index).offset().left + offset;
            }
            if (this.options.alignment === 'right') {
                dropdownwidth = this.$dropdown[index].outerWidth();
                liwidth       = this.$items.eq(index).outerWidth();
                offset        = liwidth - dropdownwidth;
                alignleft     = this.$items.eq(index).offset().left + offset;
            }
        }

        if (this.options.position === 'left') {
            alignleft = this.$items.eq(index).offset().left - parseInt(this.$dropdown[index].css('margin-right')) - this.$dropdown[index].outerWidth() - this.options.offset;
            aligntop  = this.$items.eq(index).offset().top;
            if (this.options.alignment === 'top') {
                aligntop += parseInt(this.$dropdown[index].css('margin-top'));
            }
            if (this.options.alignment === 'center') {
                dropdownheight = this.$dropdown[index].outerHeight(!0);
                liheight       = this.$items.eq(index).outerHeight(!0);
                offset         = (liheight - dropdownheight) / 2;
                aligntop      += offset;
            }
            if (this.options.alignment === 'bottom') {
                dropdownheight = this.$dropdown[index].outerHeight(!0);
                liheight       = this.$items.eq(index).outerHeight(!0);
                offset         = (liheight - dropdownheight);
                aligntop      += offset;
            }
        }

        if (this.options.position === 'top') {
            aligntop -= this.$menu.outerHeight(!0) + parseInt(this.$dropdown[index].css('margin-bottom')) + this.options.offset;
            if (this.options.alignment === 'left') {
                alignleft =  this.$items.eq(index).offset().left;
            }
            if (this.options.alignment === 'center') {
                dropdownwidth = this.$dropdown[index].outerWidth();
                liwidth       = this.$items.eq(index).outerWidth();
                offset        = (liwidth - dropdownwidth) / 2;
                alignleft     = this.$items.eq(index).offset().left + offset;
            }
            if (this.options.alignment === 'right') {
                dropdownwidth = this.$dropdown[index].outerWidth();
                liwidth       = this.$items.eq(index).outerWidth();
                offset        = liwidth - dropdownwidth;
                alignleft     = this.$items.eq(index).offset().left + offset;
            }
        }

        if (this.options.position === 'right') {
            alignleft = this.$items.eq(index).offset().left + this.$items.eq(index).outerWidth() + parseInt(this.$dropdown[index].css('margin-left')) + this.options.offset;
            aligntop  = this.$items.eq(index).offset().top;
            if (this.options.alignment === 'top') {
                aligntop += parseInt(this.$dropdown[index].css('margin-top'));
            }
            if (this.options.alignment === 'center') {
                dropdownheight = this.$dropdown[index].outerHeight(!0);
                liheight       = this.$items.eq(index).outerHeight(!0);
                offset         = (liheight - dropdownheight) / 2;
                aligntop      += offset;
            }
            if (this.options.alignment === 'bottom') {
                dropdownheight = this.$dropdown[index].outerHeight(!0);
                liheight       = this.$items.eq(index).outerHeight(!0);
                offset         = (liheight - dropdownheight);
                aligntop      += offset;
            }
        }

        return {
            top: aligntop,
            left: alignleft
        };
    };

    Dropdown.prototype._calculateSubOffset = function(index, idx) {
        var aligntop  = this.$dropdown[index].children('li').eq(idx).offset().top + parseInt(this.$subdrop[index][idx].css('margin-top'));
        var alignleft  = 0;
        var liwidth    = this.$dropdown[index].outerWidth();
        var ulwidth    = this.$subdrop[index][idx].outerWidth();
        var canGoRight = ((this.$dropdown[index].offset().left + liwidth + ulwidth) < $(window).width());
        var canGoLeft  = (this.$dropdown[index].offset().left > ulwidth);
        var canGoTop   = (this.$dropdown[index].offset().top > this.$subdrop[index][idx].outerHeight());

        if (canGoRight) {
            alignleft = this.$dropdown[index].offset().left + liwidth + parseInt(this.$subdrop[index][idx].css('margin-left')) + this.options.offset;
            this.$subdrop[index][idx].addClass('grid-dropdown-position-right');
        }
        else if (canGoLeft) {
            alignleft = this.$dropdown[index].offset().left - ulwidth - parseInt(this.$subdrop[index][idx].css('margin-right')) - this.options.offset;
            this.$subdrop[index][idx].addClass('grid-dropdown-position-left');
        }
        else {
            alignleft = this.$dropdown[index].offset().left;
            if (this.options.position === 'top' && canGoTop) {
                aligntop = this.$dropdown[index].offset().top - this.$dropdown[index].outerHeight(!0) - parseInt(this.$subdrop[index][idx].css('margin-bottom')) - this.options.offset;
                this.$subdrop[index][idx].addClass('grid-dropdown-position-top');
            }
            else {
                aligntop = this.$dropdown[index].offset().top + this.$dropdown[index].outerHeight(!0) + parseInt(this.$subdrop[index][idx].css('margin-top')) + this.options.offset;
                this.$subdrop[index][idx].addClass('grid-dropdown-position-bottom');
            }
        }

        return {
            top: aligntop,
            left: alignleft
        };
    };

    Dropdown.prototype.show = function(el) {
        $(el).stop();
        switch (this.options.animation) {
            case 'fade':
                $(el).fadeIn(this.options.speed);
                break;

            case 'slide':
                $(el).slideDown(this.options.speed);
                break;

            case 'none':
            default:
                $(el).show(0);
                break;
        }
    };

    Dropdown.prototype.hide = function(el) {
        switch (this.options.animation) {
            case 'fade':
                $(el).fadeOut(this.options.speed);
                break;

            case 'slide':
                $(el).slideUp(this.options.speed);
                break;

            case 'none':
            default:
                $(el).hide(0);
                break;
        }
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.dropdown');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Dropdown.DEFAULTS, options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.dropdown', (data = new Dropdown(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.dropdown || $.fn.dropdown;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.dropdown             = Plugin;
    $.grid.dropdown.Constructor = Dropdown;

    // Dropdown jQuery Extension
    // ------------------------------------------------------------
    $.fn.dropdown               = Plugin;
    $.fn.dropdown.Constructor   = Dropdown;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.dropdown.noConflict  = function() {
        $.grid.dropdown = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : carousel.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var MenuToggle = function MenuToggle(element, options) {
        var that = this;

        this.options  = options;

        this.$nav     = $(element);
        this.$menu    = this.$nav.children('ul');
        this.$submenu = this.$nav.children('ul').children('li').children('ul');
        this.$items   = this.$menu.children('li');

        var bodyWidth = $(document.body).outerWidth();

        this.menuIsCreated = false;

        $(window).on('resize.grid.menuToggle', function () {
            that.toggleMenu($(document.body).outerWidth());
        });

        this.toggleMenu(bodyWidth);
    };

    MenuToggle.VERSION = '2.1.0';

    MenuToggle.DEFAULTS = {
        breakPoint: GRID.breakPoints.mobile,
        wrapper: "body",
        animation: "slide-left",
        closeText: false,
        menuOpenerClass: "grid-phone-menu-toggle-button",
        closeOnScroll: false
    };

    MenuToggle.prototype._init = function () {
        this.body = document.body;
        this.wrapper = document.querySelector(this.options.wrapper);
        this.mask = document.querySelector('#grid-phone-menu-mask');
        this.menu = document.querySelector('#grid-phone-menu--' + this.options.animation);
        this.closeBtn = this.menu.querySelector('.grid-phone-menu__close');
        this.menuOpeners = document.querySelectorAll('.'+this.options.menuOpenerClass);
        this.wrapper.classList.add('grid-phone-menu-wrapper');
        this._initEvents();
    };

    MenuToggle.prototype._initEvents = function () {
        $(this.closeBtn).on('click.grid.menutoggle', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));

        $(this.mask).on('click.grid.menutoggle', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));

        if (this.options.closeOnScroll === true) {
            $(window).on('scroll.grid.menutoggle', function (e) {
                e.preventDefault();
                this.close();
            }.bind(this));
        }
    };

    MenuToggle.prototype.open = function () {
        this.body.classList.add('grid-phone-menu-page-has-active-menu');
        this.wrapper.classList.add('grid-phone-menu-page-has-' + this.options.animation);
        this.menu.classList.add('is-active');
        this.mask.classList.add('is-active');
        this.disableMenuOpeners();
    };

    MenuToggle.prototype.close = function () {
        this.body.classList.remove('grid-phone-menu-page-has-active-menu');
        this.wrapper.classList.remove('grid-phone-menu-page-has-' + this.options.animation);
        this.menu.classList.remove('is-active');
        this.mask.classList.remove('is-active');
        this.enableMenuOpeners();
    };

    MenuToggle.prototype.disableMenuOpeners = function () {
        $(this.menuOpeners).each(function () {
            this.disabled = true;
        });
    };

    MenuToggle.prototype.enableMenuOpeners = function () {
        $(this.menuOpeners).each(function () {
            this.disabled = false;
        });
    };

    MenuToggle.prototype.create_menu = function () {
        if (!this.menuIsCreated) {
            var $mask = $('<div id="grid-phone-menu-mask"></div>'),
                $butt = $('<div id="grid-phone-menu-button--' + this.options.animation + '" class="' + this.options.menuOpenerClass + '"><span></span></div>'),
                $pnav = $('<nav id="grid-phone-menu--' + this.options.animation + '" class="' + this.options.animation + ' grid-phone-menu"></nav>'),
                $cmenu = $('<ul class="alt-menu"></ul>');

            if (this.closeText) {
                var $close = $('<li class="menu-item grid-phone-menu__close"><a href="javascript:;">' + this.options.closeText + '</a></li>');
                $cmenu.prepend($close);
            }

            this.$items.each(function () {
                var $i = $(this);
                $i.addClass('menu-item level-0').clone().appendTo($cmenu).find('ul').remove();
                var $a = $i.children('ul').children('li');
                $a.each(function () {
                    $(this).addClass('menu-item level-1').clone().remove('ul').appendTo($cmenu).find('ul').remove();
                    var $b = $(this).children('ul').children('li');
                    $b.each(function () {
                        $(this).addClass('menu-item level-2').clone().remove('ul').appendTo($cmenu).find('ul').remove();
                    });
                });
            });

            $pnav.prepend($cmenu);
            $(this.options.wrapper).prepend($mask);
            $(this.options.wrapper).prepend($pnav);
            $(document.body).prepend($butt);

            this.menuIsCreated = true;

            this.init_menu();
        }
    };

    MenuToggle.prototype.remove_menu = function () {
        if (this.menuIsCreated) {
            $('div#grid-phone-menu-button--' + this.options.animation).remove();
            $('nav#grid-phone-menu--' + this.options.animation).remove();
            $('div#grid-phone-menu-mask').remove();
            this.menuIsCreated = false;
        }
    };

    MenuToggle.prototype.init_menu = function () {
        var that = this;
        this._init();
        $('div#grid-phone-menu-button--' + this.options.animation).on('click.grid.menutoggle', function (e) {
            e.preventDefault();
            that.open();
        });
    };

    MenuToggle.prototype.toggleMenu = function (minWidth) {
        if (minWidth <= this.options.breakPoint) {
            this.create_menu();
            this.$nav.css('display', 'none');
        }
        else {
            this.remove_menu();
            this.$nav.css('display', 'block');
        }
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.menuToggle');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, MenuToggle.DEFAULTS, options);
            var $wrapper = $(params.menu || this);

            return $wrapper.each(function () {
                $(this).data('grid.menuToggle', (new MenuToggle(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : null);
        }
    }

    var old = $.grid.menuToggle || $.fn.menuToggle;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.menuToggle             = Plugin;
    $.grid.menuToggle.Constructor = MenuToggle;

    // MenuToggle jQuery Extension
    // ------------------------------------------------------------
    $.fn.menuToggle               = Plugin;
    $.fn.menuToggle.Constructor   = MenuToggle;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.menuToggle.noConflict  = function() {
        $.grid.menuToggle = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : panels.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Panels = function Panels(element, options) {
        var that = this;

        this.$element = $(element);
        this.options  = options;
        this.$parent  = this.$element.parent('.grid-panels-wrapper');
        this.$panels  = this.$element.find('.grid-panels-item');
        this.$buttons = this.$parent.find('.grid-panels-switcher');

        this.currpanel = '#' + this.$panels.eq(0).attr('id');
        this.prevpanel = null;

        this._init();
    };

    Panels.VERSION = '2.1.0';

    Panels.DEFAULTS = {
        element: ".grid-panels",
        speed: 500
    };

    Panels.prototype._init = function() {
        var that = this;

        this.$panels.each(function(index) {
            if (index > 0) {
                $(this).hide();
            }
        });

        this.$buttons.each(function() {
            var $a = $(this);
            $a.on('click.grid.panels', function(e) {
                if ($a.hasClass('disable-switch')) {
                    return false;
                }
                else {
                    if (that.currpanel != $a.attr('href')) {
                        that.prevpanel = that.currpanel;
                        that.currpanel = $a.attr('href');
                        that.update_panels();
                    }
                }
                e.preventDefault();
                e.stopPropagation();
            });
        });
    };

    Panels.prototype.update_panels = function() {
        var that = this;

        this.$buttons.removeClass('active');
        this.$buttons.filter('[href="' + this.currpanel + '"]').addClass('active');

        $(this.prevpanel).fadeOut(this.options.speed, (function (that) {
            return function() {
                that.$element.animate({
                    height: $(that.currpanel).outerHeight()
                }, that.options.speed, 'swing', function() {
                    $(that.currpanel).fadeIn(that.options.speed);
                });
            }
        })(this));
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin(options) {
        var params   = $.extend({}, Panels.DEFAULTS, typeof options === 'object' && options);
        if ($('body').find(params.element || this).length < 1) {
            return;
        }

        return $(params.element || this).each(function () {
            $(this).data('grid.panels', (new Panels(this, params)));
        });
    }

    var old = $.grid.panels || $.fn.panels;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.panels             = Plugin;
    $.grid.panels.Constructor = Panels;

    // Panels jQuery Extension
    // ------------------------------------------------------------
    $.fn.panels               = Plugin;
    $.fn.panels.Constructor   = Panels;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.panels.noConflict  = function() {
        $.grid.panels = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : popup.js v1.0.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {

    var Popup = function (element, options) {
        this.$element = $('<div/>')
            .addClass('grid-popup')
            .append($(element).addClass('grid-popup-modal'))
            .appendTo(document.body);

        this.$bg = $('<div/>').addClass('grid-popup-bg').appendTo(document.body);

        this.options = options;

        this._init();
    };

    Popup.VERSION = '1.0.0';

    Popup.DEFAULTS = {
        withClose: true,
        closeWithEsc: true,
        animationOpen: 'fadeIn',
        animationClose: 'fadeOut',
        opener: '',
        onClose: null,
        onOpen: null
    };

    Popup.prototype._init = function () {
        this.$bg
            .addClass('animated grid-popup-closed');

        this.$element
            .addClass('animated grid-popup-closed');

        var that = this;

        $(this.options.opener).each(function () {
            $(this).on('click.grid.popup.open', function (e) {
                e.preventDefault();
                e.stopPropagation();
                that.open();
            });
        });

        if (this.options.withClose) {
            $('<span/>')
                .addClass('grid-popup-closer')
                .prependTo(this.$element)
                .on('click.grid.popup.close', function () {
                    that.close();
                });
        }

        if (this.options.closeWithEsc) {
            $(window).on('keydown.grid.popup.close', function (e) {
                if (e.keyCode === 27) {
                    that.close();
                }
            });
        }

        this.$bg.on('click.grid.popup.close', function () {
            that.close();
        });
    };

    Popup.prototype.open = function () {
        var that = this;

        if (this.options.closeWithEsc) {
            $(window).on('keydown.grid.popup.close', function (e) {
                if (e.keyCode === 27) {
                    that.close();
                }
            });
        }

        this.$bg
            .removeClass('grid-popup-closed fadeOut')
            .addClass('grid-popup-opened fadeIn');

        this.$element
            .removeClass('grid-popup-closed ' + this.options.animationClose)
            .addClass('grid-popup-opened ' + this.options.animationOpen);

        if ($.isFunction(this.options.onOpen)) {
            this.options.onOpen();
        }
    };

    Popup.prototype.close = function () {
        if (this.options.closeWithEsc) {
            $(window).off('keydown.grid.popup.close');
        }

        this.$bg
            .removeClass('grid-popup-opened fadeIn')
            .addClass('fadeOut');

        this.$element
            .removeClass('grid-popup-opened ' + this.options.animationOpen)
            .addClass(this.options.animationClose);

        if ($.isFunction(this.options.onClose)) {
            this.options.onClose();
        }

        setTimeout(function () {
            this.$bg.addClass('grid-popup-closed');
            this.$element.addClass('grid-popup-closed');
        }.bind(this), 1000);
    };

    Popup.prototype.onOpen = function(callback) {
        this.options.onOpen = callback || this.options.onOpen;
        return this;
    };

    Popup.prototype.onClose = function(callback) {
        this.options.onClose = callback || this.options.onClose;
        return this;
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.popup');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Popup.DEFAULTS, options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.popup', (data = new Popup(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.popup || $.fn.popup;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.popup             = Plugin;
    $.grid.popup.Constructor = Popup;

    // Popup jQuery Extension
    // ------------------------------------------------------------
    $.fn.popup               = Plugin;
    $.fn.popup.Constructor   = Popup;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.popup.noConflict = function() {
        $.grid.popup = old;
        return this
    };

} (jQuery);
/* **************************************************
 * G.R.I.D : progress.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Progress = function Progress(element, options) {
        var that = this;

        this.$element = $(element);
        this.options  = options;
        this.finish   = false;
        this.interval = null;

        this.setDelay();

        this.animate();
    };

    Progress.VERSION = '2.1.0';

    Progress.DEFAULTS = {
        element: '.grid-progress',
        delay: 1500,
        width: null,
        color: null,
        onEmpty: null,
        onFull: null,
        onFinish: null
    };

    Progress.prototype.setDelay = function(delay) {
        delay = delay || this.options.delay;

        if (delay < 1500) {
            delay = 1500;
        }

        this.options.delay = delay;

        return this;
    };

    Progress.prototype.setColor = function(color) {
        this.options.color = color || this.options.color;
        return this;
    };

    Progress.prototype.setWidth = function(width) {
        this.options.width = width || this.options.width;
        return this;
    };

    Progress.prototype.animate = function() {
        this.finish = false;

        if (this.options.color) {
            this.$element.css({
                "background-color": this.options.color,
            });
        }
        if (this.options.width) {
            this.$element.css({
                width: this.options.width
            });
        }

        this.finish = true;

        this.setInterval();

        return this;
    };

    Progress.prototype.setInterval = function(delay) {
        var that = this;

        this.setDelay(delay);

        this.interval = setInterval(function() {
            if (that.finish) {
                that.callback();
            }
        }, this.options.delay);

        return this;
    };

    Progress.prototype.onFull = function(callback) {
        this.options.onFull = callback || this.options.onFull;
        return this;
    };

    Progress.prototype.onEmpty = function(callback) {
        this.options.onEmpty = callback || this.options.onEmpty;
        return this;
    };

    Progress.prototype.onFinish = function(callback) {
        this.options.onFinish = callback || this.options.onFinish;
        return this;
    };

    Progress.prototype.callback = function() {
        var f = this.$element.parent('.grid-progress-bar').width();
        var b = this.$element.width();

        if (this.options.onEmpty && b === 0) {
            this.options.onEmpty();
        }

        if (this.options.onFull) {
            if (f === b) {
                this.options.onFull();
            }
        }

        if (this.options.onFinish && this.finish) {
            this.options.onFinish();
        }

        clearInterval(this.interval);
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.progress');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, Progress.DEFAULTS, typeof options == 'object' && options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.progress', (data = new Progress(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.progress || $.fn.progress;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.progress             = Plugin;
    $.grid.progress.Constructor = Progress;

    // Progress jQuery Extension
    // ------------------------------------------------------------
    $.fn.progress               = Plugin;
    $.fn.progress.Constructor   = Progress;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.progress.noConflict  = function() {
        $.grid.progress = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : scrollto.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var ScrollTo = function ScrollTo(element, options) {
        this.$element = $(element);
        this.options  = options;

        this._init();
    };

    ScrollTo.VERSION = '2.1.0';

    ScrollTo.DEFAULTS = {
        element: '.grid-scrollto',
        anchor: "top",
        axis: 'y',
        offset: 0,
        speed: 500,
        before: null,
        after: null
    };

    ScrollTo.prototype._init = function() {
        var that = this;
        this.$element.on('click.grid.scrollTo', function (e) {
            var $this = $(this);
            e.preventDefault();
            e.stopPropagation();
            that.options.before($this);
            switch (that.options.axis) {
                case 'xy':
                    $('body,html').animate(that._calculateOffset($this.attr('href')), that.options.speed, 'swing', (function (callback, $this) {
                        return function () {
                            callback($this);
                        };
                    })(that.options.after, $this));
                break;

                case 'x':
                    $('body,html').animate({ scrollLeft: that._calculateOffset($this.attr('href')).scrollLeft }, that.options.speed, 'swing', (function (callback, $this) {
                        return function () {
                            callback($this);
                        };
                    })(that.options.after, $this));
                break;

                case 'y':
                default:
                    $('body,html').animate({ scrollTop: that._calculateOffset($this.attr('href')).scrollTop }, that.options.speed, 'swing', (function (callback, $this) {
                        return function () {
                            callback($this);
                        };
                    })(that.options.after, $this));
                break;
            }
        });
    };

    ScrollTo.prototype._calculateOffset = function(element) {
        var $element  = $(element);
        var aligntop  = $element.offset().top;
        var alignleft = $element.offset().left;

        if (this.options.anchor === 'middle') {
            aligntop -= ($(window).height() - $element.outerHeight()) / 2;
        }

        if (this.options.anchor === 'bottom') {
            aligntop -= $(window).height() - $element.outerHeight();
        }

        if (typeof this.options.offset === 'object') {
            aligntop  += this.options.offset.top;
            alignleft += this.options.offset.left;
        }
        else {
            aligntop  += this.options.offset;
            alignleft += this.options.offset;
        }

        return {
            scrollTop:  aligntop,
            scrollLeft: alignleft
        };

    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin(options) {
        var params   = $.extend({}, ScrollTo.DEFAULTS, typeof options === 'object' && options);
        if ($('body').find(params.element || this).length < 1) {
            return;
        }

        return $(params.element || this).each(function () {
            $(this).data('grid.scrollTo', (new ScrollTo(this, params)));
        });
    }

    var old = $.grid.scrollTo || $.fn.scrollTo;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.scrollTo             = Plugin;
    $.grid.scrollTo.Constructor = ScrollTo;

    // ScrollTo jQuery Extension
    // ------------------------------------------------------------
    $.fn.scrollTo               = Plugin;
    $.fn.scrollTo.Constructor   = ScrollTo;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.scrollTo.noConflict  = function() {
        $.grid.scrollTo = old;
        return this;
    };

} (jQuery);
/* **************************************************
 * G.R.I.D : scrollwatch.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var ScrollWatch = function ScrollWatch(element, options) {
        this.$element     = $(element);
        this.options      = options;
        this.onTriggered  = false;
        this.offTriggered = false;
        this.watching     = false;

        this._init();
    };

    ScrollWatch.VERSION = '2.1.0';

    ScrollWatch.DEFAULTS = {
        element: '.grid-scrollwatch',
        delay: 0,
        offset: 10,
        anchor: 'top',
        on: null,
        off: null
    };

    ScrollWatch.prototype._init = function() {
        var that = this;
        $(window).on('scroll.grid.scrollwatch', function() {
            that._trigger();
        }).trigger('scroll.grid.scrollwatch');
    };

    ScrollWatch.prototype._trigger = function() {
        var that = this;
        var offsetTop = 0;
        var offsetBottom = 0;

        offsetTop    = $(window).scrollTop() - this.$element.offset().top + $(window).height() - this.options.offset;
        offsetBottom = offsetTop - ($(window).height() + this.$element.outerHeight()) + this.options.offset;

        if ((offsetTop > 0 && offsetTop < $(window).height()) || (offsetBottom < 0 && offsetBottom > -$(window).height())) {
            this.watching = true;
            this.offTriggered = false;
            if (!this.onTriggered) {
                this.options.on && setTimeout(function() { that.options.on(that.$element) }, this.options.delay);
                this.onTriggered  = true;
            }
        }

        switch (this.options.anchor) {
            default:
            case 'top':
                if (offsetBottom >= 0) {
                    this.watching = false;
                    this.onTriggered = false;
                    if (!this.offTriggered) {
                        this.options.off && setTimeout(function() { that.options.off(that.$element) }, this.options.delay);
                        this.offTriggered = true;
                    }
                }
            break;

            case 'bottom':
                if (offsetTop <= 0) {
                    this.watching = false;
                    this.onTriggered = false;
                    if (!this.offTriggered) {
                        this.options.off && setTimeout(function() { that.options.off(that.$element) }, this.options.delay);
                        this.offTriggered = true;
                    }
                }
            break;
        }
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin(options) {
        var params   = $.extend({}, ScrollWatch.DEFAULTS, typeof options === 'object' && options);
        if ($('body').find(params.element || this).length < 1) {
            return;
        }

        return $(params.element || this).each(function () {
            $(this).data('grid.scrollwatch', (new ScrollWatch(this, params)));
        });
    }

    var old = $.grid.scrollwatch || $.fn.scrollwatch;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.scrollwatch             = Plugin;
    $.grid.scrollwatch.Constructor = ScrollWatch;

    // ShakeIt jQuery Extension
    // ------------------------------------------------------------
    $.fn.scrollwatch               = Plugin;
    $.fn.scrollwatch.Constructor   = ScrollWatch;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.scrollwatch.noConflict  = function() {
        $.grid.scrollwatch = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : shakeit.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var ShakeIt = function ShakeIt(element, options) {
        var that = this;

        this.$element = $(element);
        this.options  = options;

        if (this.options.stopBefore) {
            this.$element
            .stop()
            .css({
                top: 0,
                left: 0
            });
        }

        this.shake();
    };

    ShakeIt.VERSION = '2.1.0';

    ShakeIt.DEFAULTS = {
        animation: "horizontal",
        speed: 50,
        intensity: 0,
        onFinish: null,
        stopBefore: true
    };

    ShakeIt.prototype.setAnimation = function(anim) {
        this.options.animation = anim || this.options.animation;
        return this;
    };

    ShakeIt.prototype.setSpeed = function(speed) {
        this.options.speed = speed || this.options.speed;
        return this;
    };

    ShakeIt.prototype.setIntensity = function(intensity) {
        this.options.intensity = intensity || this.options.intensity;
        return this;
    };

    ShakeIt.prototype.setStopBefore = function(stop) {
        this.options.stopBefore = stop || this.options.stopBefore;
        return this;
    };

    ShakeIt.prototype.onFinish = function(callback) {
        this.options.onFinish = callback || this.options.onFinish;
        return this;
    };

    ShakeIt.prototype.shake = function(anim) {
        var that = this;
        var n = {}, r = {}, p = {};
        var side = '';

        anim = anim || this.options.animation;

        if (anim === "horizontal") {
            side = 'left';
        }
        else if (anim === "vertical") {
            side = 'top';
        }

        n[side] = "-" + that.options.intensity;
        p[side] = that.options.intensity;
        r[side] = 0;

        that.$element.animate(n,
            that.options.speed,
            function() {
                that.$element.animate(p,
                    that.options.speed,
                    function() {
                        that.$element.animate(n,
                            that.options.speed,
                            function() {
                                that.$element.animate(p,
                                    that.options.speed,
                                    function() {
                                        that.$element.animate(r,
                                            that.options.speed,
                                            function() {
                                                that.options.onFinish && that.options.onFinish();
                                            }
                                        );
                                    }
                                );
                            }
                        );
                    }
                );
            }
        );

        return this;
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin() {
        var args = arguments;
        var data = $(this).data('grid.shakeIt');
        args[0]  = args[0] || {};

        if (typeof args[0] === 'object') {
            var options  = args[0];
            var params   = $.extend({}, ShakeIt.DEFAULTS, typeof options === 'object' && options);
            if ($('body').find(params.element || this).length < 1) {
                return;
            }

            return $(params.element || this).each(function () {
                $(this).data('grid.shakeIt', (new ShakeIt(this, params)));
            });
        }
        else if (typeof args[0] === 'string') {
            data && data[args[0]]((!(typeof args[1] === 'undefined')) ? args[1] : void 0);
        }
    }

    var old = $.grid.shakeIt || $.fn.shakeIt;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.shakeIt             = Plugin;
    $.grid.shakeIt.Constructor = ShakeIt;

    // ShakeIt jQuery Extension
    // ------------------------------------------------------------
    $.fn.shakeIt               = Plugin;
    $.fn.shakeIt.Constructor   = ShakeIt;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.shakeIt.noConflict  = function() {
        $.grid.shakeIt = old;
        return this;
    };

} (jQuery);

/* **************************************************
 * G.R.I.D : slider.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function ($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Slider = function Slider(element, options) {
        this.$element     = $(element);
        this.options      = options;
        this.$slider      = this.$element.find('ul');
        this.$slides      = this.$slider.children('li');
        this.slideCount   = this.$slides.length;
        this.$canvas      = null;
        this.prevIndex    = 0;
        this.currIndex    = 0;
        this.nextIndex    = 0;
        this.responsiveW  = 0;
        this.responsiveR  = 0;
        this.responsiveH  = 0;
        this.interval     = null;
        this.paused       = false;

        if (this.options.timeOut < 1000) {
            this.options.timeOut = 1000;
        }

        this.options.pauseHover && !GRID.isTouchScreen() && this.$element
            .on('mouseenter.grid.slider', $.proxy(this.pause, this))
            .on('mouseleave.grid.slider', $.proxy(this.resume, this));

        this.start();
    }

    Slider.VERSION  = '2.1.0';

    Slider.DEFAULTS = {
        width:       1500,
        height:      500,
        animSpeed:   500,
        animType:    'fade',
        timeOut:     10000,
        useCaptions: true,
        responsive:  true,
        pauseHover:  true
    };

    Slider.prototype.start = function() {
        var that = this;

        this.$slides.addClass('grid-slider-slide');

        if (this.options.responsive) {
            this.set_responsive();
        }
        else {
            this.set_static();
        }

        if (this.options.useCaptions) {
            this.create_captions();
        }

        if (this.options.animType == 'fade') {
            this.prepare_fade_anim();
        }
        else if (this.options.animType == 'push-left' ||
                 this.options.animType == 'push-right' ||
                 this.options.animType == 'push-down' ||
                 this.options.animType == 'push-up') {

            this.prepare_push_anim();
        }
        else if (this.options.animType == 'slide-left' ||
                 this.options.animType == 'slide-right' ||
                 this.options.animType == 'slide-down' ||
                 this.options.animType == 'slide-up') {

            this.prepare_slide_anim();
        }
        else if (this.options.animType == 'zoom-in' ||
                 this.options.animType == 'zoom-out') {

            this.prepare_zoom_anim();
        }
        else {
            this.prepare_none_anim();
        }

        this.reset_positions();

        this.create_links();

        this.reset_timer();
    };

    Slider.prototype.reset_timer = function() {
        this.pause();
        this.resume();
    };

    Slider.prototype.resume = function() {
        var that = this;

        this.interval = setInterval(function() {
            that.show_next();
        }, this.options.timeOut);

        this.paused = false;
    };

    Slider.prototype.pause = function() {
        this.interval = clearInterval(this.interval);

        this.paused = true;
    };

    Slider.prototype.reset_positions  = function() {
        this.$slider.css({
            top: 'unset',
            left: 'unset',
            right: 'unset',
            bottom: 'unset'
        });
        this.$slides.css({
            top: 'unset',
            left: 'unset',
            right: 'unset',
            bottom: 'unset'
        });
    };

    Slider.prototype.reset_dimensions = function(width, height) {
        this.$slides.css({
            'height'        : height,
            'width'         : width
        });
        this.$slides.find('img').css({
            'height'        : height,
            'width'         : width
        });
        this.$slider.css({
            'height'        : height,
            'width'         : width
        });
    };

    Slider.prototype.set_responsive = function() {
        var that = this;

        this.responsiveW = this.$element.outerWidth(),
        this.responsiveR = this.responsiveW / this.options.width,
        this.responsiveH = this.responsiveR * this.options.height;
        this.reset_dimensions('100%', this.responsiveH);
        if(this.responsiveW < this.options.width){
            this.reset_dimensions(this.responsiveW, this.responsiveH);
        }
        $(window).on('load.grid.slider', function(){
            that.responsiveW = that.$element.outerWidth(),
            that.responsiveR = that.responsiveW / that.options.width,
            that.responsiveH = that.responsiveR * that.options.height;
            that.reset_dimensions('100%', that.responsiveH);
            that.reset_dimensions(that.responsiveW, that.responsiveH);
            that.$canvas && that.$canvas.css({
                'width'     : that.responsiveW,
                'height'    : that.responsiveH,
                'overflow'  : 'hidden',
                'position'  : 'relative'
            });
            if (that.options.animType == 'push-left') {
                that.$slider.css({
                    'width'     : that.responsiveW * that.slideCount ,
                    'height'    : that.responsiveH,
                    'left'      : 0
                });
            }
            else if (that.options.animType == 'push-right') {
                that.$slider.css({
                    'width'     : that.responsiveW * that.slideCount,
                    'height'    : that.responsiveH,
                    'right'     : that.responsiveW * (that.slideCount - 1)
                });
            }
            else if (that.options.animType == 'push-up') {
                that.$slider.css({
                    'width'     : that.responsiveW,
                    'height'    : that.responsiveH * that.slideCount,
                    'top'       : 0
                });
            }
            else if (that.options.animType == 'push-down') {
                that.$slider.css({
                    'width'     : that.responsiveW,
                    'height'    : that.responsiveH * that.slideCount,
                    'bottom'    : that.responsiveH * (that.slideCount - 1)
                });
            }
        }).on('resize.grid.slider', function() {
            $(window).trigger('load.grid.slider');
        });
    };

    Slider.prototype.set_static = function() {
        this.reset_dimensions(this.options.width, this.options.height);
    };

    Slider.prototype.create_links = function() {
        this.$slides.find('img').each(function() {
            var $i   = $(this);
            var link = $i.attr('data-link');
            if (link) {
                $i.wrap('<a href="'+link+'"></a>');
            }
        });
    };

    Slider.prototype.create_captions = function() {
        this.$slides.find('img').each(function() {
            var $i      = $(this);
            var caption = $i.attr('alt');
            if (caption) {
                $i.parent().find('.grid-slider-caption').length || $i.after('<p class="grid-slider-caption">'+caption+'</p>');
            }
        });
    };

    Slider.prototype.init_go_next = function() {
        this.prevIndex = this.slideCount - 1;
        this.currIndex = 0;
        this.nextIndex = this.currIndex + 1;
    };

    Slider.prototype.init_go_previous = function() {
        this.prevIndex = this.slideCount - 2;
        this.currIndex = this.slideCount - 1;
        this.nextIndex = this.currIndex - 1;
    };

    Slider.prototype.prepare_fade_anim = function() {
        this.$slides.show(0);

        this.$slides.css({
            'display'       : 'block'
        });

        this.$slides.each(function(index) {
            var $s = $(this);
            if (index > 0) {
                $s.hide(0);
            }
        });

        this.init_go_next();
    };

    Slider.prototype.prepare_push_anim = function() {
        if (this.options.animType == 'push-left' || this.options.animType == 'push-up') {
            this.init_go_next();
        }
        else if (this.options.animType == 'push-right' || this.options.animType == 'push-down') {
            this.init_go_previous();
        }

        var wrapper  = this.$element.find('.grid-slider-wrapper');
        this.$canvas = (wrapper.length > 0) ? wrapper : $('<div></div>');
        this.$canvas.attr('class', 'grid-slider-'+this.options.animType+'-wrapper grid-slider-wrapper');

        if(this.options.responsive && (this.responsiveW < this.options.width)){
            if (this.options.animType == 'push-left') {
                this.$canvas.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.responsiveW * this.slideCount,
                    'height'    : this.responsiveH,
                    'left'      : 0
                });
            }
            else if (this.options.animType == 'push-right') {
                this.$canvas.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.responsiveW * this.slideCount,
                    'height'    : this.responsiveH,
                    'right'     : this.responsiveW * (this.slideCount - 1)
                });
            }
            else if (this.options.animType == 'push-up') {
                this.$canvas.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH * this.slideCount,
                    'top'       : 0
                });
            }
            else if (this.options.animType == 'push-down') {
                this.$canvas.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.responsiveW,
                    'height'    : this.responsiveH * this.slideCount,
                    'bottom'    : this.responsiveH * (this.slideCount - 1)
                });
            }
        }
        else {

            if (this.options.animType == 'push-left') {
                this.$canvas.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.options.width * this.slideCount,
                    'height'    : this.options.height,
                    'left'      : 0
                });
            }

            else if (this.options.animType == 'push-right') {
                this.$canvas.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.options.width * this.slideCount,
                    'height'    : this.options.height,
                    'right'     : this.options.width * (this.slideCount - 1)
                });
            }

            else if (this.options.animType == 'push-up') {
                this.$canvas.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height * this.slideCount,
                    'top'       : 0
                });
            }

            else if (this.options.animType == 'push-down') {
                this.$canvas.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height,
                    'overflow'  : 'hidden',
                    'position'  : 'relative'
                });

                this.$slider.css({
                    'width'     : this.options.width,
                    'height'    : this.options.height * this.slideCount,
                    'bottom'    : this.options.height * (this.slideCount - 1)
                });
            }
        }

        if (this.options.animType == 'push-left' || this.options.animType == 'push-up') {
            this.$slides.css({
                'float'         : 'left',
                'position'      : 'relative',
                'display'       : 'list-item'
            });
        }
        else if (this.options.animType == 'push-right' || this.options.animType == 'push-down') {
            this.$slides.css({
                'float'         : 'right',
                'position'      : 'relative',
                'display'       : 'list-item'
            });
        }

        this.$canvas.prependTo(this.$element);
        this.$slider.appendTo(this.$canvas);
    };

    Slider.prototype.prepare_slide_anim = function() {
        var that = this;

        if (this.options.animType == 'slide-left' || this.options.animType == 'slide-up') {
            this.$slides.css({
                'float'         : 'left',
                'position'      : 'absolute',
                'top'           : 0,
                'display'       : 'list-item'
            });
        }
        else if (this.options.animType == 'slide-right' || this.options.animType == 'slide-down') {
            this.$slides.css({
                'float'         : 'right',
                'position'      : 'absolute',
                'top'           : 0,
                'display'       : 'list-item'
            });
        }

        this.$slides.show(0);

        this.$slides.each(function(index) {
            var $s = $(this);
            $s.css('z-index', that.slideCount - index);
            if (index > 1) {
                $s.hide(0);
            }
        });

        this.init_go_next();
    };

    Slider.prototype.prepare_zoom_anim = function() {
        this.$slides.show(0);

        this.$slides.each(function(index) {
            var $s = $(this);
            if (index > 0) {
                $s.hide(0);
            }
        });

        this.init_go_next();
    };

    Slider.prototype.prepare_none_anim = function() {
        this.$slides.show(0);

        this.$slides.each(function(index) {
            var $s = $(this);
            if (index > 0) {
                $s.hide(0);
            }
        });

        this.init_go_next();
    };

    Slider.prototype.go_next = function() {
        if (this.nextIndex < this.slideCount - 1) {
            this.currIndex++;
            this.prevIndex = this.currIndex - 1;
        }
        else {
            this.currIndex = -1;
            this.prevIndex = this.slideCount - 1;
        }
        this.nextIndex = this.currIndex + 1;
    };

    Slider.prototype.go_previous = function() {
        if (this.nextIndex > 0) {
            this.currIndex--;
            this.prevIndex = this.currIndex - 1;
        }
        else {
            this.currIndex = this.slideCount;
            this.prevIndex = 0;
        }
        this.nextIndex = this.currIndex - 1;
    };

    Slider.prototype.show_next = function() {
        var that = this,
            slideH,
            slideW;
        if (that.options.responsive) {
            slideW = that.responsiveW;
            slideH = that.responsiveH;
        }
        else {
            slideW = that.options.width;
            slideH = that.options.height;
        }
        if (that.options.animType == 'fade') {
            that.$slides.eq(that.currIndex).fadeOut(that.options.animSpeed, function() {
                that.$slides.eq(that.nextIndex).fadeIn(that.options.animSpeed, function() {
                    that.go_next();
                });
            });
        }
        else if (that.options.animType == 'push-left') {
            that.$slider.animate({'left': -that.nextIndex * slideW }, that.options.animSpeed, function(){
                that.go_next();
            });
        }
        else if (that.options.animType == 'push-right') {
            that.$slider.animate({'right': that.nextIndex * slideW }, that.options.animSpeed, function(){
                that.go_previous();
            });
        }
        else if (that.options.animType == 'push-up') {
            that.$slider.animate({'top': -that.nextIndex * slideH }, that.options.animSpeed, function(){
                that.go_next();
            });
        }
        else if (that.options.animType == 'push-down') {
            that.$slider.animate({'bottom': that.nextIndex * slideH }, that.options.animSpeed, function(){
                that.go_previous();
            });
        }
        else if (that.options.animType == 'slide-left') {
            that.$slides.eq(that.nextIndex).css({'display': 'list-item'});
            that.$slides.eq(that.currIndex).animate({'left': -slideW }, that.options.animSpeed, function(){
                var z = that.$slides.eq(that.currIndex).css('z-index');
                that.$slides.eq(that.currIndex).css({'display': 'none', 'z-index': that.$slides.eq(that.nextIndex).css('z-index')});
                that.$slides.eq(that.nextIndex).css('z-index', z);
                that.$slides.eq(that.currIndex).animate({'left': 0 }, that.options.animSpeed);
                that.go_next();
            });
        }
        else if (that.options.animType == 'slide-right') {
            that.$slides.eq(that.nextIndex).css({'display': 'list-item'});
            that.$slides.eq(that.currIndex).animate({'right': -slideW }, that.options.animSpeed, function(){
                var z = that.$slides.eq(that.currIndex).css('z-index');
                that.$slides.eq(that.currIndex).css({'display': 'none', 'z-index': that.$slides.eq(that.nextIndex).css('z-index')});
                that.$slides.eq(that.nextIndex).css('z-index', z);
                that.$slides.eq(that.currIndex).animate({'right': 0 }, that.options.animSpeed);
                that.go_next();
            });
        }
        else if (that.options.animType == 'slide-up') {
            that.$slides.eq(that.nextIndex).css({'display': 'list-item'});
            that.$slides.eq(that.currIndex).animate({'top': -slideH }, that.options.animSpeed, function(){
                var z = that.$slides.eq(that.currIndex).css('z-index');
                that.$slides.eq(that.currIndex).css({'display': 'none', 'z-index': that.$slides.eq(that.nextIndex).css('z-index')});
                that.$slides.eq(that.nextIndex).css('z-index', z);
                that.$slides.eq(that.currIndex).animate({'top': 0 }, that.options.animSpeed);
                that.go_next();
            });
        }
        else if (that.options.animType == 'slide-down') {
            that.$slides.eq(that.nextIndex).css({'display': 'list-item'});
            that.$slides.eq(that.currIndex).animate({'bottom': -slideH }, that.options.animSpeed, function(){
                var z = that.$slides.eq(that.currIndex).css('z-index');
                that.$slides.eq(that.currIndex).css({'display': 'none', 'z-index': that.$slides.eq(that.nextIndex).css('z-index')});
                that.$slides.eq(that.nextIndex).css('z-index', z);
                that.$slides.eq(that.currIndex).animate({'bottom': 0 }, that.options.animSpeed);
                that.go_next();
            });
        }
        else if (that.options.animType == 'zoom-in') {
            that.$slides.eq(that.currIndex).find('img:first-child').animate({'height': 2 * slideH, 'width': 2 * slideW, 'top': '-50%', 'left': '-50%'}, that.options.animSpeed);
            that.$slides.eq(that.currIndex).fadeOut(that.options.animSpeed, function() {
                that.$slides.eq(that.nextIndex).fadeIn(that.options.animSpeed, function() {
                    that.$slides.eq(that.currIndex).find('img:first-child').animate({'height': slideH, 'width': slideW, 'top': 0, 'left': 0}, that.options.animSpeed);
                    that.go_next();
                });
            });
        }
        else if (that.options.animType == 'zoom-out') {
            that.$slides.eq(that.currIndex).find('img:first-child').animate({'height': 0, 'width': 0, 'top': '50%'}, that.options.animSpeed);
            that.$slides.eq(that.currIndex).fadeOut(that.options.animSpeed, function() {
                that.$slides.eq(that.nextIndex).fadeIn(that.options.animSpeed, function() {
                    that.$slides.eq(that.currIndex).find('img:first-child').animate({'height': slideH, 'width': slideW, 'top': 0}, that.options.animSpeed);
                    that.go_next();
                });
            });
        }
        else {
            that.$slides.eq(that.currIndex).css('display', 'none');
            that.$slides.eq(that.nextIndex).css('display', 'block');
            that.go_next();
        }
    };

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
    function Plugin(options) {
        var params   = $.extend({}, Slider.DEFAULTS, typeof options == 'object' && options);
        if ($('body').find(params.element || this).length < 1) {
            return;
        }

        return $(params.element || this).each(function () {
            $(this).data('grid.slider', (new Slider(this, params)));
        });
    }

    var old = $.grid.slider || $.fn.slider;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.slider             = Plugin;
    $.grid.slider.Constructor = Slider;

    // ShakeIt jQuery Extension
    // ------------------------------------------------------------
    $.fn.slider               = Plugin;
    $.fn.slider.Constructor   = Slider;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.slider.noConflict  = function() {
        $.grid.slider = old;
        return this;
    };

}(jQuery);
/* **************************************************
 * G.R.I.D : tooltips.js v2.1.0
 * **************************************************
 * Copyright 2015-2016 Centers Technologies
 * Licensed under MIT http://opensource.org/licenses/MIT
 * **************************************************
 * Ispired by Tooltipster 3.3.0 | 2014-11-08
 * Developed by Caleb Jacob
 * Licensed under MIT http://opensource.org/licenses/MIT
 * *************************************************/

+function($) {
    'use strict';

    // ------------------------------------------------------------
    // CLASS DEFINITION
    // ------------------------------------------------------------
    var Tooltip = function(element, options) {

		// list of instance variables

		this.bodyOverflowX;
		// stack of custom callbacks provided as parameters to API methods
		this.callbacks = {
			hide: [],
			show: []
		};
		this.checkInterval = null;
		// this will be the user content shown in the tooltip. A capital "C" is used because there is also a method called content()
		this.Content;
		// this is the original element which is being applied the tooltip plugin
		this.$el = $(element);
		// this will be the element which triggers the appearance of the tooltip on hover/click/custom events.
		// it will be the same as this.$el if icons are not used (see in the options), otherwise it will correspond to the created icon
		this.$elProxy;
		this.elProxyPosition;
		this.enabled = true;
		this.options = $.extend({}, Tooltip.DEFAULTS, options);
		this.mouseIsOverProxy = false;
		// a unique namespace per instance, for easy selective unbinding
		this.namespace = 'grid-tooltip-'+ Math.round(Math.random()*100000);
		// Status (capital S) can be either : appearing, shown, disappearing, hidden
		this.Status = 'hidden';
		this.timerHide = null;
		this.timerShow = null;
		// this will be the tooltip element (jQuery wrapped HTML element)
		this.$tooltip;

		// for backward compatibility
		this.options.iconTheme = this.options.iconTheme.replace('.', '');
		this.options.theme = this.options.theme.replace('.', '');

		// launch

		this._init();
	};

    Tooltip.VERSION = '2.1.0';

    Tooltip.DEFAULTS = {
        animation: 'fade',
        arrow: true,
        arrowColor: '',
        autoClose: true,
        content: null,
        contentAsHTML: false,
        contentCloning: true,
        debug: true,
        delay: 200,
        minWidth: 0,
        maxWidth: null,
        functionInit: function(origin, content) {},
        functionBefore: function(origin, continueTooltip) {
            continueTooltip();
        },
        functionReady: function(origin, tooltip) {},
        functionAfter: function(origin) {},
        hideOnClick: false,
        icon: '(?)',
        iconCloning: true,
        iconDesktop: false,
        iconTouch: false,
        iconTheme: 'grid-tooltip-icon',
        interactive: false,
        interactiveTolerance: 350,
        multiple: false,
        offsetX: 0,
        offsetY: 0,
        onlyOne: false,
        position: 'top',
        positionTracker: false,
        positionTrackerCallback: function(origin){
            // the default tracker callback will close the tooltip when the trigger is
            // 'hover' (see https://github.com/iamceege/tooltip/pull/253)
            if(this.option('trigger') == 'hover' && this.option('autoClose')) {
                this.hide();
            }
        },
        restoration: 'current',
        speed: 350,
        timer: 0,
        theme: 'grid-tooltip-default',
        touchDevices: true,
        trigger: 'hover',
        updateAnimation: true
    };

	Tooltip.prototype = {

		_init: function() {

			var self = this;

			// disable the plugin on old browsers (including IE7 and lower)
			if (document.querySelector) {

				// note : the content is null (empty) by default and can stay that way if the plugin remains initialized but not fed any content. The tooltip will just not appear.

				// let's save the initial value of the title attribute for later restoration if need be.
				var initialTitle = null;
				// it will already have been saved in case of multiple tooltips
				if (self.$el.data('grid-tooltip-initialTitle') === undefined) {

					initialTitle = self.$el.attr('title');

					// we do not want initialTitle to have the value "undefined" because of how jQuery's .data() method works
					if (initialTitle === undefined) initialTitle = null;

					self.$el.data('grid-tooltip-initialTitle', initialTitle);
				}

				// if content is provided in the options, its has precedence over the title attribute.
				// Note : an empty string is considered content, only 'null' represents the absence of content.
				// Also, an existing title="" attribute will result in an empty string content
				if (self.options.content !== null){
					self._content_set(self.options.content);
				}
				else {
					self._content_set(initialTitle);
				}

				var c = self.options.functionInit.call(self.$el, self.$el, self.Content);
				if(typeof c !== 'undefined') self._content_set(c);

				self.$el
					// strip the title off of the element to prevent the default tooltips from popping up
					.removeAttr('title')
					// to be able to find all instances on the page later (upon window events in particular)
					.addClass('tooltipstered');

				// detect if we're changing the tooltip origin to an icon
				// note about this condition : if the device has touch capability and self.options.iconTouch is false, you'll have no icons event though you may consider your device as a desktop if it also has a mouse. Not sure why someone would have this use case though.
				if ((!GRID.isTouchScreen() && self.options.iconDesktop) || (GRID.isTouchScreen() && self.options.iconTouch)) {

					// TODO : the tooltip should be automatically be given an absolute position to be near the origin. Otherwise, when the origin is floating or what, it's going to be nowhere near it and disturb the position flow of the page elements. It will imply that the icon also detects when its origin moves, to follow it : not trivial.
					// Until it's done, the icon feature does not really make sense since the user still has most of the work to do by himself

					// if the icon provided is in the form of a string
					if(typeof self.options.icon === 'string'){
						// wrap it in a span with the icon class
						self.$elProxy = $('<span class="'+ self.options.iconTheme +'"></span>');
						self.$elProxy.text(self.options.icon);
					}
					// if it is an object (sensible choice)
					else {
						// (deep) clone the object if iconCloning == true, to make sure every instance has its own proxy. We use the icon without wrapping, no need to. We do not give it a class either, as the user will undoubtedly style the object on his own and since our css properties may conflict with his own
						if (self.options.iconCloning) self.$elProxy = self.options.icon.clone(true);
						else self.$elProxy = self.options.icon;
					}

					self.$elProxy.insertAfter(self.$el);
				}
				else {
					self.$elProxy = self.$el;
				}

				// for 'click' and 'hover' triggers : bind on events to open the tooltip. Closing is now handled in _showNow() because of its bindings.
				// Notes about touch events :
					// - mouseenter, mouseleave and clicks happen even on pure touch devices because they are emulated. GRID.isPureTouchScreen() is a simple attempt to detect them.
					// - on hybrid devices, we do not prevent touch gesture from opening tooltips. It would be too complex to differentiate real mouse events from emulated ones.
					// - we check GRID.isPureTouchScreen() at each event rather than prior to binding because the situation may change during browsing
				if (self.options.trigger == 'hover') {

					// these binding are for mouse interaction only
					self.$elProxy
						.on('mouseenter.'+ self.namespace, function() {
							if (!GRID.isPureTouchScreen() || self.options.touchDevices) {
								self.mouseIsOverProxy = true;
								self._show();
							}
						})
						.on('mouseleave.'+ self.namespace, function() {
							if (!GRID.isPureTouchScreen() || self.options.touchDevices) {
								self.mouseIsOverProxy = false;
							}
						});

					// for touch interaction only
					if (GRID.isTouchScreen() && self.options.touchDevices) {

						// for touch devices, we immediately display the tooltip because we cannot rely on mouseleave to handle the delay
						self.$elProxy.on('touchstart.'+ self.namespace, function() {
							self._showNow();
						});
					}
				}
				else if (self.options.trigger == 'click') {

					// note : for touch devices, we do not bind on touchstart, we only rely on the emulated clicks (triggered by taps)
					self.$elProxy.on('click.'+ self.namespace, function() {
						if (!GRID.isPureTouchScreen() || self.options.touchDevices) {
							self._show();
						}
					});
				}
			}
		},

		// this function will schedule the opening of the tooltip after the delay, if there is one
		_show: function() {

			var self = this;

			if (self.Status != 'shown' && self.Status != 'appearing') {

				if (self.options.delay) {
					self.timerShow = setTimeout(function(){

						// for hover trigger, we check if the mouse is still over the proxy, otherwise we do not show anything
						if (self.options.trigger == 'click' || (self.options.trigger == 'hover' && self.mouseIsOverProxy)) {
							self._showNow();
						}
					}, self.options.delay);
				}
				else self._showNow();
			}
		},

		// this function will open the tooltip right away
		_showNow: function(callback) {

			var self = this;

			// call our constructor custom function before continuing
			self.options.functionBefore.call(self.$el, self.$el, function() {

				// continue only if the tooltip is enabled and has any content
				if (self.enabled && self.Content !== null) {

					// save the method callback and cancel hide method callbacks
					if (callback) self.callbacks.show.push(callback);
					self.callbacks.hide = [];

					//get rid of any appearance timer
					clearTimeout(self.timerShow);
					self.timerShow = null;
					clearTimeout(self.timerHide);
					self.timerHide = null;

					// if we only want one tooltip open at a time, close all auto-closing tooltips currently open and not already disappearing
					if (self.options.onlyOne) {
						$('.tooltipstered').not(self.$el).each(function(i,el) {

							var $el = $(el),
								nss = $el.data('grid.tooltip');

							// iterate on all tooltips of the element
							$.each(nss, function(i, ns){
								var instance = $el.data(ns),
									// we have to use the public methods here
									s = instance.status(),
									ac = instance.option('autoClose');

								if (s !== 'hidden' && s !== 'disappearing' && ac) {
									instance.hide();
								}
							});
						});
					}

					var finish = function() {
						self.Status = 'shown';

						// trigger any show method custom callbacks and reset them
						$.each(self.callbacks.show, function(i,c) { c.call(self.$el); });
						self.callbacks.show = [];
					};

					// if this origin already has its tooltip open
					if (self.Status !== 'hidden') {

						// the timer (if any) will start (or restart) right now
						var extraTime = 0;

						// if it was disappearing, cancel that
						if (self.Status === 'disappearing') {

							self.Status = 'appearing';

							if (GRID.browserSupportsTransitions()) {

								self.$tooltip
									.clearQueue()
									.removeClass('grid-tooltip-dying')
									.addClass('grid-tooltip-'+ self.options.animation +'-show');

								if (self.options.speed > 0) self.$tooltip.delay(self.options.speed);

								self.$tooltip.queue(finish);
							}
							else {
								// in case the tooltip was currently fading out, bring it back to life
								self.$tooltip
									.stop()
									.fadeIn(finish);
							}
						}
						// if the tooltip is already open, we still need to trigger the method custom callback
						else if(self.Status === 'shown') {
							finish();
						}
					}
					// if the tooltip isn't already open, open that sucker up!
					else {

						self.Status = 'appearing';

						// the timer (if any) will start when the tooltip has fully appeared after its transition
						var extraTime = self.options.speed;

						// disable horizontal scrollbar to keep overflowing tooltips from jacking with it and then restore it to its previous value
						self.bodyOverflowX = $('body').css('overflow-x');
						$('body').css('overflow-x', 'hidden');

						// get some other settings related to building the tooltip
						var animation = 'grid-tooltip-' + self.options.animation,
							animationSpeed = '-webkit-transition-duration: '+ self.options.speed +'ms; -webkit-animation-duration: '+ self.options.speed +'ms; -moz-transition-duration: '+ self.options.speed +'ms; -moz-animation-duration: '+ self.options.speed +'ms; -o-transition-duration: '+ self.options.speed +'ms; -o-animation-duration: '+ self.options.speed +'ms; -ms-transition-duration: '+ self.options.speed +'ms; -ms-animation-duration: '+ self.options.speed +'ms; transition-duration: '+ self.options.speed +'ms; animation-duration: '+ self.options.speed +'ms;',
							minWidth = self.options.minWidth ? 'min-width:'+ Math.round(self.options.minWidth) +'px;' : '',
							maxWidth = self.options.maxWidth ? 'max-width:'+ Math.round(self.options.maxWidth) +'px;' : '',
							pointerEvents = self.options.interactive ? 'pointer-events: auto;' : '';

						// build the base of our tooltip
						self.$tooltip = $('<div class="grid-tooltip-base '+ self.options.theme +'" style="'+ minWidth +' '+ maxWidth +' '+ pointerEvents +' '+ animationSpeed +'"><div class="grid-tooltip-content"></div></div>');

						// only add the animation class if the user has a browser that supports animations
						if (GRID.browserSupportsTransitions()) self.$tooltip.addClass(animation);

						// insert the content
						self._content_insert();

						// attach
						self.$tooltip.appendTo('body');

						// do all the crazy calculations and positioning
						self.reposition();

						// call our custom callback since the content of the tooltip is now part of the DOM
						self.options.functionReady.call(self.$el, self.$el, self.$tooltip);

						// animate in the tooltip
						if (GRID.browserSupportsTransitions()) {

							self.$tooltip.addClass(animation + '-show');

							if(self.options.speed > 0) self.$tooltip.delay(self.options.speed);

							self.$tooltip.queue(finish);
						}
						else {
							self.$tooltip.css('display', 'none').fadeIn(self.options.speed, finish);
						}

						// will check if our tooltip origin is removed while the tooltip is shown
						self._interval_set();

						// reposition on scroll (otherwise position:fixed element's tooltips will move away form their origin) and on resize (in case position can/has to be changed)
						$(window).on('scroll.'+ self.namespace +' resize.'+ self.namespace, function() {
							self.reposition();
						});

						// auto-close bindings
						if (self.options.autoClose) {

							// in case a listener is already bound for autoclosing (mouse or touch, hover or click), unbind it first
							$('body').off('.'+ self.namespace);

							// here we'll have to set different sets of bindings for both touch and mouse
							if (self.options.trigger == 'hover') {

								// if the user touches the body, hide
								if (GRID.isTouchScreen()) {
									// timeout 0 : explanation below in click section
									setTimeout(function() {
										// we don't want to bind on click here because the initial touchstart event has not yet triggered its click event, which is thus about to happen
										$('body').on('touchstart.'+ self.namespace, function() {
											self.hide();
										});
									}, 0);
								}

								// if we have to allow interaction
								if (self.options.interactive) {

									// touch events inside the tooltip must not close it
									if (GRID.isTouchScreen()) {
										self.$tooltip.on('touchstart.'+ self.namespace, function(event) {
											event.stopPropagation();
										});
									}

									// as for mouse interaction, we get rid of the tooltip only after the mouse has spent some time out of it
									var tolerance = null;

									self.$elProxy.add(self.$tooltip)
										// hide after some time out of the proxy and the tooltip
										.on('mouseleave.'+ self.namespace + '-autoClose', function() {
											clearTimeout(tolerance);
											tolerance = setTimeout(function(){
												self.hide();
											}, self.options.interactiveTolerance);
										})
										// suspend timeout when the mouse is over the proxy or the tooltip
										.on('mouseenter.'+ self.namespace + '-autoClose', function() {
											clearTimeout(tolerance);
										});
								}
								// if this is a non-interactive tooltip, get rid of it if the mouse leaves
								else {
									self.$elProxy.on('mouseleave.'+ self.namespace + '-autoClose', function() {
										self.hide();
									});
								}

								// close the tooltip when the proxy gets a click (common behavior of native tooltips)
								if (self.options.hideOnClick) {

									self.$elProxy.on('click.'+ self.namespace + '-autoClose', function() {
										self.hide();
									});
								}
							}
							// here we'll set the same bindings for both clicks and touch on the body to hide the tooltip
							else if(self.options.trigger == 'click'){

								// use a timeout to prevent immediate closing if the method was called on a click event and if options.delay == 0 (because of bubbling)
								setTimeout(function() {
									$('body').on('click.'+ self.namespace +' touchstart.'+ self.namespace, function() {
										self.hide();
									});
								}, 0);

								// if interactive, we'll stop the events that were emitted from inside the tooltip to stop autoClosing
								if (self.options.interactive) {

									// note : the touch events will just not be used if the plugin is not enabled on touch devices
									self.$tooltip.on('click.'+ self.namespace +' touchstart.'+ self.namespace, function(event) {
										event.stopPropagation();
									});
								}
							}
						}
					}

					// if we have a timer set, let the countdown begin
					if (self.options.timer > 0) {

						self.timerHide = setTimeout(function() {
							self.timerHide = null;
							self.hide();
						}, self.options.timer + extraTime);
					}
				}
			});
		},

		_interval_set: function() {

			var self = this;

			self.checkInterval = setInterval(function() {

				// if the tooltip and/or its interval should be stopped
				if (
						// if the origin has been removed
						$('body').find(self.$el).length === 0
						// if the elProxy has been removed
					||	$('body').find(self.$elProxy).length === 0
						// if the tooltip has been closed
					||	self.Status == 'hidden'
						// if the tooltip has somehow been removed
					||	$('body').find(self.$tooltip).length === 0
				) {
					// remove the tooltip if it's still here
					if (self.Status == 'shown' || self.Status == 'appearing') self.hide();

					// clear this interval as it is no longer necessary
					self._interval_cancel();
				}
				// if everything is alright
				else {
					// compare the former and current positions of the elProxy to reposition the tooltip if need be
					if(self.options.positionTracker){

						var p = self._repositionInfo(self.$elProxy),
							identical = false;

						// compare size first (a change requires repositioning too)
						if(areEqual(p.dimension, self.elProxyPosition.dimension)){

							// for elements with a fixed position, we track the top and left properties (relative to window)
							if(self.$elProxy.css('position') === 'fixed'){
								if(areEqual(p.position, self.elProxyPosition.position)) identical = true;
							}
							// otherwise, track total offset (relative to document)
							else {
								if(areEqual(p.offset, self.elProxyPosition.offset)) identical = true;
							}
						}

						if(!identical){
							self.reposition();
							self.options.positionTrackerCallback.call(self, self.$el);
						}
					}
				}
			}, 200);
		},

		_interval_cancel: function() {
			clearInterval(this.checkInterval);
			// clean delete
			this.checkInterval = null;
		},

		_content_set: function(content) {
			// clone if asked. Cloning the object makes sure that each instance has its own version of the content (in case a same object were provided for several instances)
			// reminder : typeof null === object
			if (typeof content === 'object' && content !== null && this.options.contentCloning) {
				content = content.clone(true);
			}
			this.Content = content;
		},

		_content_insert: function() {

			var self = this,
				$d = this.$tooltip.find('.grid-tooltip-content');

			if (typeof self.Content === 'string' && !self.options.contentAsHTML) {
				$d.text(self.Content);
			}
			else {
				$d
					.empty()
					.append(self.Content);
			}
		},

		_update: function(content) {

			var self = this;

			// change the content
			self._content_set(content);

			if (self.Content !== null) {

				// update the tooltip if it is open
				if (self.Status !== 'hidden') {

					// reset the content in the tooltip
					self._content_insert();

					// reposition and resize the tooltip
					self.reposition();

					// if we want to play a little animation showing the content changed
					if (self.options.updateAnimation) {

						if (GRID.browserSupportsTransitions()) {

							self.$tooltip.css({
								'width': '',
								'-webkit-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-moz-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-o-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-ms-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms'
							}).addClass('grid-tooltip-content-changing');

							// reset the CSS transitions and finish the change animation
							setTimeout(function() {

								if(self.Status != 'hidden'){

									self.$tooltip.removeClass('grid-tooltip-content-changing');

									// after the changing animation has completed, reset the CSS transitions
									setTimeout(function() {

										if(self.Status !== 'hidden'){
											self.$tooltip.css({
												'-webkit-transition': self.options.speed + 'ms',
												'-moz-transition': self.options.speed + 'ms',
												'-o-transition': self.options.speed + 'ms',
												'-ms-transition': self.options.speed + 'ms',
												'transition': self.options.speed + 'ms'
											});
										}
									}, self.options.speed);
								}
							}, self.options.speed);
						}
						else {
							self.$tooltip.fadeTo(self.options.speed, 0.5, function() {
								if(self.Status != 'hidden'){
									self.$tooltip.fadeTo(self.options.speed, 1);
								}
							});
						}
					}
				}
			}
			else {
				self.hide();
			}
		},

		_repositionInfo: function($el) {
			return {
				dimension: {
					height: $el.outerHeight(false),
					width: $el.outerWidth(false)
				},
				offset: $el.offset(),
				position: {
					left: parseInt($el.css('left')),
					top: parseInt($el.css('top'))
				}
			};
		},

		hide: function(callback) {

			var self = this;

			// save the method custom callback and cancel any show method custom callbacks
			if (callback) self.callbacks.hide.push(callback);
			self.callbacks.show = [];

			// get rid of any appearance timeout
			clearTimeout(self.timerShow);
			self.timerShow = null;
			clearTimeout(self.timerHide);
			self.timerHide = null;

			var finishCallbacks = function() {
				// trigger any hide method custom callbacks and reset them
				$.each(self.callbacks.hide, function(i,c) { c.call(self.$el); });
				self.callbacks.hide = [];
			};

			// hide
			if (self.Status == 'shown' || self.Status == 'appearing') {

				self.Status = 'disappearing';

				var finish = function() {

					self.Status = 'hidden';

					// detach our content object first, so the next jQuery's remove() call does not unbind its event handlers
					if (typeof self.Content == 'object' && self.Content !== null) {
						self.Content.detach();
					}

					self.$tooltip.remove();
					self.$tooltip = null;

					// unbind orientationchange, scroll and resize listeners
					$(window).off('.'+ self.namespace);

					$('body')
						// unbind any auto-closing click/touch listeners
						.off('.'+ self.namespace)
						.css('overflow-x', self.bodyOverflowX);

					// unbind any auto-closing click/touch listeners
					$('body').off('.'+ self.namespace);

					// unbind any auto-closing hover listeners
					self.$elProxy.off('.'+ self.namespace + '-autoClose');

					// call our constructor custom callback function
					self.options.functionAfter.call(self.$el, self.$el);

					// call our method custom callbacks functions
					finishCallbacks();
				};

				if (GRID.browserSupportsTransitions()) {

					self.$tooltip
						.clearQueue()
						.removeClass('grid-tooltip-' + self.options.animation + '-show')
						// for transitions only
						.addClass('grid-tooltip-dying');

					if(self.options.speed > 0) self.$tooltip.delay(self.options.speed);

					self.$tooltip.queue(finish);
				}
				else {
					self.$tooltip
						.stop()
						.fadeOut(self.options.speed, finish);
				}
			}
			// if the tooltip is already hidden, we still need to trigger the method custom callback
			else if(self.Status == 'hidden') {
				finishCallbacks();
			}

			return self;
		},

		// the public show() method is actually an alias for the private showNow() method
		show: function(callback) {
			this._showNow(callback);
			return this;
		},

		// 'update' is deprecated in favor of 'content' but is kept for backward compatibility
		update: function(c) {
			return this.content(c);
		},
		content: function(c) {
			// getter method
			if(typeof c === 'undefined'){
				return this.Content;
			}
			// setter method
			else {
				this._update(c);
				return this;
			}
		},

		reposition: function() {

			var self = this;

			// in case the tooltip has been removed from DOM manually
			if ($('body').find(self.$tooltip).length !== 0) {

				// reset width
				self.$tooltip.css('width', '');

				// find variables to determine placement
				self.elProxyPosition = self._repositionInfo(self.$elProxy);
				var arrowReposition = null,
					windowWidth = $(window).width(),
					// shorthand
					proxy = self.elProxyPosition,
					tooltipWidth = self.$tooltip.outerWidth(false),
					tooltipInnerWidth = self.$tooltip.innerWidth() + 1, // this +1 stops FireFox from sometimes forcing an additional text line
					tooltipHeight = self.$tooltip.outerHeight(false);

				// if this is an <area> tag inside a <map>, all hell breaks loose. Recalculate all the measurements based on coordinates
				if (self.$elProxy.is('area')) {
					var areaShape = self.$elProxy.attr('shape'),
						mapName = self.$elProxy.parent().attr('name'),
						map = $('img[usemap="#'+ mapName +'"]'),
						mapOffsetLeft = map.offset().left,
						mapOffsetTop = map.offset().top,
						areaMeasurements = self.$elProxy.attr('coords') !== undefined ? self.$elProxy.attr('coords').split(',') : undefined;

					if (areaShape == 'circle') {
						var areaLeft = parseInt(areaMeasurements[0]),
							areaTop = parseInt(areaMeasurements[1]),
							areaWidth = parseInt(areaMeasurements[2]);
						proxy.dimension.height = areaWidth * 2;
						proxy.dimension.width = areaWidth * 2;
						proxy.offset.top = mapOffsetTop + areaTop - areaWidth;
						proxy.offset.left = mapOffsetLeft + areaLeft - areaWidth;
					}
					else if (areaShape == 'rect') {
						var areaLeft = parseInt(areaMeasurements[0]),
							areaTop = parseInt(areaMeasurements[1]),
							areaRight = parseInt(areaMeasurements[2]),
							areaBottom = parseInt(areaMeasurements[3]);
						proxy.dimension.height = areaBottom - areaTop;
						proxy.dimension.width = areaRight - areaLeft;
						proxy.offset.top = mapOffsetTop + areaTop;
						proxy.offset.left = mapOffsetLeft + areaLeft;
					}
					else if (areaShape == 'poly') {
						var areaXs = [],
							areaYs = [],
							areaSmallestX = 0,
							areaSmallestY = 0,
							areaGreatestX = 0,
							areaGreatestY = 0,
							arrayAlternate = 'even';

						for (var i = 0; i < areaMeasurements.length; i++) {
							var areaNumber = parseInt(areaMeasurements[i]);

							if (arrayAlternate == 'even') {
								if (areaNumber > areaGreatestX) {
									areaGreatestX = areaNumber;
									if (i === 0) {
										areaSmallestX = areaGreatestX;
									}
								}

								if (areaNumber < areaSmallestX) {
									areaSmallestX = areaNumber;
								}

								arrayAlternate = 'odd';
							}
							else {
								if (areaNumber > areaGreatestY) {
									areaGreatestY = areaNumber;
									if (i == 1) {
										areaSmallestY = areaGreatestY;
									}
								}

								if (areaNumber < areaSmallestY) {
									areaSmallestY = areaNumber;
								}

								arrayAlternate = 'even';
							}
						}

						proxy.dimension.height = areaGreatestY - areaSmallestY;
						proxy.dimension.width = areaGreatestX - areaSmallestX;
						proxy.offset.top = mapOffsetTop + areaSmallestY;
						proxy.offset.left = mapOffsetLeft + areaSmallestX;
					}
					else {
						proxy.dimension.height = map.outerHeight(false);
						proxy.dimension.width = map.outerWidth(false);
						proxy.offset.top = mapOffsetTop;
						proxy.offset.left = mapOffsetLeft;
					}
				}

				// our function and global vars for positioning our tooltip
				var myLeft = 0,
					myLeftMirror = 0,
					myTop = 0,
					offsetY = parseInt(self.options.offsetY),
					offsetX = parseInt(self.options.offsetX),
					// this is the arrow position that will eventually be used. It may differ from the position option if the tooltip cannot be displayed in this position
					practicalPosition = self.options.position;

				// a function to detect if the tooltip is going off the screen horizontally. If so, reposition the crap out of it!
				function dontGoOffScreenX() {

					var windowLeft = $(window).scrollLeft();

					// if the tooltip goes off the left side of the screen, line it up with the left side of the window
					if((myLeft - windowLeft) < 0) {
						arrowReposition = myLeft - windowLeft;
						myLeft = windowLeft;
					}

					// if the tooltip goes off the right of the screen, line it up with the right side of the window
					if (((myLeft + tooltipWidth) - windowLeft) > windowWidth) {
						arrowReposition = myLeft - ((windowWidth + windowLeft) - tooltipWidth);
						myLeft = (windowWidth + windowLeft) - tooltipWidth;
					}
				}

				// a function to detect if the tooltip is going off the screen vertically. If so, switch to the opposite!
				function dontGoOffScreenY(switchTo, switchFrom) {
					// if it goes off the top off the page
					if(((proxy.offset.top - $(window).scrollTop() - tooltipHeight - offsetY - 12) < 0) && (switchFrom.indexOf('top') > -1)) {
						practicalPosition = switchTo;
					}

					// if it goes off the bottom of the page
					if (((proxy.offset.top + proxy.dimension.height + tooltipHeight + 12 + offsetY) > ($(window).scrollTop() + $(window).height())) && (switchFrom.indexOf('bottom') > -1)) {
						practicalPosition = switchTo;
						myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					}
				}

				if(practicalPosition == 'top') {
					var leftDifference = (proxy.offset.left + tooltipWidth) - (proxy.offset.left + proxy.dimension.width);
					myLeft = (proxy.offset.left + offsetX) - (leftDifference / 2);
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom', 'top');
				}

				if(practicalPosition == 'top-left') {
					myLeft = proxy.offset.left + offsetX;
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom-left', 'top-left');
				}

				if(practicalPosition == 'top-right') {
					myLeft = (proxy.offset.left + proxy.dimension.width + offsetX) - tooltipWidth;
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom-right', 'top-right');
				}

				if(practicalPosition == 'bottom') {
					var leftDifference = (proxy.offset.left + tooltipWidth) - (proxy.offset.left + proxy.dimension.width);
					myLeft = proxy.offset.left - (leftDifference / 2) + offsetX;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top', 'bottom');
				}

				if(practicalPosition == 'bottom-left') {
					myLeft = proxy.offset.left + offsetX;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top-left', 'bottom-left');
				}

				if(practicalPosition == 'bottom-right') {
					myLeft = (proxy.offset.left + proxy.dimension.width + offsetX) - tooltipWidth;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top-right', 'bottom-right');
				}

				if(practicalPosition == 'left') {
					myLeft = proxy.offset.left - offsetX - tooltipWidth - 12;
					myLeftMirror = proxy.offset.left + offsetX + proxy.dimension.width + 12;
					var topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
					myTop = proxy.offset.top - (topDifference / 2) - offsetY;

					// if the tooltip goes off boths sides of the page
					if((myLeft < 0) && ((myLeftMirror + tooltipWidth) > windowWidth)) {
						var borderWidth = parseFloat(self.$tooltip.css('border-width')) * 2,
							newWidth = (tooltipWidth + myLeft) - borderWidth;
						self.$tooltip.css('width', newWidth + 'px');

						tooltipHeight = self.$tooltip.outerHeight(false);
						myLeft = proxy.offset.left - offsetX - newWidth - 12 - borderWidth;
						topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
						myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					}

					// if it only goes off one side, flip it to the other side
					else if(myLeft < 0) {
						myLeft = proxy.offset.left + offsetX + proxy.dimension.width + 12;
						arrowReposition = 'left';
					}
				}

				if(practicalPosition == 'right') {
					myLeft = proxy.offset.left + offsetX + proxy.dimension.width + 12;
					myLeftMirror = proxy.offset.left - offsetX - tooltipWidth - 12;
					var topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
					myTop = proxy.offset.top - (topDifference / 2) - offsetY;

					// if the tooltip goes off boths sides of the page
					if(((myLeft + tooltipWidth) > windowWidth) && (myLeftMirror < 0)) {
						var borderWidth = parseFloat(self.$tooltip.css('border-width')) * 2,
							newWidth = (windowWidth - myLeft) - borderWidth;
						self.$tooltip.css('width', newWidth + 'px');

						tooltipHeight = self.$tooltip.outerHeight(false);
						topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
						myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					}

					// if it only goes off one side, flip it to the other side
					else if((myLeft + tooltipWidth) > windowWidth) {
						myLeft = proxy.offset.left - offsetX - tooltipWidth - 12;
						arrowReposition = 'right';
					}
				}

				// if arrow is set true, style it and append it
				if (self.options.arrow) {

					var arrowClass = 'grid-tooltip-arrow-' + practicalPosition;

					// set color of the arrow
					if(self.options.arrowColor.length < 1) {
						var arrowColor = self.$tooltip.css('background-color');
					}
					else {
						var arrowColor = self.options.arrowColor;
					}

					// if the tooltip was going off the page and had to re-adjust, we need to update the arrow's position
					if (!arrowReposition) {
						arrowReposition = '';
					}
					else if (arrowReposition == 'left') {
						arrowClass = 'grid-tooltip-arrow-right';
						arrowReposition = '';
					}
					else if (arrowReposition == 'right') {
						arrowClass = 'grid-tooltip-arrow-left';
						arrowReposition = '';
					}
					else {
						arrowReposition = 'left:'+ Math.round(arrowReposition) +'px;';
					}

					// building the logic to create the border around the arrow of the tooltip
					if ((practicalPosition == 'top') || (practicalPosition == 'top-left') || (practicalPosition == 'top-right')) {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-bottom-width')),
							tooltipBorderColor = self.$tooltip.css('border-bottom-color');
					}
					else if ((practicalPosition == 'bottom') || (practicalPosition == 'bottom-left') || (practicalPosition == 'bottom-right')) {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-top-width')),
							tooltipBorderColor = self.$tooltip.css('border-top-color');
					}
					else if (practicalPosition == 'left') {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-right-width')),
							tooltipBorderColor = self.$tooltip.css('border-right-color');
					}
					else if (practicalPosition == 'right') {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-left-width')),
							tooltipBorderColor = self.$tooltip.css('border-left-color');
					}
					else {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-bottom-width')),
							tooltipBorderColor = self.$tooltip.css('border-bottom-color');
					}

					if (tooltipBorderWidth > 1) {
						tooltipBorderWidth++;
					}

					var arrowBorder = '';
					if (tooltipBorderWidth !== 0) {
						var arrowBorderSize = '',
							arrowBorderColor = 'border-color: '+ tooltipBorderColor +';';
						if (arrowClass.indexOf('bottom') !== -1) {
							arrowBorderSize = 'margin-top: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('top') !== -1) {
							arrowBorderSize = 'margin-bottom: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('left') !== -1) {
							arrowBorderSize = 'margin-right: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('right') !== -1) {
							arrowBorderSize = 'margin-left: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						arrowBorder = '<span class="grid-tooltip-arrow-border" style="'+ arrowBorderSize +' '+ arrowBorderColor +';"></span>';
					}

					// if the arrow already exists, remove and replace it
					self.$tooltip.find('.grid-tooltip-arrow').remove();

					// build out the arrow and append it
					var arrowConstruct = '<div class="'+ arrowClass +' grid-tooltip-arrow" style="'+ arrowReposition +'">'+ arrowBorder +'<span style="border-color:'+ arrowColor +';"></span></div>';
					self.$tooltip.append(arrowConstruct);
				}

				// position the tooltip
				self.$tooltip.css({'top': Math.round(myTop) + 'px', 'left': Math.round(myLeft) + 'px'});
			}

			return self;
		},

		enable: function() {
			this.enabled = true;
			return this;
		},

		disable: function() {
			// hide first, in case the tooltip would not disappear on its own (autoClose false)
			this.hide();
			this.enabled = false;
			return this;
		},

		destroy: function() {

			var self = this;

			self.hide();

			// remove the icon, if any
			if (self.$el[0] !== self.$elProxy[0]) {
				self.$elProxy.remove();
			}

			self.$el
				.removeData(self.namespace)
				.off('.'+ self.namespace);

			var ns = self.$el.data('grid.tooltip');

			// if there are no more tooltips on this element
			if(ns.length === 1){

				// optional restoration of a title attribute
				var title = null;
				if (self.options.restoration === 'previous'){
					title = self.$el.data('grid.tooltip.initialTitle');
				}
				else if(self.options.restoration === 'current'){

					// old school technique to stringify when outerHTML is not supported
					title =
						(typeof self.Content === 'string') ?
						self.Content :
						$('<div></div>').append(self.Content).html();
				}

				if (title) {
					self.$el.attr('title', title);
				}

				// final cleaning
				self.$el
					.removeClass('tooltipstered')
					.removeData('grid.tooltip')
					.removeData('grid.tooltip.initialTitle');
			}
			else {
				// remove the instance namespace from the list of namespaces of tooltips present on the element
				ns = $.grep(ns, function(el, i){
					return el !== self.namespace;
				});
				self.$el.data('grid.tooltip', ns);
			}

			return self;
		},

		elementIcon: function() {
			return (this.$el[0] !== this.$elProxy[0]) ? this.$elProxy[0] : undefined;
		},

		elementTooltip: function() {
			return this.$tooltip ? this.$tooltip[0] : undefined;
		},

		// public methods but for internal use only
		// getter if val is ommitted, setter otherwise
		option: function(o, val) {
			if (typeof val == 'undefined') return this.options[o];
			else {
				this.options[o] = val;
				return this;
			}
		},
		status: function() {
			return this.Status;
		}
	};

    // ------------------------------------------------------------
    // PLUGIN DEFINITION
    // ------------------------------------------------------------
	function Plugin() {

		// for using in closures
		var args = arguments;
        var data = $(this).data('grid.tooltip');
        args[0]  = args[0] || {};

        if (typeof args[0] == 'object' && args[0].element) {
            var options  = args[0];
            var params   = $.extend({}, Tooltip.DEFAULTS, options);
            var $wrapper = $(params.element || this);

            return $wrapper.each(function () {
                if (params.multiple) {
                    var ns = $(this).data('grid.tooltip.ns');
                    if (!ns) ns = [];
                    var instance = new Tooltip(this, params);
                    ns.push(instance.namespace);
                    $(this).data('grid.tooltip.ns', ns);
                }
                $(this).data('grid.tooltip', (data = new Tooltip(this, params)));
            });
        }
        else {
            // if we are not in the context of jQuery wrapped HTML element(s) :
            // this happens when calling static methods in the form $.fn.tooltip('methodName'), or when calling $(sel).tooltip('methodName or options') where $(sel) does not match anything
            if (this.length === 0) {

                // if the first argument is a method name
                if (typeof args[0] === 'string') {

                    var methodIsStatic = true;

                    // list static methods here (usable by calling $.fn.tooltip('methodName');)
                    switch (args[0]) {

                        case 'setDefaults':
                            // change default options for all future instances
                            $.extend(Tooltip.DEFAULTS, args[1]);
                            break;

                        default:
                            methodIsStatic = false;
                            break;
                    }

                    // $.fn.tooltip('methodName') calls will return true
                    if (methodIsStatic) return true;
                    // $(sel).tooltip('methodName') calls will return the list of objects event though it's empty because chaining should work on empty lists
                    else return this;
                }
                // the first argument is undefined or an object of options : we are initalizing but there is no element matched by selector
                else {
                    // still chainable : same as above
                    return this;
                }
            }
            // this happens when calling $(sel).tooltip('methodName or options') where $(sel) matches one or more elements
            else {

                // method calls
                if (typeof args[0] === 'string') {

                    var v = '#*$~&';

                    this.each(function() {

                        // retrieve the namepaces of the tooltip(s) that exist on that element. We will interact with the first tooltip only.
                        var ns = $(this).data('grid.tooltip'),
                            // self represents the instance of the first tooltip plugin associated to the current HTML object of the loop
                            self = ns ? $(this).data(ns[0]) : null;

                        // if the current element holds a tooltip instance
                        if (self) {

                            if (typeof self[args[0]] === 'function') {
                                // note : args[1] and args[2] may not be defined
                                var resp = self[args[0]](args[1], args[2]);
                            }
                            else {
                                throw new Error('Unknown method .tooltip("' + args[0] + '")');
                            }

                            // if the function returned anything other than the instance itself (which implies chaining)
                            if (resp !== self){
                                v = resp;
                                // return false to stop .each iteration on the first element matched by the selector
                                return false;
                            }
                        }
                        else {
                            throw new Error('You called G.R.I.D Tooltip\'s "' + args[0] + '" method on an uninitialized element');
                        }
                    });

                    return (v !== '#*$~&') ? v : this;
                }
                // first argument is undefined or an object : the tooltip is initializing
                else {

                    var instances = [],
                        // is there a defined value for the multiple option in the options object ?
                        multipleIsSet = args[0] && typeof args[0].multiple !== 'undefined',
                        // if the multiple option is set to true, or if it's not defined but set to true in the defaults
                        multiple = (multipleIsSet && args[0].multiple) || (!multipleIsSet && Tooltip.DEFAULTS.multiple),
                        // same for debug
                        debugIsSet = args[0] && typeof args[0].debug !== 'undefined',
                        debug = (debugIsSet && args[0].debug) || (!debugIsSet && Tooltip.DEFAULTS.debug);

                    // initialize a tooltip instance for each element if it doesn't already have one or if the multiple option is set, and attach the object to it
                    return this.each(function () {

                        var go = false,
                            ns = $(this).data('grid.tooltip.ns'),
                            instance = null;

                        if (!ns) {
                            go = true;
                        }
                        else if (multiple) {
                            go = true;
                        }
                        else if (debug) {
                            console.log('G.R.I.D Tooltip: one or more tooltips are already attached to this element: ignoring. Use the "multiple" option to attach more tooltips.');
                        }

                        if (go) {
                            instance = new Tooltip(this, $.extend({}, Tooltip.DEFAULTS, args[0]));

                            // save the reference of the new instance
                            if (!ns) ns = [];
                            ns.push(instance.namespace);
                            $(this).data('grid.tooltip.ns', ns)

                            // save the instance itself
                            $(this).data(instance.namespace, instance);
                        }

                        instances.push(instance);
                    });
                }
            }
        }
	};

    var old = $.grid.tooltip || $.fn.tooltip;

    // G.R.I.D jQuery Global Var
    // ------------------------------------------------------------
    $.grid.tooltip             = Plugin;
    $.grid.tooltip.Constructor = Tooltip;

    // Alert jQuery Extension
    // ------------------------------------------------------------
    $.fn.tooltip               = Plugin;
    $.fn.tooltip.Constructor   = Tooltip;

    // ------------------------------------------------------------
    // NO CONFLICT
    // ------------------------------------------------------------
    $.grid.tooltip.noConflict  = function() {
        $.grid.tooltip = old;
        return this;
    };

	// Quick & dirty compare function (not bijective nor multidimensional)
	function areEqual(a,b) {
		$.each(a, function(i, el){
			if(typeof b[i] === 'undefined' || a[i] !== b[i]){
				return false;
			}
		});
		return true;
	}

}(jQuery);