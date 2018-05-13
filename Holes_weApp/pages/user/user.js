const app = getApp();

Page({

  data: {
    myContent : 0,
    myActivity : 0,
    myNotification : 0,
    myDay : 0,
    nickName : wx.getStorageSync('user_nick'),
    userAvatar : wx.getStorageSync('user_avatar')
  },

  onLoad: function (options) {
    var that = this;
    app.userLogin();
    wx.request({
      url: app.globalData.domain + '/info',
      data : {
        'user_openid' : wx.getStorageSync('user_openid')
      },
      header:{
        'content-type' : 'application/json'
      },
      method : 'POST',
      success: function(res) {
        if(res.statusCode === 200) {
          var callbackData = res.data;
          // 用户发表帖子数
          var contentNum = callbackData.contentNum;
          // 用户动态数
          var activityNum = callbackData.activityNum;
          // 用户消息数
          var notificationNum = callbackData.notificationNum;
          // 用户注册时间
          var createTime = wx.getStorageSync('create_time');
          that.setData({
            myContent : contentNum,
            myActivity : activityNum,
            myNotification : notificationNum,
            // 计算用户来到树洞的第几天，使用时间戳计算
            myDay : parseInt((Date.parse(new Date()) - createTime*1000)/(60*60*24*1000))+2,
            nickName: wx.getStorageSync('user_nick'),
            userAvatar: wx.getStorageSync('user_avatar')
          })
        } else {
          wx.showToast({
            title: '未知错误',
            icon: 'none'
          })
        }
      },
      fail: function(res) {
        wx.showToast({
          title: '网络错误',
          icon: 'none'
        })
      }
    })
  },

  onPullDownRefresh: function () {
    app.userLogin();
    wx.reLaunch({
      url: '/pages/user/user',
    })
  },

})