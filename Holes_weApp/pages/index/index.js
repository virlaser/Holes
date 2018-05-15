const app = getApp()

Page({

  data: {
    currentPage: 1,
    topContents: [],
    contents: [],
  },

  onLoad: function () {
    var that = this;
    // todo 应该改为同步操作
    app.userLogin();
    wx.showLoading({
      title: '正在查询树洞帖子',
    })
    // 首先查询要置顶的帖子，不分页
    wx.request({
      url: app.globalData.domain + '/top',
      data: {
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function(res) {
        if(res.statusCode == 200) {
          var callbackData = res.data;
          that.setData({
            topContents: callbackData
          })
        }
      }
    })
    // 之后查询普通帖子，分页
    this.loadContent(1);
  },

  // 用户下拉页面刷新是直接重新载入此页面
  onPullDownRefresh: function () {
    app.userLogin();
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },

  // 用户触底时将请求的分页页面加一
  onReachBottom: function () {
    var currentPage = this.data.currentPage;
    var newPage = currentPage + 1;
    this.setData({
      'currentPage': newPage
    })
    this.loadContent(newPage);
  },

  // 用户点击右上角分享
  onShareAppMessage: function () {
    return {
      title: "武理树洞",
      path: "/pages/index/index"
    }
  },

  onMoreTap: function (event) {
    let itemList = [];
    // 自己只能删除自己的帖子
    if (event.currentTarget.dataset.ismy === 1) {
      itemList.push('删除');
    }
    // 自己不能举报自己的帖子
    if (event.currentTarget.dataset.ismy === 0) {
      if (event.currentTarget.dataset.isreport === 0) {
        itemList.push('举报');
      } else if (event.currentTarget.dataset.isreport === 1) {
        itemList.push('已举报');
      }
    }
    wx.showActionSheet({
      itemList,
      itemColor: '#705f5d',
      success: function (res) {
        if (itemList[res.tapIndex] === '举报') {
          wx.request({
            url: app.globalData.domain + '/report',
            data: {
              'content_id': event.currentTarget.dataset.id,
              'user_openid': wx.getStorageSync('user_openid'),
            },
            header: {
              'content-type': 'application/json'
            },
            method: 'POST',
            success: function (res) {
              if (res.statusCode === 200) {
                var callbackData = res.data;
                if (callbackData.status === 'success') {
                  wx.showToast({
                    title: '举报成功',
                  })
                } else {
                  wx.showToast({
                    title: '举报失败',
                    icon: 'none'
                  })
                }
              }
            },
            fail: function (res) {
              wx.showToast({
                title: '举报失败',
                icon: 'none'
              })
            }
          })
        } else if (itemList[res.tapIndex] === '删除') {
          // 删除时需要用户确认
          wx.showModal({
            title: '系统提示',
            content: '确定删除？',
            confirmColor: '#7d4a2b',
            success: function (res) {
              if (res.confirm) {
                wx.request({
                  url: app.globalData.domain + '/delete',
                  data: {
                    'user_openid': wx.getStorageSync('user_openid'),
                    'content_id': event.currentTarget.dataset.id
                  },
                  header: {
                    'content-type': 'application/json'
                  },
                  method: 'POST',
                  success: function (res) {
                    // 删除后刷新页面
                    wx.reLaunch({
                      url: '/pages/index/index',
                    })
                  },
                  fail: function (res) {
                    wx.showToast({
                      title: '删除失败',
                      icon: 'none'
                    })
                  }
                })
              }
            }
          })
        }
      }
    })
  },

  // 用户点击点赞按钮
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

  // 用户点击点踩按钮
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

  // 用户点击帖子详情
  onDetailTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var index = callbackData.index;
    var data = that.data.contents[index];
    // 进入帖子详情的时候携带当前帖子的点赞、点踩、评论等数据
    wx.navigateTo({
      url: '/pages/index/detail?data=' + JSON.stringify(data)
    })
  },

  // 用户点击帖子详情
  onTopDetailTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var index = callbackData.index;
    var data = that.data.topContents[index];
    // 进入帖子详情的时候携带当前帖子的点赞、点踩、评论等数据
    wx.navigateTo({
      url: '/pages/index/detail?data=' + JSON.stringify(data)
    })
  },

  // 分页加载函数
  loadContent: function (pageNum) {
    var that = this;
    var id = wx.getStorageSync('user_openid')
    wx.request({
      url: app.globalData.domain + '/api/square/index?page=' + pageNum,
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
