//index.js
//获取应用实例
const app = getApp()

Page({

  data: {
    currentPage: 1,
    contents: [],
  },

  onLoad: function () {
    wx.showLoading({
      title: '正在查询树洞帖子',
    })
    this.loadContent(1);
  },

  onPullDownRefresh: function () {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },

  onReachBottom: function () {
    var currentPage = this.data.currentPage;
    var newPage = currentPage + 1;
    this.setData({
      'currentPage': newPage
    })
    this.loadContent(newPage);
  },

  onShareAppMessage: function () {
    return {
      title: "武理树洞",
      path: "/pages/index/index"
    }
  },

  onMoreTap: function (event) {
    console.log(event);
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
                  })
                }
              }
            },
            fail: function (res) {
              wx.showToast({
                title: '举报失败',
              })
            }
          })
        } else if (itemList[res.tapIndex] === '删除') {
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
                    console.log(res);
                    wx.reLaunch({
                      url: '/pages/index/index',
                    })
                  },
                  fail: function (res) {
                    wx.showToast({
                      title: '删除失败',
                      icon: 'error'
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

  onLikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    // 帖子id
    var contentId = callbackData.id;
    // 帖子在数组中的位置
    var index = callbackData.index;
    console.log(index);
    // 帖子列表
    var lists = that.data.contents;
    // 先把结果在页面展示，再调用后端逻辑
    lists[index].like_flag = lists[index].like_flag?0:1;
    lists[index].like_num = lists[index].like_flag?(lists[index].like_num+1):(lists[index].like_num-1);
    // 实时数据渲染
    that.setData({
      'contents' : lists
    })
    wx.request({
      url: app.globalData.domain + '/like',
      data:{
        'content_id': contentId,
        'user_openid': wx.getStorageSync('user_openid')
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function(res) {
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

  onDislikeTap: function (event) {
    var that = this;
    var callbackData = event.currentTarget.dataset;
    var contentId = callbackData.id;
    var index = callbackData.index;
    var lists = that.data.contents;
    lists[index].dislike_flag = lists[index].dislike_flag?0:1;
    lists[index].dislike_num = lists[index].dislike_flag?(lists[index].dislike_num+1):(lists[index].dislike_num-1);
    that.setData({
      'contents' : lists
    })
    wx.request({
      url: app.globalData.domain + '/dislike',
      data:{
        'content_id' : contentId,
        'user_openid' : wx.getStorageSync('user_openid')
      },
      header: {
        'content-type' : 'application/json'
      },
      method: 'POST',
      success: function(res) {
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

  onDetailTap: function(event) {
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
      url: app.globalData.domain + '/api/square/index?page=' + pageNum,
      data: {
        'user_openid': id
      },
      header: {
        'content-type': 'application/json'
      },
      method: 'POST',
      success: function (res) {
        console.log(res.data);
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
