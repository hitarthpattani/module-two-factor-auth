/**
 * @package     HitarthPattani\TwoFactorAuth
 * @author      Hitarth Pattani <hitarthpattani@gmail.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_TwoFactorAuth/js/error'
], function ($, ko, Component, error) {
    'use strict';

    return Component.extend({
        currentStep: ko.observable('register'),
        waitVerifyText: ko.observable(''),
        waitResendText: ko.observable(''),
        verifyCode: ko.observable(''),
        defaults: {
            template: 'HitarthPattani_TwoFactorAuth/email/auth'
        },

        postUrl: '',
        resendUrl: '',
        successUrl: '',
        secretCode: '',
        watcherId: null,
        stopWatcherId: null,
        count: 0,
        timeout: 30,

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.initializeHandle();
            return this;
        },

        /**
         * Stop handle
         */
        stopHandle: function() {
            // Clear timers
            clearTimeout(this.stopWatcherId);
            clearInterval(this.watcherId);
            this.waitResendText('');
        },

        /**
         * Initialize handle
         */
        initializeHandle: function() {
            /**
             * Watch a result 1 time per second
             */
            this.count = 0;
            this.waitResendText("Resend code in (" + this.timeout + ")");
            this.watcherId = setInterval(this.startHandle.bind(this), 1000);
        },

        /**
         * Start handle
         */
        startHandle: function() {
            this.count++;
            this.waitResendText("Resend code in (" + (this.timeout - this.count) + ")");

            /**
             * If within 10 seconds the result is not received, then reject the request
             */
            var me = this;
            this.stopWatcherId = setTimeout(function () {
                me.stopHandle();
            }, ((me.timeout - 1) * 1000));
        },

        /**
         * Get POST URL
         * @returns {String}
         */
        getPostUrl: function () {
            return this.postUrl;
        },

        /**
         * Get POST URL
         * @returns {String}
         */
        getResendUrl: function () {
            return this.resendUrl;
        },

        /**
         * Get plain Secret Code
         * @returns {String}
         */
        getSecretCode: function () {
            return this.secretCode;
        },

        /**
         * Go to next step
         */
        nextStep: function () {
            this.currentStep('login');
            self.location.href = this.successUrl;
        },

        /**
         * Verify auth code
         */
        doVerify: function () {
            var me = this;

            this.waitVerifyText('Please wait...');
            $.post(this.getPostUrl(), {
                'tfa_code': this.verifyCode()
            })
                .done(function (res) {
                    if (res.success) {
                        me.nextStep();
                    } else {
                        error.display(res.message);
                        me.verifyCode('');
                    }
                    me.waitVerifyText('');
                })
                .fail(function () {
                    error.display('There was an internal error trying to verify your code');
                    me.waitVerifyText('');
                });
        },

        /**
         * Verify auth code
         */
        doResend: function () {
            var me = this;

            this.waitResendText('Please wait...');
            $.get(this.getResendUrl())
                .done(function (res) {
                    me.initializeHandle();
                })
                .fail(function () {
                    error.display('There was an internal error trying to verify your code');
                });
        }
    });
});
