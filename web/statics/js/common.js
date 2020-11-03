/**
 * 浏览器跳转链接
 * @param {JSON} params
 * @param {bool} newWindow
 */
const navigateTo = (params, newWindow = false) => {
    let url = null;
    if (typeof params === 'string') {
        url = params;
    } else {
        const queryString = Qs.stringify(params);
        url = `${_scriptUrl}?${queryString}`;
    }
    if (newWindow) {
        window.open(url);
    } else {
        window.location.href = url;
    }
};

const historyGo = (number) => {
  if (typeof number === 'number') {
      window.history.go(number);
  }
};

const Navigate = {
    install(Vue, options) {
        Vue.prototype.$navigate = function (params, newWindow) {
            navigateTo(params, newWindow);
        }
    }
};

const HistoryGo = {
    install(Vue, options) {
        Vue.prototype.$historyGo = function (number) {
            historyGo(number);
        }
    }
};

Vue.use(Navigate);
Vue.use(HistoryGo);

/**
 * 获取get请求参数的值
 * @param {String} name
 * @returns {String||null}
 */
const getQuery = (name) => {
    const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    const r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return decodeURIComponent(r[2]);
    }
    return null;
};

/**
 * 获取cookie值
 * @param {String} cname
 * @returns {String||null}
 */
const getCookieValue = (cname) => {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/**
 * 生成随机字符串
 * @param {Number} len
 * @returns {string}
 */
const randomString = (len) => {
    len = len || 32;
    let $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
    let maxPos = $chars.length;
    let pwd = '';
    for (i = 0; i < len; i++) {
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
};

const common = axios.create({
    transformRequest: [function (data, headers) {
        if (data instanceof FormData) {
            data.append('_csrf', _csrf);
        } else {
            if (data && !data['_csrf']) {
                data['_csrf'] = _csrf;
            }
            data = Qs.stringify(data);
        }
        return data;
    }],
});

window.request = common;

common.defaults.baseURL = _scriptUrl;
common.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
common.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

common.interceptors.request.use(function (config) {
    return config;
}, function (error) {
    return Promise.reject(error);
});

common.interceptors.response.use(function (response) {
    if (response.data && typeof response.data.code !== 'undefined') {
        if (response.data.code >= 400) {
            if (_layout) {
                _layout.$alert(response.data.msg, '错误');
            } else {
                console.log(response.data);
            }
        } else {
            return response;
        }
    } else {
        return Promise.reject(response);
    }
}, function (error) {
    if (_layout) {
        _layout.$alert(response.data.msg, '错误');
    } else {
        console.log(response.data);
    }
    return Promise.reject(error);
});

Vue.use({
    install(Vue, options) {
        Vue.prototype.$request = request;
    }
});

// 传入请求地址与页数获取列表
const loadList = (url, page) => {
    return request({
        params: {
            r: url,
            page: page
        },
    }).then(e => {
        if (e.data.code === 0) {
            return e.data.data;
        } else {
            this.$message.error(e.data.msg);
        }
    }).catch(e => {
    });
};