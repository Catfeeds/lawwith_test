(function() {
    // 配置
    var envir = 'online';
    var configMap = {
        test: {
            appkey: '2efb9a02309e00ec9153324af405dea8',
            url:'https://apptest.netease.im'
        },
        pre:{
            appkey: '2efb9a02309e00ec9153324af405dea8',
            url:'http://dgphy12.netease.im:9093'
        },
        online: {
            appkey: '2efb9a02309e00ec9153324af405dea8',
            url:'https://app.netease.im'
        }
    };
    window.CONFIG = configMap[envir];
}())