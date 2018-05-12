const app = getApp()

Page({

  data: {
    rawData: [],
    comments: [],
    currentPage: 1
  },

  onLoad: function (options) {
    this.setData({
      'rawData': JSON.parse(options.data)
    })
    this.loadComment(1);
  },

  onPullDownRefresh: function () {
    this.setData({
      'comments' : [],
      'currentPage' : 1
    })
    this.loadComment(1);
  },

  onReachBottom: function () {
    var currentPage = this.data.currentPage;
    var nextPage = currentPage + 1;
    this.setData({
      'currentPage' : nextPage
    })
    this.loadComment(nextPage);
  },

  onShareAppMessage: function () {
    return {
      title: "武理树洞",
      path: "/pages/index/index"
    }
  },

  onLikeTap: function(event) {

  },

  onDislikeTap: function(event) {

  },

  onCommentTap: function(event) {

  },

  onCommentLikeTap: function(event) {

  },

  loadComment: function (pageNum) {
    var that = this;
    var id = wx.getStorageSync('user_openid')
    wx.request({
      url: app.globalData.domain + '/api/square/detail?page=' + pageNum,
      data: {
        'user_openid': id,
        'content_id': that.data.rawData.id
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        console.log(res.data);
        if (res.statusCode == "200") {
          var callBackData = res.data;
          var currentComments = that.data.comments;
          var newComments = currentComments.concat(callBackData);
          that.setData({
            comments: newComments
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
      complete: function () {
        wx.stopPullDownRefresh();
      }
    })
  }
})