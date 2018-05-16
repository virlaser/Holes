const app = getApp()

Page({

  data: {
    // 从上个页面传来的帖子信息
    rawData: [],
    // 帖子的评论信息
    comments: [],
    // 分页信息
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
      'comments': [],
      'currentPage': 1
    })
    this.loadComment(1);
  },

  onReachBottom: function () {
    var currentPage = this.data.currentPage;
    var nextPage = currentPage + 1;
    this.setData({
      'currentPage': nextPage
    })
    this.loadComment(nextPage);
  },

  onShareAppMessage: function () {
    return {
      title: "武理树洞",
      path: "/pages/index/index"
    }
  },

  onLikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    // 帖子id
    var contentId = callbackData.id;
    // 当前用户是否为帖子点赞过
    var like_flag = callbackData.flag;
    // 当前帖子喜欢人数
    var like_num = callbackData.num;
    like_flag = like_flag ? 0 : 1;
    like_num = like_flag ? (like_num + 1) : (like_num - 1);
    // 前端先更新数据渲染，然后发送请求给后端
    var data = that.data.rawData;
    data['like_flag'] = like_flag;
    data['like_num'] = like_num;
    that.setData({
      'rawData': data
    })
    wx.request({
      url: app.globalData.domain + '/like',
      data: {
        'content_id': contentId,
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        if (res.statusCode === 200) { }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      }
    })
  },

  onDislikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var contentId = callbackData.id;
    var dislike_flag = callbackData.flag;
    var dislike_num = callbackData.num;
    dislike_flag = dislike_flag ? 0 : 1;
    dislike_num = dislike_flag ? (dislike_num + 1) : (dislike_num - 1);
    var data = that.data.rawData;
    data['dislike_flag'] = dislike_flag;
    data['dislike_num'] = dislike_num;
    that.setData({
      'rawData': data
    })
    wx.request({
      url: app.globalData.domain + '/dislike',
      data: {
        'content_id': contentId,
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        if (res.statusCode === 200) { }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      }
    })
  },

  onCommentTap: function (event) {
    // 传递帖子信息到评论页面
    var data = this.data.rawData;
    wx.navigateTo({
      url: '/pages/index/reply?data=' + JSON.stringify(data),
    })
  },

  onCommentLikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var data = that.data.comments;
    // 评论下标
    var index = callbackData.index;
    var like_flag = data[index].like_flag;
    var like_num = data[index].like_num;
    // 评论id
    var comment_id = data[index].id;
    // 帖子id
    var content_id = data[index].content_id;
    // 点击按钮后按钮状态改变，先在前端展示
    like_flag = like_flag ? 0 : 1;
    like_num = like_flag ? (like_num + 1) : (like_num - 1);
    data[index]['like_flag'] = like_flag;
    data[index]['like_num'] = like_num;
    that.setData({
      'comments': data
    });
    wx.request({
      url: app.globalData.domain + '/commentlike',
      data: {
        'comment_id': comment_id,
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        if (res.statusCode === 200) { }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      }
    })
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
        if (res.statusCode == "200") {
          var callBackData = res.data;
          var currentComments = that.data.comments;
          var newComments = currentComments.concat(callBackData);
          that.setData({
            comments: newComments
          });
          wx.hideLoading();
        } else {
          wx.showToast({
            title: '网络异常',
            icon: 'none'
          })
        }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      },
      complete: function () {
        wx.stopPullDownRefresh();
      }
    })
  }
})