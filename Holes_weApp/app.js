//app.js
App({
  onLaunch: function () {
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

    // 登录
    try {
      var value = wx.getStorageSync('user_openid');
      if (!value) {
        wx.login({
          success: function (res) {
            //发送给服务器的code 
            var code = res.code;
            console.log(code);
            wx.getUserInfo({
              success: function (res) {
                //用户昵称
                var userNick = res.userInfo.nickName;
                //用户头像地址
                var avataUrl = res.userInfo.avatarUrl;
                //用户性别
                var gender = res.userInfo.gender;
                if (code) {
                  wx.request({
                    //服务器的地址，现在微信小程序只支持https请求，所以调试的时候请勾选不校监安全域名
                    url: 'http://localhost/login',
                    data: {
                      code: code,
                      nick: userNick,
                      avaurl: avataUrl,
                      sex: gender,
                    },
                    header: {
                      'content-type': 'application/json'
                    },
                    method: 'POST',
                    success: function (res) {
                      console.log(res.data);
                      //将获取信息写入本地缓存
                      wx.setStorageSync('user_nick', res.data.name);
                      wx.setStorageSync('user_openid', res.data.openid);
                      wx.setStorageSync('user_avatar', res.data.imgurl);
                      wx.setStorageSync('user_gender', res.data.sex);
                    }
                  })
                }
                else {
                  console.log("获取用户登录态失败！");
                }
              }
            })
          },
          fail: function (error) {
            console.log('login failed ' + error);
          }
        })
      }
    } catch (e) {
      console.log(e);
    }
  },

  getUserInfo: function (cb) {
    var that = this
    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      //调用登录接口
      wx.getUserInfo({
        withCredentials: false,
        success: function (res) {
          that.globalData.userInfo = res.userInfo
          typeof cb == "function" && cb(that.globalData.userInfo)
        }
      })
    }
  },
  
  globalData: {
    userInfo: null
  }
})