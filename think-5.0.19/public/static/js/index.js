// 用户注册页面字段验证
function doRegister() {
    let userName = $('#name').val();
    let userMail = $('#mail').val();
    let pwd1 = $('#pwd1').val();
    let pwd2 = $('#pwd2').val();
    if(!userName) {
        alert("请填写用户名");
        return false;
    }
    let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if(!mailReg.test(userMail)) {
        alert("请填写正确邮箱");
        return false;
    }
    if(!pwd1 || !pwd2) {
        alert("请填写密码");
        return false;
    } else {
        if(pwd1 === pwd2) {
            if(pwd1.length < 6) {
                alert("请填写六位以上密码");
                return false;
            }
        } else {
            alert("两次填写密码不一致");
            return false;
        }
    }
    alert("请点击确定后在此页面耐心等候，您的账号激活链接将会发送到您的邮箱");
}

// 用户登录页面表单验证
function doLogin() {
    let userMail = $('#mail').val();
    let pwd = $('#password').val();
    let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if(!mailReg.test(userMail)) {
        alert("请填写正确邮箱");
        return false;
    }
    if(!pwd || pwd.length < 6) {
        alert("请正确填写密码");
        return false;
    }
}

// 用户发帖页面表单验证
function doCreate() {
    let content = $('#content').val();
    if(!content) {
        alert("请输入内容");
        return false;
    }
}

// 用户点赞，点踩ajax
function doVote(event, contentId, type) {
    let img = $(event).find('img');
    let p = $(event).find('p');
    let staticRes = "/static/images/";
    $.ajax({
        type: 'POST',
        url: '/operate',
        data: {
            'contentId': contentId,
            'type': type
        },
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            if(res.status === 'success') {
                // 页面显示状态更改
                // 给帖子点赞 给评论点赞
                if (type === 1 || type === 5) {
                    let src = img.attr('src').slice(-6, -4);
                    if (src === 'ed') {
                        img.attr('src', staticRes + 'icon-like.png');
                        if (parseInt(p.html()) === 1) {
                            p.html('赞');
                        } else {
                            p.html(parseInt(p.html()) - 1);
                        }
                    } else {
                        img.attr('src', staticRes + 'icon-like-selected.png');
                        if (p.html() === '赞') {
                            p.html('1');
                        } else {
                            p.html(parseInt(p.html()) + 1);
                        }
                    }
                }
                // 给帖子点踩 给评论点踩
                if (type === 2 || type === 6) {
                    let src = img.attr('src').slice(-6, -4);
                    if (src === 'ed') {
                        img.attr('src', staticRes + 'icon-dislike.png');
                        if (parseInt(p.html()) === 1) {
                            p.html('踩');
                        } else {
                            p.html(parseInt(p.html()) - 1);
                        }
                    } else {
                        img.attr('src', staticRes + 'icon-dislike-selected.png');
                        if (p.html() === '踩') {
                            p.html('1');
                        } else {
                            p.html(parseInt(p.html()) + 1);
                        }
                    }
                }
            }
        }
    })
}

// 用户评论页面跳转
function doComment(contentId) {
    window.location.href = '/comment?contentId=' + contentId;
}

// 用户评论数据发送
function sendComment(contentId) {
    let content = $('#content').val();
    let hide = $('#hide').is(":checked");
    if(!content) {
        alert("请输入内容");
        return false;
    } else {
        $.ajax({
            type: 'POST',
            url: '/doComment',
            data: {
                'contentId': contentId,
                'content': content,
                'hide': hide?1:0
            },
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
                if(res.status === 'success') {
                    // 禁用发送按钮
                    $('.submitBtn').attr('type', 'button');
                    // todo 刷新页面
                    alert("评论成功，请返回刷新");
                    window.history.go(-1);
                } else {
                    alert("网络错误");
                }
            },
            error: function () {
                alert("网络错误");
            }
        });
        return false;
    }
}

// 帖子详细信息页面跳转
function getDetail(event, contentId) {
    window.location.href = '/detail?contentId=' + contentId;
}

// 获取滚动条当前的位置
function getScrollTop() {
    let scrollTop = 0;
    if (document.documentElement && document.documentElement.scrollTop) {
        scrollTop = document.documentElement.scrollTop;
    }
    else if (document.body) {
        scrollTop = document.body.scrollTop;
    }
    return scrollTop;
}

//获取当前可视范围的高度
function getClientHeight() {
    let clientHeight = 0;
    if (document.body.clientHeight && document.documentElement.clientHeight) {
        clientHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight);
    }
    else {
        clientHeight = Math.max(document.body.clientHeight, document.documentElement.clientHeight);
    }
    return clientHeight;
}

//获取文档完整的高度
function getScrollHeight() {
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
}

