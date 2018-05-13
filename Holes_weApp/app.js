//app.js
App({
  onLaunch: function () {
    
  },

  userLogin: function () {
    // 登录
    // todo 异步执行操作会出现问题
    try {
      // 首先查询用户本地是否存储有用户标识
      var value = wx.getStorageSync('user_openid');
      var id = wx.getStorageSync('user_id');
      if (!value && !id) {
        wx.login({
          success: function (res) {
            //发送给服务器的code 
            var code = res.code;
            console.log(code);
            wx.getUserInfo({
              success: function (res) {
                console.log(res);
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
                      wx.setStorageSync('user_id', res.data.userid);
                      wx.setStorageSync('create_time', res.data.createtime);
                    }
                  })
                }
                else {
                  console.log("获取用户登录态失败！");
                }
              },
              fail: function (error) {
                wx.navigateTo({
                  url: '/pages/auth/auth',
                })
              }
            })
          },
          fail: function (error) {
            console.log('login failed ' + error);
          }
        })
      }
    } catch (e) {
      wx.showToast({
        title: '系统出错',
      })
      console.log(e);
    }
  },

  globalData: {
    userInfo: null,
    domain: 'http://localhost'
  }
})