const app = getApp();

Page({

  data: {
    // 从帖子详情页面传过来的帖子信息
    rawData: [],
    // 用户点击提交按钮后就会禁用，防止用户多次提交
    disabledSubmitBtn: false
  },

  onLoad: function (options) {
    this.setData({
      'rawData': JSON.parse(options.data)
    })
  },

  doSubmit: function (event) {
    var that = this;
    var callbackData = event.detail.value;
    var content = callbackData.content;
    var isAnoymous = callbackData.anoymous;
    // 发帖用户id
    var userId = that.data.rawData.user_id;
    // 帖子id
    var contentId = that.data.rawData.id;
    if (typeof content === 'string' && (content.length === 0 || content.length > 800)) {
      return wx.showToast({
        title: '内容有误',
        icon: 'none'
      });
    }
    wx.request({
      url: app.globalData.domain + '/comment',
      data: {
        'content': content,
        'hide': isAnoymous,
        'content_id': contentId,
        'user_openid': wx.getStorageSync('user_openid'),
        'user_id': userId
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
            title: '评论成功',
            icon: 'none'
          });
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