const app = getApp();

Page({

  data: {
    // 用户点击提交按钮后按钮禁用
    disabledSubmitBtn: false
  },

  doSubmit: function (event) {
    var that = this;
    var callbackData = event.detail.value;
    // 用户评论内容
    var content = callbackData.content;
    // 用户评论是否匿名
    var isAnoymous = callbackData.anoymous;
    if (typeof content === 'string' && (content.length == 0 || content.length > 800)) {
      return wx.showToast({
        title: '内容有误',
        icon: 'none'
      })
    }
    wx.request({
      url: app.globalData.domain + '/create',
      data: {
        'content': content,
        'hide': isAnoymous,
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        if (res.statusCode === 200) {
          that.setData({
            'disabledSubmitBtn': true
          });
          wx.showToast({
            title: '发帖成功',
            icon: 'none'
          })
        }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      }
    })
  }

})