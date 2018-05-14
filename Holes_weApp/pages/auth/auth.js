Page({
  data: {
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  onLoad: function () {
    // 查看是否授权
    wx.getSetting({
      success: function (res) {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称
          wx.getUserInfo({
            success: function (res) { },
            fail: function (res) {
              wx.showToast({
                title: '授权失败',
                icon: 'none'
              })
            }
          })
        }
      }
    })
  },
  bindGetUserInfo: function (e) {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  }
})