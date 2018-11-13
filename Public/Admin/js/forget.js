/**
 * 忘记密码
 * Created by Mrx on 2016/5/31.
 */
// forget javascript
var FORGET = {
    lock: true,
    submitLock: true,
    total: 60,
    oldval: {},
    valid: {},
    invalid: {},
    init: function() {
        var self = this;
        this.bindEle();
        PASSPORT.getImgcode($('.verifyCode'));
        $('.verifyCode').click();
        // placeholder
        $('.passport-txt').passportPlaceholder('placeholder');
        $('input[name="password"]').passportPasswordStrong($('#safely'));

        this.emailCheckSend('#forget-form-email-send', '#forgetEmail');
//      this.emailCheckView();
        this.emailCheckApply('#forget-form-email-apply', '#applyEmail');
        this.phoneCheckSend('#forget-form-phone-send', '#forgetPhone');
        this.phoneCheckView('#forget-form-phone-view', '#mcodeSubmit');
        this.phoneCheckApply('#forget-form-phone-apply', '#applyPhone');
    },
    bindEle: function() {
        var self = this;
        var $emailsend = $('#forget-form-email-send');
        var $emailapply = $('#forget-form-email-apply');
        var $phonesend = $('#forget-form-phone-send');
        var $phoneview = $('#forget-form-phone-view');
        var $phoneapply = $('#forget-form-phone-apply');
        // 刷新图片验证码
        $('.refreshCode, .verifyCode').bind('click', function() {
            var $getcode = $(this).parent().find('.verifyCode');
            PASSPORT.getImgcode($getcode);
        });

        $('body').delegate('.passport-txt', 'focus', function() {
            PASSPORT.removeHint($(this), self.hintclass());
            PASSPORT.removeInputState($(this), self.inputclass());
        });
        $(document).bind('keyup', function(e) {
            if (e.keyCode == 13) {
                if ($emailsend.length) $emailsend.find('#forgetEmail').click();
                if ($emailapply.length) $emailapply.find('#applyEmail').click();
                if ($phonesend.length) $phonesend.find('#forgetPhone').click();
                if ($phoneview.length) $phoneview.find('#mcodeSubmit').click();
                if ($phoneapply.length) $phoneapply.find('#applyPhone').click();
            }
        });
    },
    hintclass: function() {
        return PASSPORT.toHintClass('passport-note', ['passport-error-text']);
    },
    inputclass: function() {
        return PASSPORT.toStateClass(['passport-error-input']);
    },
    hintEvent: function(elem, errhint) {
        PASSPORT.insetHint(elem, errhint, this.hintclass());
        PASSPORT.removeInputState(elem, this.inputclass());
        PASSPORT.addInputState(elem, this.inputclass());
    },
    hintArgs: {
        tag: 'div',
        hint: 'passport-note',
        state: 'passport-error-text'
    },
    getVoice: function(form, param) {
        var self = this;
        self.lock = false;

        var sets = $.extend({
            fn: null,
        }, param);
        var $this = $(this);
        var $form = $(form);
        var $getvoice = $form.find('.js-getvoice');
        var $revoice = $form.find('.js-revoice');
        var $getCode = $form.find('.getCode');
        var $reVoice = $form.find('.reVoice');
        var $mcode = $form.find('input[name="verify_code"]');
        var $verify = $form.find('input[name="verify"]');
        var verifyCode = $.trim($verify.val());
        var phone = PASSPORT.getCookie('forget_phone');

        var required = function(elem, val, hint /*, rules*/) {
            var name = elem.attr('name');
            if (!val) {
                self.hintArgs.text = hint;
                var errhint = PASSPORT.createHint(self.hintArgs, 'span');
                self.hintEvent(elem, errhint);
                self.lock = true;
                return false;
            } else if (arguments.length > 3) {
                return arguments[3];
            }
            return true;
        };
        var check = required($verify, verifyCode, passportHint.verify.required);
        if (!check) return false;

        if (!phone) {
            PASSPORT.msgBox(0, '手机号有误，请回到上一步重新填写');
            self.lock = true;
            return false;
        }

        $.ajax({
            type: 'post',
            url: passportUrl + '/check/phone?is_ajax=1',
            data: {phone: phone},
            dataType: 'json',
            error: function() {
                PASSPORT.msgBox(0, '手机：网络出错，过会再试');
                self.lock = true;
            },
            success: function(res) {
                if (res.status == 0) {
                    $.ajax({
                        type: 'post',
                        url: passportUrl + '/check/verify?is_ajax=1',
                        data: {verify: verifyCode},
                        dataType: 'json',
                        error: function() {
                            PASSPORT.msgBox(0, '验证：网络错误，过会再试');
                            self.lock = true;
                        },
                        success: function(res) {
                            if (res.status == 0) {
                                // 创建提示
                                self.hintArgs.text = '验证码错误';
                                var ajaxhint = PASSPORT.createHint(self.hintArgs, 'span');
                                self.hintEvent($verify, ajaxhint);
                                self.lock = true;
                                return false;
                            }
                            $getvoice.text('发送中');
                            var data = {
                                phone: phone,
                                verify: verifyCode,
                                type: 6,
                            };
                            $.ajax({
                                type: "post",
                                url: passportUrl + '/sso/voice?is_ajax=1',
                                data: data,
                                dataType: 'json',
                                error: function() {
                                    PASSPORT.msgBox(0, '语音：网络出错，过会再试');
                                    self.lock = true;
                                },
                                success: function(res) {
                                    if (res.status == 1) {
                                        $revoice.passportSetBtnTimer({
                                            time: self.total,
                                            timerstart: function(elem) {
                                                $getCode.hide();
                                                $reVoice.show();
                                                $form.children('.form-group:first').show().siblings('.form-group').hide();
                                                $mcode.trigger('focus');
                                                $('#sendsms').attr('disabled', 'disabled');
                                            },
                                            callback: function() {
                                                $getCode.show();
                                                $reVoice.hide();
                                                self.lock = true;
                                                $('.verifyCode').click();
                                            },
                                        });
                                        if (sets.fn) sets.fn();
                                    } else {
                                        PASSPORT.msgBox(0, res.msg);
                                        $getvoice.text('重新发送一遍');
                                        self.lock = true;
                                    }
                                },
                            });
                        }
                    });
                } else {
                    PASSPORT.msgBox(0, '手机未注册');
                    self.lock = true;
                }
            }
        });
    },
    getMcode: function(form, param) {
        var self = this;
        self.lock = false;

        var sets = $.extend({
            fn: null
        }, param);
        var $this = $(this);
        var $form = $(form);
        var $getcode = $form.find('.js-getcode');
        var $recode = $form.find('.js-recode');
        var $getCode = $form.find('.getCode');
        var $reCode = $form.find('.reCode');
        var $phone = $form.find('input[name="phone"]');
        var $verify = $form.find('input[name="verify"]');
        var $mcode = $form.find('input[name="verify_code"]');
        var phoneNum = $.trim($phone.val());
        var phone = PASSPORT.getCookie('forget_phone');
        var verifyCode = $.trim($verify.val());
        var required = function(elem, val, hint /*, rules*/) {
            var name = elem.attr('name');
            if (!val) {
                self.hintArgs.text = hint;
                var errhint = PASSPORT.createHint(self.hintArgs, 'span');
                self.hintEvent(elem, errhint);
                self.lock = true;
                return false;
            } else if (arguments.length > 3) {
                return arguments[3];
            }
            return true;
        };
        var check = required($verify, verifyCode, passportHint.verify.required);
        if (!check) return false;

        if (!phone) {
            PASSPORT.msgBox(0, '手机号有误，请回到上一步重新填写');
            self.lock = true;
            return false;
        }

        $.ajax({
            type: 'post',
            url: passportUrl + '/check/phone?is_ajax=1',
            data: {phone: phone},
            dataType: 'json',
            error: function() {
                PASSPORT.msgBox(0, '手机：网络出错了，请过会再试');
                self.lock = true;
            },
            success: function(res) {
                if (res.status == 0) {
                    $.ajax({
                        type: 'post',
                        url: passportUrl + '/check/verify?is_ajax=1',
                        data: {verify: verifyCode},
                        dataType: 'json',
                        error: function() {
                            PASSPORT.msgBox(0, '验证：网络错误，过会再试！');
                            self.lock = true;
                        },
                        success: function(res) {
                            if (res.status == 0) {
                                // 创建提示
                                self.hintArgs.text = '验证码错误';
                                var ajaxhint = PASSPORT.createHint(self.hintArgs, 'span');
                                self.hintEvent($verify, ajaxhint);
                                self.lock = true;
                                return false;
                            }
                            $getcode.text('发送中');
                            var data = {
                                phone: phone,
                                verify: verifyCode,
                                type: 6,
                            };
                            $.ajax({
                                type: "post",
                                url: passportUrl + '/sso/sms?is_ajax=1',
                                data: data,
                                dataType: 'json',
                                error: function() {
                                    PASSPORT.msgBox(0, '短信：网络错误，过会再试');
                                    self.lock = true;
                                },
                                success: function(res) {
                                    if (res.status == 1) {
                                        $recode.passportSetBtnTimer({
                                            time: self.total,
                                            timerstart: function(elem) {
                                                $getCode.hide();
                                                $reCode.show();
                                                $form.children('.form-group:first').show().siblings('.form-group').hide();
                                                $mcode.trigger('focus');
                                                $('#sendsms').attr('disabled', 'disabled');
                                            },
                                            callback: function() {
                                                $getCode.show();
                                                $reCode.hide();
                                                self.lock = true;
                                                $('.verifyCode').click();
                                            },
                                        });
                                        if (sets.fn) sets.fn();
                                    } else {
                                        PASSPORT.msgBox(0, res.msg);
                                        $getcode.text('重新发送一遍');
                                        self.lock = true;
                                    }
                                },
                            });
                        }
                    });
                } else {
                    PASSPORT.msgBox(0, '手机未注册');
                    self.lock = true;
                }
            }
        });
    },
    submit: function(param){
        var self = this;
        var sets = $.extend({
            'form': null,
            'button': null,
            'text': null,
            'error': null,
            'success': null
        }, param);

        if(!sets.form.valid() || !self.submitLock) return false;
        var oldtext = sets.button.val();
        self.submitLock = false;
        sets.button.val(sets.text);
        $.post(sets.form.attr('action') + '?is_ajax=1', sets.form.serialize(), function(res) {
            if (res.status != 1) {
                PASSPORT.msgBox(0, res.msg);
                self.submitLock = true;
                sets.form.find('.verifyCode').click();
                sets.button.val(oldtext);
                if (sets.error) sets.error(res);
            } else {
                if (sets.success) sets.success(res);
                window.location.href = res.jumpUrl
            }
        }, 'json');
    },
    resetData: function(mode) {
        this.oldval[mode] = {};
        this.valid[mode] = {};
        this.invalid[mode] = {};
    },
    required: function(param) {
        var self = this;
        var sets = $.extend({
            'elem': null,
            'val': null,
            'hint': null,
            'mode': null,
            'error': null,
            'success': null,
            'rules': null,
            'rangelength': null,
        }, param);
        var name = sets.elem.attr('name');
        var type = sets.elem.attr('type');
        var errhint;
        if (type == 'text' || type == 'password' || type == 'textarea') {
            if (!sets.val) {
                self.hintArgs.text = sets.hint;
                errhint = PASSPORT.createHint(self.hintArgs, 'span');
                self.hintEvent(sets.elem, errhint);
                self.oldval[sets.mode][name] = '';
                if (sets.error) sets.error();
                return false;
            } else {
                if (sets.success) sets.success();
            }
        } else if (type == 'checkbox' || type == 'radio') {
            if (!sets.elem.is(':checked')) {
                self.hintArgs.text = sets.hint;
                errhint = PASSPORT.createHint(self.hintArgs, 'span');
                self.hintEvent(sets.elem, errhint);
                self.oldval[sets.mode][name] = '';
                if (sets.error) sets.error();
                return false;
            } else {
                if (sets.success) sets.success();
            }
        }
        if (sets.rules && !self.rules(sets.rules)) return false;
        if (sets.rangelength && !self.rangelength(sets.rangelength)) return false;
        return true;
    },
    rules: function(param) {
        var self = this;
        var sets = $.extend({
            'elem': null,
            'val': null,
            'rules': null,
            'hint': null,
            'error': null,
            'success': null
        }, param);
        var rules = sets.rules.test(sets.val);
        var errhint;
        if (!rules) {
            self.hintArgs.text = sets.hint;
            errhint = PASSPORT.createHint(self.hintArgs, 'span');
            self.hintEvent(sets.elem, errhint);
            if (sets.error) sets.error();
            return false;
        } else {
            if (sets.success) sets.success();
        }
        return true;
    },
    rangelength: function(param) {
        var self = this;
        var sets = $.extend({
            'elem': null,
            'val': null,
            'rangelength': null,
            'hint': null
        }, param);
        var errhint, minlength = sets.rangelength[0], maxlength = sets.rangelength[1];
        if (sets.val.length < minlength || sets.val.length > maxlength) {
            self.hintArgs.text = sets.hint;
            errhint = PASSPORT.createHint(self.hintArgs, 'span');
            self.hintEvent(sets.elem, errhint);
            return false;
        }
        return true;
    },
    remote: function(args, settings) {
        var self = this;
        var sets = $.extend({
            'context': null,
            'mode': null,
            'elem': settings.elem,
            'hint': settings.hint,
            'reverse': false,
            'error': null,
            'success': null,
        }, settings);
        var name = sets.elem.attr('name');
        var status, errhint;
        var param = $.extend(true, {
            type: 'post',
            dataType: 'json',
            context: sets.context,
            error: function() {
                PASSPORT.msgBox(0, '远程：网络出错，过会再试');
            },
            success: function(res) {
                status = sets.reverse ? 0 : 1;
                if (res.status !== status) {
                    self.hintArgs.text = sets.hint;
                    errhint = PASSPORT.createHint(self.hintArgs, 'span');
                    self.hintEvent(sets.elem, errhint);
                    self.invalid[sets.mode][name] = true;
                    delete self.valid[sets.mode][name];
                    if (sets.error) return sets.error(res);
                } else {
                    self.valid[sets.mode][name] = true;
                    delete self.invalid[sets.mode][name];
                    if (sets.success) return sets.success(res);
                }
            }
        }, args);
        return param;
    },
    emailCheckSend: function(form, button) {
        var self = this;
        var $form = $(form);
        var $submit = $form.find(button);
        var $email = $form.find('input[name="email"]');
        var emailremote = '邮箱未注册';
        self.oldval.emailSend = {};
        self.valid.emailSend = {};
        self.invalid.emailSend = {};
        // 阻止默认事件，防止提交表单
        $email.bind('keydown', function(e) {
            if (e.keyCode == 13) e.preventDefault();
        });
        $email.bind('blur', function() {
            var $this = $(this);
            var name = $this.attr('name');
            var val = $.trim($this.val());
            var check = self.required({
                elem: $this,
                val: val,
                hint: passportHint.email.required,
                mode: 'emailSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po:91078});
                },
                rules: {
                    elem: $this,
                    val: val,
                    rules: passportReg.email,
                    hint: passportHint.email.rules,
                    error: function() {
                        if (typeof stat != 'undefined') stat.efunc({po: 91018});
                    }
                }
            });

            if (check) {
                if (self.oldval.emailSend[name] === val && self.valid.emailSend[name]) return false;
                if (self.oldval.emailSend[name] === val && self.valid.emailSend[name]) {
                    self.hintArgs.text = emailremote;
                    var errhint = PASSPORT.createHint(self.hintArgs, 'span');
                    self.hintEvent($this, errhint);
                    return false;
                }
                self.oldval.emailSend[name] = val;
                $.ajax(self.remote({
                    type: 'post',
                    url: passportUrl + '/check/email?is_ajax=1',
                    dataType: 'json',
                    data: {email: val}
                }, {
                    elem: $this,
                    mode: 'emailSend',
                    hint: emailremote,
                    reverse: true,
                    error: function() {
                        if(typeof stat != 'undefined') stat.efunc({po:91019});
                    }
                }));
            }
        });
        $submit.bind('click', function(e) {
            var $this = $(this);
            var emailname = $email.attr('name');
            var emailval = $.trim($email.val());
            var emailcheck = self.required({
                elem: $email,
                val: emailval,
                hint: passportHint.email.required,
                mode: 'emailSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po:91078});
                },
                rules: {
                    elem: $email,
                    val: emailval,
                    rules: passportReg.email,
                    hint: passportHint.email.rules,
                    error: function() {
                        if (typeof stat != 'undefined') stat.efunc({po: 91018});
                    }
                }
            });

            if (emailcheck) {
                $.ajax(self.remote({
                    type: 'post',
                    url: passportUrl + '/check/email?is_ajax=1',
                    dataType: 'json',
                    data: {email: emailval}
                }, {
                    elem: $email,
                    mode: 'emailSend',
                    hint: emailremote,
                    reverse: true,
                    error: function() {
                        if(typeof stat != 'undefined') stat.efunc({po: 91018});
                    },
                    success: function() {
                        self.resetData();
                        $form.submit();
                    }
                }));
            }
        });
    },
    emailCheckView: function() {
        if (!$('#userEmail').length || !$('#goto-email').length) return;
        var regEmailAddr = /[^@]+\w+$/;
        var regEmailName = /[^\.]+/;
        var email = $('#userEmail').text();
        var emailAddr = regEmailAddr.exec(email)[0];
        var emailDomain = regEmailName.exec(emailAddr)[0];
        $('#goto-email').text('登录' + emailDomain + '邮箱查看').attr('href', 'http://mail.' + emailAddr);
    },
    emailCheckApply: function(form, button) {
        if (!$(form).length) return;
        var self = this;
        var $form = $(form);
        var $submit = $form.find(button);
        var $pwd = $form.find('input[name="password"]');
        var checkpwd;

        var validator = $form.validate({
            errorClass: 'passport-error',
            errorElement: 'span',
            focusInvalid: false,
            onkeyup: false,
            errorPlacement: function(error, element) {
                var errhint = PASSPORT.createHint({
                    tag: 'div',
                    hint: 'passport-note',
                    state: 'passport-error-text',
                    text: error
                });
                self.hintEvent(element, errhint);
            },
            rules: {
                password: {
                    required: true,
                    rangelength: [6, 32],
                },
                repassword: {
                    required: true,
                    rangelength: [6, 32],
                    equalTo: '#password'
                }
            },
            messages: {
                password: {
                    required: passportHint.pwd.required,
                    rangelength: passportHint.pwd.rangelength,
                },
                repassword: {
                    required: passportHint.pwd.required,
                    rangelength: passportHint.pwd.rangelength,
                    equalTo: passportHint.pwd.repeat
                }
            },
        });
        $submit.bind('click', function() {
            self.submit({
                form: $form,
                button: $submit,
                text: '提交中',
            });
        });
        $pwd.bind('blur', function() {
            checkpwd = validator.element('input[name=password]');
            if (checkpwd) {
                var $safely = $form.find('#safely');
                if ($safely.hasClass('safely-danger')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91079});
                } else if ($safely.hasClass('safely-general')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91080});
                } else if ($safely.hasClass('safely-safe')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91081});
                }
            }
        });
    },
    phoneCheckSend: function(form, button) {
        var self = this;
        var $form = $(form);
        var $submit = $form.find(button);
        var $phone = $form.find('input[name="phone"]');
        var $verify = $form.find('input[name="verify"]');
        var premote = '手机未注册';
        self.oldval.phoneSend = {};
        self.valid.phoneSend = {};
        self.invalid.phoneSend = {};

        $phone.bind('blur', function() {
            var $this = $(this);
            var name = $this.attr('name');
            var val = $.trim($this.val());
            var check = self.required({
                elem: $this,
                val: val,
                hint: passportHint.phone.required,
                mode: 'phoneSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po: 91072});
                },
                rules: {
                    elem: $this,
                    val: val,
                    rules: passportReg.phone,
                    hint: passportHint.phone.rules,
                    error: function() {
                        if (typeof stat != 'undefined') stat.efunc({po: 91070});
                    }
                }
            });

            if (check) {
                if (self.oldval.phoneSend[name] === val && self.valid.phoneSend[name]) return false;
                if (self.oldval.phoneSend[name] === val && self.invalid.phoneSend[name]) {
                    self.hintArgs.text = premote;
                    var errhint = PASSPORT.createHint(self.hintArgs, 'span');
                    self.hintEvent($this, errhint);
                    return false;
                }
                self.oldval.phoneSend[name] = val;
                $.ajax(self.remote({
                    type: 'post',
                    url: passportUrl + '/check/phone?is_ajax=1',
                    dataType: 'json',
                    data: {phone: val}
                }, {
                    elem: $this,
                    mode: 'phoneSend',
                    hint: premote,
                    reverse: true,
                    error: function() {
                        if(typeof stat != 'undefined') stat.efunc({po: 91069});
                    }
                }));
            }
        });
        $verify.bind('blur', function() {
            var $this = $(this);
            var name = $this.attr('name');
            var val = $.trim($this.val());
            var check = self.required({
                elem: $this,
                val: val,
                hint: passportHint.verify.required,
                mode: 'phoneSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po: 91067});
                }
            });

            if (check) {
                if (self.oldval.phoneSend[name] === val && self.valid.phoneSend[name]) return false;
                if (self.oldval.phoneSend[name] === val && self.invalid.phoneSend[name]) {
                    self.hintArgs.text = passportHint.verify.remote;
                    var errhint = PASSPORT.createHint(self.hintArgs, 'span');
                    self.hintEvent($this, errhint);
                    return false;
                }
                self.oldval.phoneSend[name] = val;
                $.ajax(self.remote({
                    type: 'post',
                    url: passportUrl + '/check/verify?is_ajax=1',
                    dataType: 'json',
                    data: {verify: val}
                }, {
                    elem: $this,
                    mode: 'phoneSend',
                    hint: passportHint.verify.remote,
                    error: function() {
                        if(typeof stat != 'undefined') stat.efunc({po: 91071});
                        $form.find('.verifyCode').click();
                    }
                }));
            }
        });
        $submit.bind('click', function() {
            var $this = $(this);
            var phonename = $phone.attr('name');
            var phoneval = $.trim($phone.val());
            var phonecheck = self.required({
                elem: $phone,
                val: phoneval,
                hint: passportHint.phone.required,
                mode: 'phoneSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po: 91072});
                },
                rules: {
                    elem: $phone,
                    val: phoneval,
                    rules: passportReg.phone,
                    hint: passportHint.phone.rules,
                    error: function() {
                        if (typeof stat != 'undefined') stat.efunc({po: 91070});
                    }
                }
            });

            var verifyname = $verify.attr('name');
            var verifyval = $.trim($verify.val());
            var verifycheck = self.required({
                elem: $verify,
                val: verifyval,
                hint: passportHint.verify.required,
                mode: 'phoneSend',
                error: function() {
                    if(typeof stat != 'undefined') stat.efunc({po: 91067});
                }
            });

            if (phonecheck && verifycheck) {
                $.ajax(self.remote({
                    type: 'post',
                    url: passportUrl + '/check/phone?is_ajax=1',
                    dataType: 'json',
                    data: {phone: phoneval}
                }, {
                    elem: $phone,
                    mode: 'phoneSend',
                    hint: premote,
                    reverse: true,
                    error: function() {
                        if(typeof stat != 'undefined') stat.efunc({po: 91069});
                    },
                    success: function() {
                        $.ajax(
                            self.remote({
                                type: 'post',
                                url: passportUrl + '/check/verify?is_ajax=1',
                                dataType: 'json',
                                data: {verify: verifyval}
                            }, {
                                elem: $verify,
                                mode: 'phoneSend',
                                hint: passportHint.verify.remote,
                                error: function() {
                                    if(typeof stat != 'undefined') stat.efunc({po: 91071});
                                    $form.find('.verifyCode').click();
                                },
                                success: function() {
                                    self.resetData();
                                    PASSPORT.setCookie('forget_phone', phoneval, 1);
                                    $form.submit();
                                }
                            })
                        );
                    }
                }));
            }
        });
    },
    phoneCheckView: function(form, button) {
        if (!$(form).length) return;
        var self = this;
        var $form = $(form);
        var $submit = $form.find(button);
        var $phone = $form.find('input[name="phone"]');
        var $verify = $form.find('input[name="verify"]');
        var $tocode = $form.find('.to-getcode');
        var $tovoice = $form.find('.to-getvoice');

        $form.validate({
            errorClass: 'passport-error',
            errorElement: 'span',
            focusInvalid: false,
            onkeyup: false,
            errorPlacement: function(error, element) {
                var errhint = PASSPORT.createHint({
                    tag: 'div',
                    hint: 'passport-note',
                    state: 'passport-error-text',
                    text: error
                });
                self.hintEvent(element, errhint);
            },
            rules: {
                verify_code: {
                    required: true
                }
            },
            messages: {
                verify_code: {
                    required: passportHint.mobile.required
                },
            },
        });
        $submit.bind('click', function() {
            self.submit({
                form: $form,
                button: $submit,
                text: '提交中',
                error: function(res) {
                    if (res.msg == '动态码无效') {
                        if(typeof stat != 'undefined') stat.efunc({po:91073});
                    }
                }
            });
        });

        var sendsms = function(type) {
            $form.children('.form-group:first').hide().siblings('.form-group').show();
            $verify.trigger('focus');
            if (type == 'sms') {
                $('#sendsms').removeClass('js-getvoice').addClass('js-getcode').removeAttr('disabled').text('获取短信动态码');
                // 获取动态码
                $('#sendsms').unbind('click');
                $('#sendsms').bind('click', function() {
                    if (self.lock) self.getMcode('#forget-form-phone-view');
                });
            } else if (type == 'voice') {
                $('#sendsms').removeClass('js-getcode').addClass('js-getvoice').removeAttr('disabled').text('获取语音动态码');
                // 获取语音动态码
                $('#sendsms').unbind('click');
                $('#sendsms').bind('click', function() {
                    if (self.lock) self.getVoice('#forget-form-phone-view');
                });
            }
        };
        $tocode.bind('click', function() {
            sendsms('sms');
        });
        $tovoice.bind('click', function() {
            sendsms('voice');
        });
    },
    phoneCheckApply: function(form, button) {
        if (!$(form).length) return;
        var self = this;
        var $form = $(form);
        var $submit = $form.find(button);
        var $pwd = $form.find('input[name="password"]');
        var checkpwd;

        var validator = $form.validate({
            errorClass: 'passport-error',
            errorElement: 'span',
            focusInvalid: false,
            onkeyup: false,
            errorPlacement: function(error, element) {
                var errhint = PASSPORT.createHint({
                    tag: 'div',
                    hint: 'passport-note',
                    state: 'passport-error-text',
                    text: error
                });
                self.hintEvent(element, errhint);
            },
            rules: {
                password: {
                    required: true,
                    rangelength: [6, 32],
                },
                repassword: {
                    required: true,
                    rangelength: [6, 32],
                    equalTo: '#password'
                }
            },
            messages: {
                password: {
                    required: passportHint.pwd.required,
                    rangelength: passportHint.pwd.rangelength,
                },
                repassword: {
                    required: passportHint.pwd.required,
                    rangelength: passportHint.pwd.rangelength,
                    equalTo: passportHint.pwd.repeat
                }
            },
        });
        $submit.bind('click', function() {
            self.submit({
                form: $form,
                button: $submit,
                text: '提交中',
            });
        });
        $pwd.bind('blur', function() {
            checkpwd = validator.element('input[name=password]');
            if (checkpwd) {
                var $safely = $form.find('#safely');
                if ($safely.hasClass('safely-danger')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91074});
                } else if ($safely.hasClass('safely-general')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91075});
                } else if ($safely.hasClass('safely-safe')) {
                    if(typeof stat != 'undefined') stat.efunc({po:91076});
                }
            }
        });
    },
};

$(function() {
    FORGET.init();
});
