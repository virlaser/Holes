const app = getApp();

Page({


  data: {
    disabledSubmitBtn : false
  },

  doSubmit: function(event) {
    var that = this;
    console.log(event);
    var callbackData = event.detail.value;
    var content = callbackData.content;
    var isAnoymous = callbackData.anoymous;
    if(typeof content === 'string' && (content.length == 0 || content.length > 800)) {
      return app.showErrorMsg({
        title: '内容有误'
      })
    }
    wx.request({
      url: app.globalData.domain + '/create',
      data: {
        'content' : content,
        'hide' : isAnoymous,
        'user_openid' : wx.getStorageSync('user_openid')
      },
      header: {
        'content-type' : 'application/json'
      },
      method : 'POST',
      success: function(res) {
        if(res.statusCode === 200) {
          console.log("创建帖子成功");
          that.setData({
            'disabledSubmitBtn' : true
          });
          wx.showToast({
            title: '发帖成功',
          })
        }
      },
      fail: function(res) {
        wx.showToast({
          title: '网络异常',
        })
      }
    })
  }


})