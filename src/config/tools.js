let tool = {};
/**
 * @description 延迟函数
 * */
tool.delay = (function () {
    let timer = 0;
    return function (callback, time) {
        clearTimeout(timer);
        timer = setTimeout(callback, time);
    };
})();
tool.formatDate = function(date, fmt) {
    if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
    }
    let o = {
        'M+': date.getMonth() + 1,
        'd+': date.getDate(),
        'h+': date.getHours(),
        'm+': date.getMinutes(),
        's+': date.getSeconds()
    };
    for (let k in o) {
        if (new RegExp(`(${k})`).test(fmt)) {
            let str = o[k] + '';
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? str : padLeftZero(str));
        }
    }
    return fmt;
};
function padLeftZero(str) {
    return ('00' + str).substr(str.length);
}
/**
 * @param {Object} param
 * @description 定时器
 * */
tool.timer = function (param) {
    let data = {
        toDo: param.toDo || function () {
        },
        didStop: param.didStop || function () {
        },
        interval: param.interval || 1000,
        repeats: param.repeats || true
    };
    let timer_t = null;
    let count = 1;
    let obj = {
        clear: function () {
            clearInterval(timer_t);
            data.didStop();
        }
    };
    timer_t = setInterval(function () {
        if (data.repeats) {
            data.toDo(obj);
        } else {
            if (count > 0) {
                count--;
                data.toDo(obj);
            } else {
                clearInterval(timer_t);
                timer_t = null;
                data.didStop();
            }
        }
    }, data.interval);
    return timer_t;
};

/**
 * @param {String} par
 * @param {String} specialKey
 * @description  从URL上获得参数
 */
tool.getPar = function (par, specialKey) {
    //获取当前URL
    let local_url = document.location.href;
    local_url = decodeURI(local_url);
    //获取要取得的get参数位置
    let get = local_url.indexOf(par + "=");
    if (get === -1) {
        return "";
    }
    //截取字符串
    let get_par = local_url.slice(par.length + get + 1);

    //判断特殊par eg:specialKey = toUrl
    if (par === specialKey) {
        return get_par;
    }

    //判断截取后的字符串是否还有其他get参数
    let nextPar = get_par.indexOf("&");
    if (nextPar !== -1) {
        get_par = get_par.slice(0, nextPar);
    }
    return get_par;
};
/**
 * @param {String} type
 * */
tool.getUrlParams = function (type) {
    var url = document.location.href;
    if (url.indexOf("?") != -1) {
        var url_params_data;
        var str = url.substr(1);
        var strs = str.split("?")[1];
        strs = strs.split("&");
        // console.log(strs);
        for (var i = 0; i < strs.length; i++) {
            var params1 = strs[i].split("=")[0];
            var params2 = strs[i].split("=")[1];
            if (params1 == type) {
                return params2;
            }
        }
    }
    return '';
};

/**
 * @param {String} name
 * @param {String} value
 * @param {String} time
 * @description 设置cookie   eg:time='d30|s30|h24'
 * */
tool.setCookie = function (cname, cvalue, hour) {
    var d = new Date();
    d.setTime(d.getTime() + (hour   * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    // console.info(cname + "=" + cvalue + "; " + expires);
    // document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/";
    document.cookie = cname + "=" + cvalue +"; path=/";
    // console.info(document.cookie);

};
/**
 * @param {String} name
 * @description 删除指定cookie
 * */
tool.delCookie = function (name) {
    let exp = new Date();
    exp.setTime(exp.getTime() - 1);
    let cval = tool.getCookie(name);
    if (cval !== null)
        document.cookie = name + "=" + cval + ";path=/;expires=" + exp.toUTCString();
};
/**
 * @param {String} name
 * @description 得到指定cookie
 * */
tool.getCookie = function (cname) {

    var name = cname + "=";
    var ca = document.cookie.split(';');
    // console.log("获取cookie,现在循环")
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        // console.log(c);
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) != -1) {
            // console.log(decodeURI(c.substring(name.length, c.length)));
            return decodeURI(c.substring(name.length, c.length));
            // return c.substring(name.length, c.length);
        }
    }
    // if (cname == "aid"){
    //     window.location.href = "/index/index/login";
    // }
    return "";
};
tool.getCurrentCookie = function (cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) != -1) {
            return decodeURI(c.substring(name.length, c.length));
        }
    }
    return "";
};

export default tool;