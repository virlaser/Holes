const app = getApp();

Page({

  data: {
    currentPage : 1,
    contents : []
  },

  onLoad: function (options) {
    app.userLogin();
    wx.showLoading({
      title: '查询中',
    })
    this.loadContent(1);
  },

  onPullDownRefresh: function () {
    app.userLogin();
    this.setData({
      'contents': [],
      currentPage: 1
    });
    wx.showLoading({
      title: '查询中',
    });
    this.loadContent(1);
  },

  onReachBottom: function () {
    var currentPage = this.data.currentPage;
    var newPage = currentPage + 1;
    this.setData({
      'currentPage' : newPage
    })
    this.loadContent(newPage);
  },

  onLikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    // 帖子id
    var contentId = callbackData.id;
    // 帖子在数组中的位置
    var index = callbackData.index;
    // 帖子列表
    var lists = that.data.contents;
    // 先把结果在页面展示，再调用后端逻辑
    lists[index].like_flag = lists[index].like_flag ? 0 : 1;
    lists[index].like_num = lists[index].like_flag ? (lists[index].like_num + 1) : (lists[index].like_num - 1);
    // 实时数据渲染
    that.setData({
      'contents': lists
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
        if (res.statusCode === 200) {}
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
    var index = callbackData.index;
    var lists = that.data.contents;
    lists[index].dislike_flag = lists[index].dislike_flag ? 0 : 1;
    lists[index].dislike_num = lists[index].dislike_flag ? (lists[index].dislike_num + 1) : (lists[index].dislike_num - 1);
    that.setData({
      'contents': lists
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
        if (res.statusCode === 200) {}
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常',
          icon: 'none'
        })
      }
    })
  },

  onDetailTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var index = callbackData.index;
    var data = that.data.contents[index];
    wx.navigateTo({
      url: '/pages/index/detail?data=' + JSON.stringify(data)
    })
  },

  loadContent: function (pageNum) {
    var that = this;
    var id = wx.getStorageSync('user_openid')
    wx.request({
      url: app.globalData.domain + '/api/user/activity?page=' + pageNum,
      data: {
        'user_openid': id
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        if (res.statusCode == "200") {
          var callBackData = res.data;
          var currentContents = that.data.contents;
          var newContents = currentContents.concat(callBackData);
          that.setData({
            contents: newContents
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