// 主页帖子上拉加载
function doLoading() {
    let page = $('.header');
    let currentPage = parseInt(page.attr('id').split('-')[1]);
    let nextPage = currentPage + 1;
    page.attr('id', 'page-'+nextPage);
    let url = '/contentApi?page='+nextPage;
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {},
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let avatar = "";
                let nickname = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
                let tag = content.tag?('<i>#'+content.tag+'#&nbsp&nbsp</i>'):'';
                if(content.hide===0){
                    if(content.avatar){
                        avatar = content.avatar;
                    } else {
                        avatar = staticRes + '/default/avatar.jpg';
                    }
                    nickname = content.userV?content.nickname:'匿名';
                } else {
                    avatar = staticRes + '/default/avatar.jpg';
                    nickname = '匿名';
                }
                let html = "<div class=\"section\" >\n" +
                    "        <div class=\"user-info\">\n" +
                    "            <img src=\"" + avatar + "\"/>\n" +
                    "            <div class=\"middle\">\n" +
                    "                <div>\n" +
                    "                    <p class=\"nickname\">" + nickname + "</p>\n" +
                    "                </div>\n" +
                    "                <p>" + content.create_time + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + tag + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"like\" onclick=\"doVote(this," + content.id + ", 1)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"dislike\" onclick=\"doVote(this," + content.id +", 2)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment\" onclick=\"doComment(" + content.id + ")\">\n" +
                    "                <img src=\"" + comment + "\"/>\n" +
                    "                <p>" + commentNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.content-list').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 标签详细页面加载
function loadTag(tag) {
    let page = $('.header');
    let currentPage = parseInt(page.attr('id').split('-')[1]);
    let nextPage = currentPage + 1;
    page.attr('id', 'page-'+nextPage);
    let url = '/tagApi?page='+nextPage;
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {
            'tag' : tag
        },
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let avatar = "";
                let nickname = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
                let tag = content.tag?('<i>#'+content.tag+'#&nbsp&nbsp</i>'):'';
                if(content.hide===0){
                    if(content.avatar){
                        avatar = content.avatar;
                    } else {
                        avatar = staticRes + '/default/avatar.jpg';
                    }
                    nickname = content.userV?content.nickname:'匿名';
                } else {
                    avatar = staticRes + '/default/avatar.jpg';
                    nickname = '匿名';
                }
                let html = "<div class=\"section\" >\n" +
                    "        <div class=\"user-info\">\n" +
                    "            <img src=\"" + avatar + "\"/>\n" +
                    "            <div class=\"middle\">\n" +
                    "                <div>\n" +
                    "                    <p class=\"nickname\">" + nickname + "</p>\n" +
                    "                </div>\n" +
                    "                <p>" + content.create_time + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + tag + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"like\" onclick=\"doVote(this," + content.id + ", 1)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"dislike\" onclick=\"doVote(this," + content.id +", 2)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment\" onclick=\"doComment(" + content.id + ")\">\n" +
                    "                <img src=\"" + comment + "\"/>\n" +
                    "                <p>" + commentNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.content-list').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 帖子详情页面评论加载
function loadComment(contentId) {
    let page = $('.user-comment');
    let currentPage = parseInt(page.attr('id').split('-')[1]);
    let nextPage = currentPage + 1;
    page.attr('id', 'page-'+nextPage);
    let url = '/commentApi?page='+nextPage;
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {
            'contentId': contentId
        },
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let nickname = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                if(content.hide===0){
                    nickname = content.userV?content.nickname:'匿名';
                } else {
                    nickname = '匿名';
                }
                let html = "<div class=\"section\">\n" +
                    "        <p class=\"content\"><strong>" + nickname + " : </strong>" + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"comment-like\" onclick=\"doVote(this, " + content.id + ", 5)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment-dislike\" onclick=\"doVote(this, " + content.id + ", 6)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.user-comment').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 用户审帖数据发送
function doCheck(contentId, type) {
    $.ajax({
        type: 'POST',
        url: '/doCheck',
        data: {
            'contentId': contentId,
            'checkType': type === 1?'yes':'no'
        },
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            if(res.status === 'success') {
                window.location.reload();
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 我的帖子加载
function doLoadingMy() {
    let page = $('.header');
    let currentPage = parseInt(page.attr('id').split('-')[1]);
    let nextPage = currentPage + 1;
    page.attr('id', 'page-'+nextPage);
    let url = '/myApi?page='+nextPage;
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {},
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let avatar = "";
                let nickname = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
                let tag = content.tag?('<i>#'+content.tag+'#&nbsp&nbsp</i>'):'';
                if(content.hide===0){
                    if(content.avatar){
                        avatar = content.avatar;
                    } else {
                        avatar = staticRes + '/default/avatar.jpg';
                    }
                    nickname = content.userV?content.nickname:'匿名';
                } else {
                    avatar = staticRes + '/default/avatar.jpg';
                    nickname = '匿名';
                }
                let html = "<div class=\"section\" >\n" +
                    "        <div class=\"user-info\">\n" +
                    "            <img src=\"" + avatar + "\"/>\n" +
                    "            <div class=\"middle\">\n" +
                    "                <div>\n" +
                    "                    <p class=\"nickname\">" + nickname + "</p>\n" +
                    "                </div>\n" +
                    "                <p>" + content.create_time + "</p>\n" +
                    "            </div>\n" +
                    "            <img src=\""+ staticRes +"/icon-more.png\" class=\"icon-more\"/>\n" +
                    "        </div>\n" +
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + tag + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"like\" onclick=\"doVote(this," + content.id + ", 1)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"dislike\" onclick=\"doVote(this," + content.id +", 2)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment\" onclick=\"doComment(" + content.id + ")\">\n" +
                    "                <img src=\"" + comment + "\"/>\n" +
                    "                <p>" + commentNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.content-list').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 删除帖子
function doDelete(event, contentId) {
    let confirm = this.confirm("确定删除这条帖子？");
    let section = $(event).parent().parent('.section');
    if(confirm === true) {
       $.ajax({
          type: 'POST',
          url: '/delete',
          data: {
              'contentId': contentId
          },
          dataType: 'json',
          timeout: 5000,
          success: function (res) {
              if(res.status === 'success') {
                  section.remove();
              }
          }
       })
    }
}

// 加载我的动态
function doLoadingActive() {
    let page = $('.header');
    let currentPage = parseInt(page.attr('id').split('-')[1]);
    let nextPage = currentPage + 1;
    page.attr('id', 'page-'+nextPage);
    let url = '/activeApi?page='+nextPage;
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {},
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let avatar = "";
                let nickname = "";
                let operate = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
                let tag = content.tag?('<i>#'+content.tag+'#&nbsp&nbsp</i>'):'';
                if(content.hide===0){
                    if(content.avatar){
                        avatar = content.avatar;
                    } else {
                        avatar = staticRes + '/default/avatar.jpg';
                    }
                    nickname = content.userV?content.nickname:'匿名';
                } else {
                    avatar = staticRes + '/default/avatar.jpg';
                    nickname = '匿名';
                }
                if(content['count(hole_content.id)']>1) {
                    operate = "我操作过：";
                } else {
                    if(content.type === 1) {
                        operate = "我点赞过：";
                    }
                    if(content.type === 2) {
                        operate = "我点踩过：";
                    }
                    if(content.type === 3) {
                        operate = "我评论过：";
                    }
                    if(content.type === 4) {
                        operate = "我举报过";
                    }
                }
                let html = "<div class=\"section\" >\n" +
                    "<div class=\"activity\">"+ operate +"</div>\n"+
                    "        <div class=\"user-info\">\n" +
                    "            <img src=\"" + avatar + "\"/>\n" +
                    "            <div class=\"middle\">\n" +
                    "                <div>\n" +
                    "                    <p class=\"nickname\">" + nickname + "</p>\n" +
                    "                </div>\n" +
                    "                <p>" + content.create_time + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + tag + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"like\" onclick=\"doVote(this," + content.id + ", 1)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"dislike\" onclick=\"doVote(this," + content.id +", 2)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment\" onclick=\"doComment(" + content.id + ")\">\n" +
                    "                <img src=\"" + comment + "\"/>\n" +
                    "                <p>" + commentNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.content-list').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 加载我的通知
function doLoadingInfo() {
    let url = '/infoApi';
    let staticRes = '/static/images';
    $.ajax({
        type: 'GET',
        url: url,
        data: {},
        dataType: 'json',
        timeout: 5000,
        success: function (res) {
            let contents = res.contentList;
            let myNick = res.myNick;
            for(let i=0;i<contents.length;i++) {
                let content = contents[i];
                let avatar = "";
                let nickname = content.nickname?content.nickname:"匿名";
                let message = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flag === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
                let tag = content.tag?('<i>#'+content.tag+'#&nbsp&nbsp</i>'):'';
                if(content.hide===0){
                    if(content.avatar){
                        avatar = content.avatar;
                    } else {
                        avatar = staticRes + '/default/avatar.jpg';
                    }
                } else {
                    avatar = staticRes + '/default/avatar.jpg';
                    myNick = '匿名';
                }
                if(content.type === 1) {
                    message = nickname + "赞过我的帖子：";
                }
                if(content.type === 3) {
                    message = nickname + "评论过我的帖子：";
                }
                let html = "<div class=\"section\" >\n" +
                    "<div class=\"activity\">"+ message +"</div>\n"+
                    "        <div class=\"user-info\">\n" +
                    "            <img src=\"" + avatar + "\"/>\n" +
                    "            <div class=\"middle\">\n" +
                    "                <div>\n" +
                    "                    <p class=\"nickname\">" + nickname + "</p>\n" +
                    "                </div>\n" +
                    "                <p>" + content.create_time + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + tag + content.content + "</p>\n" +
                    "        <div class=\"control-block\">\n" +
                    "            <div class=\"button\" id=\"like\" onclick=\"doVote(this," + content.id + ", 1)\">\n" +
                    "                <img src=\"" + like + "\"/>\n" +
                    "                <p>" + likeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"dislike\" onclick=\"doVote(this," + content.id +", 2)\">\n" +
                    "                <img src=\""+ dislike + "\"/>\n" +
                    "                <p>" + dislikeNum + "</p>\n" +
                    "            </div>\n" +
                    "            <div class=\"button\" id=\"comment\" onclick=\"doComment(" + content.id + ")\">\n" +
                    "                <img src=\"" + comment + "\"/>\n" +
                    "                <p>" + commentNum + "</p>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>";

                $('.content-list').append($(html));
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

// 找回密码表单验证
function doFind() {
    let mail = $('#mail').val();
    let captcha = $('#captcha').val();
    let pwd1 = $('#pwd1').val();
    let pwd2 = $('#pwd2').val();
    let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if(!mailReg.test(mail)) {
        alert("请填写正确邮箱");
        return false;
    }
    if(!captcha) {
        alert("请填写验证码");
        return false;
    }
    if(captcha.length !== 4) {
        alert("请正确填写验证码");
        return false;
    }
    if(!pwd1 || !pwd2) {
        alert("请填写密码");
        return false;
    } else {
        if(pwd1 === pwd2) {
            if(pwd1.length < 6) {
                alert("请填写六位以上密码");
                return false;
            }
        } else {
            alert("两次填写密码不一致");
            return false;
        }
    }
}

// 发送验证码
function doCaptcha() {
    let userMail = $('#mail').val();
    let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    if(!mailReg.test(userMail)) {
        alert("请填写正确邮箱");
        return false;
    }
    $(".captchaBtn").attr('onclick', '#');
    alert("验证码将发送到您的邮箱，请在此页面耐心等候");
    $.ajax({
        type: 'POST',
        url: '/captcha',
        data: {
            'userMail': userMail
        },
        dataType: 'json',
        // 发送验证码时间较长，不做超时处理
        //timeout: 300,
        success: function (res) {
            alert(res.message);
        },
        error: function () {
            alert("网络错误，请稍后再试");
        }
    })
}

// 举报帖子
function doReport(contentId) {
    let confirm = this.confirm("确定举报这条帖子？");
    if(confirm) {
        $.ajax({
            type: 'POST',
            url: '/operate',
            data: {
                'type': 4,
                'contentId' : contentId
            },
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
               if(res.status === 'success') {
                   alert("举报成功");
               } else {
                   alert(res.message);
               }
            },
            error: function () {
                alert("网络错误");
            }
        })
    }
}

// 标签详情页面跳转
function doTag(tag) {
    let href = '/tag?tag=' + tag;
    window.location.href = href;
}

// 修改信息页面跳转
function getChange() {
    let href = '/upload';
    window.location.href = href;
}

// 个人信息修改页面上传头像
function doImage() {
    let inputImg = document.getElementById('inputImg');
    let reader = new FileReader();
    let img = new Image();
    let canvas = document.getElementById('canvasImg');
    let context = canvas.getContext('2d');
    reader.onload = function (e) {
        img.src = e.target.result;
    };
    inputImg.addEventListener('change', function (e) {
        let file = e.target.files[0];
        reader.readAsDataURL(file);
    });
    img.onload = function () {
        let w = this.width;
        let h = this.height;
        let width = w;
        let height = h;
        let size = 300;
        if(w>=h && w>size) {
            width = size;
            height = size/w*h;
        } else if(w<h && h>size) {
            height = size;
            width = size/h*w;
        }
        canvas.width = width;
        canvas.height = height;
        context.clearRect(0, 0, width, height);
        context.drawImage(img, 0, 0, width, height);
        canvas.toBlob(function (blob) {
            $(".wrapper img").remove();
            $(".wrapper canvas").prop('hidden', false);
            $.ajax({
                type: 'POST',
                url: '/doUpload',
                data: blob,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 5000,
                success: function (res) {
                    if(res.status === 'success') {
                        alert("头像上传成功");
                    }
                },
                error: function () {
                    alert("头像上传失败，请稍后再试");
                }
            })
        })
    }
}

// 修改信息页面表单验证
function doChange() {
    let userName = $('#name').val();
    if(!userName) {
        alert("请填写用户名");
        return false;
    }
}