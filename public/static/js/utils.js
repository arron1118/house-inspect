!function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('@popperjs/core')) :
        typeof define === 'function' && define.amd ? define(['@popperjs/core'], factory) :
            (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.utils = factory(global));
}(this, (function (global) {
    'use strict';

    const $ = global.jQuery;
    const layer = global.layui.layer;

    function getDateTime(param = {}) {
        let defaultParam = {
            year: 0,
            month: 0,
            day: 0,
            hour: 0,
            minute: 0,
            second: 0
        };
        param = Object.assign(defaultParam, param)
        let d = new Date();
        let year = d.getFullYear() + param.year,
            month = d.getMonth() + 1 + param.month,
            day = d.getDate() + param.day,
            hour = d.getHours() + param.hour,
            minute = d.getMinutes() + param.minute,
            second = d.getSeconds() + param.second;

        if (month < 10) {
            month = '0' + month;
        }

        if (day < 10) {
            day = '0' + day;
        }

        if (hour < 10) {
            hour = '0' + hour;
        }

        if (minute < 10) {
            minute = '0' + minute;
        }

        if (second < 10) {
            second = '0' + second;
        }

        return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
    }

    /**
     * 设置 和 获取 cookie
     * @param key
     * @param val
     * @param obj
     * @returns {string|boolean|*[]}
     */
    function cookie(key = null, val = null, obj = {}) {
        if (key === null) {
            let val = [];
            let cookie = document.cookie.split('; ');
            $.each(cookie, function (index, item) {
                let i = item.split('=');
                val[i[0]] = i[1];
            })

            return val;
        } else {
            if (val === null) {
                let val = '';
                let cookie = document.cookie.split('; ');
                $.each(cookie, function (index, item) {
                    let i = item.split('=');
                    if (i[0] === key) {
                        val = i[1];
                        return false;
                    }
                })

                return val;
            } else {
                document.cookie = key + '=' + val + '; path=/';
                return true;
            }
        }
    }

    function isPhone(phone) {
        let pattern = /^1[3456789]\d{9}$/;
        return pattern.test(phone);
    }


    return {
        getDateTime,
        cookie,
        isPhone,
    }
}));


