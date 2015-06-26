var CONFIG = (function() {
    return {
        get: function(name) {
            return private[name];
        }
    };
})();

var GTm;
(new (function GTMain() {
    var _this = GTm = this;
    var _options = {};
    this.init = function(options) {
        _options = options || {};


        $.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            $.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
    }

    /**
    * Check of variable or element of array initialization (not check defining)
    * @access	public
    * @param	mixed
    * @return	bool
    */
    this.isset = function(variables) {
        return (typeof (variables) != 'undefined' && variables != null);
    }

    this.ifset = function(variables, defValue) {
        return this.isset(variables) ? variables : defValue;
    }

    this.post = function(url, post_data, callback, errorCallback) {
        jQuery.post(url, post_data, function(data) {
            if (_this.isset(data.error) && _this.isset(data.result)) {
                if (!data.error) {
                    callback(data.result);
                } else if (data.error && data.result.code == 1) {
                    _this.dialog({
                        header: "Error",
                        body: "Sorry, you don't have access to receive this information!",
                        btnOk: function() {
                            return true;
                        }
                    });
                } else {
                    if ($.isFunction(errorCallback)) {
                        errorCallback(data.result);
                    } else {
                        _this.dialog({
                            header: "Error",
                            body: data.result.message || "Some error occurred on the server",
                            btnOk: function() {
                                return true;
                            }
                        });
                    }

                }
            } else {
                _this.dialog({
                    header: "Error",
                    body: "Wrong response from server!",
                    btnOk: function() {
                        return true;
                    }
                });
            }
        }, "json");
    }

    /**
     * Options can have following keys:
     * btnOk - callback
     * btnCancel - callback
     * url - upload dialog body from server. Server should return "html" key to fill dialog content.
     * data - post parameters (key/value array). It is using if url is specified.
     * header - header of dialog (text)
     * body - dialog body (HTML or text)
     * backdrop - block page while dialog is opened (true/false)
     * keyboard - close dialog by ESC (true/false)
     * before - callback, fire before opening dialog.
     * width, height - dialog width and height
     */
    this.dialog = function(options) {
        if (_this.isset(options)) {
            var okCallback = null, closeCallback = null;
            var btnOk = options.btnOk || null;
            var btnCancel = options.btnCancel || null;
            var dataResult = null;
            if (options.url) {
                $.ajax({
                    url: options.url,
                    async: false,
                    data: options.data || null,
                    dataType: "json"
                }).done(function(data) {
                    if (_this.isset(data.error) && _this.isset(data.result)) {
                        dataResult = data.result;
                        if (data.error || !_this.isset(data.result.html)) {
                            options.header = "Error";
                            options.body = data.message ? data.message : "Wrong response from server!";
                        } else {
                            options.header = data.header ? data.header : options.header;
                            options.body = data.result.html;
                        }
                    }
                }).fail(function(data) {
                    options.header = "Error";
                    options.body = data.message ? data.message : "Wrong response from server!";
                });
            }

            //if (options.width && parseInt(options.width) > 0) {
            //    var width = parseInt(options.width);
            //    $("#dialog").css("width", options.width + "px");
            //    $("#dialog").css("margin-left", "-" + (width / 2) + "px");
            //} else {
            //    $("#dialog").css("width", "");
            //    $("#dialog").css("margin-left", "");
            //}

            if (options.header) {
                $("#dialog .modal-header h3").html(options.header);
                $("#dialog .modal-header").removeClass("hide");
            } else {
                $("#dialog .modal-header").addClass("hide");
            }

            $('#dialog #btnOk').addClass('hide');
            if (btnOk) {
                if (btnOk.label) {
                    $('#dialog #btnOk').text(btnOk.label);
                }
                $('#dialog #btnOk').removeClass('hide');
                $('#dialog #btnOk').unbind('click').click(function() {
                    okCallback = $.isFunction(btnOk) ? btnOk : btnOk.callback;
                    $('#dialog').modal('hide');
                });
            }

            $('#dialog #btnCancel').addClass('hide');
            if (btnCancel) {
                $('#dialog #btnCancel').removeClass('hide');
                if (btnCancel.label) {
                    $('#dialog #btnCancel').text(btnCancel.label);
                }
                closeCallback = $.isFunction(btnCancel) ? btnCancel : btnCancel.callback;
            }

            $("#dialog .modal-body").html(options.body);

            $('#dialog').unbind('hide').unbind('shown');
            $('#dialog').modal({
                backdrop: _this.ifset(options.backdrop, true),
                keyboard: _this.ifset(options.keyboard, true),
                show: false
            }).on('hide', function() {
                if ($.isFunction(okCallback)) {
                    var value = okCallback($('#dialog'));
                    okCallback = null;
                    return value;
                } else if ($.isFunction(closeCallback)) {
                    var value = closeCallback($('#dialog'));
                    closeCallback = null;
                    return value;
                }
                return true;
            }).on('shown', function() {
                if (options.before && $.isFunction(options.before)) {
                    options.before(dataResult);
                }
            });
            $('#dialog').modal('show');
        }
    }

})());