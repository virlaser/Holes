//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    currentPage: 1,
    contents: []
  },

  onLoad: function () {
    wx.showLoading({
      title: '正在查询树洞帖子',
    })
    this.loadContent(1);
  },

  onPullDownRefresh: function() {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },

  onShareAppMessage: function () {
    return {
      title: "武理树洞",
      path: "pages/index/index"
    }
  },

  loadContent: function (pageNum) {
    var that = this;
    var id = wx.getStorageSync('user_openid')
    wx.request({
      url: app.globalData.domain + '/api/square/index?page=' + pageNum,
      data : {
        'user_openid' : id
      },
      header: {
        'content-type' : 'application/json'
      },
      method: 'post',
      success: function(res) {
        console.log(res.data);
        if(res.statusCode == "200") {
          var callBackData = res.data;
          var currentContents = that.data.contents;
          var newContents = currentContents.concat(callBackData);
          that.setData({
            contents: newContents
          });
          wx.hideLoading();
        } else {
          wx.showModal({
            title: '加载失败',
            content: '服务器出了点问题',
            showCancel: false,
            confirmText: '好的',
            confirmColor: '#EE8AB0'
          });
        }
      },
      fail: function (res) {
        wx.showModal({
          title: '加载失败',
          content: '服务器出了点问题',
          showCancel: false,
          confirmText: '好的',
          confirmColor: '#EE8AB0'
        });
      },
      complete: function() {
        wx.stopPullDownRefresh();
      }
    })
  }

})
