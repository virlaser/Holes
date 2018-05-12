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
    console.log(event);
    var that = this;
    var callbackData = event.currentTarget.dataset;
    // 帖子id
    var contentId = callbackData.id;
    // 当前用户是否为帖子点赞过
    var like_flag = callbackData.flag;
    var like_num = callbackData.num;
    like_flag = like_flag?0:1;
    like_num = like_flag?(like_num+1):(like_num-1);
    // 前端先更新数据渲染
    var data = that.data.rawData;
    data['like_flag'] = like_flag;
    data['like_num'] = like_num;
    that.setData({
      'rawData' : data
    })
    wx.request({
      url: app.globalData.domain + '/like',
      data: {
        'content_id' : contentId,
        'user_openid' : wx.getStorageSync('user_openid')
      },
      header: {
        'content-type' : 'application/json'
      },
      method : 'POST',
      success:function(res) {
        if(res.statusCode === 200) {
          console.log('点赞成功');
        }
      },
      fail: function(res) {
        wx.showToast({
          title: '网络异常',
        })
      }
    })
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