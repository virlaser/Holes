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
    console.log(userMail);
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
}

function doLogin() {
    let userMail = $('#mail').val();
    let pwd = $('#password').val();

    let mailReg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    console.log(userMail);
    if(!mailReg.test(userMail)) {
        alert("请填写正确邮箱");
        return false;
    }

    if(!pwd || pwd.length < 6) {
        alert("请正确填写密码");
        return false;
    }
}

function doCreate() {
    let content = $('#content').val();

    if(!content) {
        alert("请输入内容");
        return false;
    }
}

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
        timeout: 300,
        success: function () {
            if(type === 1) {
                let src = img.attr('src').slice(-6, -4);
                if(src === 'ed') {
                    img.attr('src', staticRes+'icon-like.png');
                    if(parseInt(p.html()) === 1) {
                        p.html('赞');
                    } else {
                        p.html(parseInt(p.html())-1);
                    }
                } else {
                    img.attr('src', staticRes+'icon-like-selected.png');
                    if(p.html() === '赞') {
                        p.html('1');
                    } else {
                        p.html(parseInt(p.html())+1);
                    }
                }
            }
            if(type === 2) {
                let src = img.attr('src').slice(-6, -4);
                if(src === 'ed') {
                    img.attr('src', staticRes+'icon-dislike.png');
                    if(parseInt(p.html()) === 1) {
                        p.html('踩');
                    } else {
                        p.html(parseInt(p.html())-1);
                    }
                } else {
                    img.attr('src', staticRes+'icon-dislike-selected.png');
                    if(p.html() === '踩') {
                        p.html('1');
                    } else {
                        p.html(parseInt(p.html())+1);
                    }
                }
            }
        },
        error: function () {
            alert("网络错误");
        }
    })
}

function doComment(contentId) {
    console.log('click doComment');
    window.location.href = '/comment?contentId=' + contentId;
}

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
            timeout: 300,
            success: function (res) {
                $('.submitBtn').attr('type', 'button');
                alert("评论成功");
            },
            error: function () {
                alert("网络错误");
            }
        });
        return false;
    }
}

function getDetail(event, contentId) {
    console.log('click getDetail');
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
        timeout: 300,
        success: function (res) {
            for(let i=0;i<res.length;i++) {
                let content = res[i];
                let avatar = "";
                let nickname = "";
                let like = content.like_flag === 0?(staticRes+"/icon-like.png"):(staticRes+"/icon-like-selected.png");
                let likeNum = content.like_num === 0?"赞":content.like_num;
                let dislike = content.dislike_flat === 0?(staticRes+"/icon-dislike.png"):(staticRes+"/icon-dislike-selected.png");
                let dislikeNum = content.dislike_num === 0?"踩":content.dislike_num;
                let comment = content.comment_flag === 0?(staticRes+"/icon-comment.png"):(staticRes+"/icon-comment-selected.png");
                let commentNum = content.comment_num === 0?"评论":content.comment_num;
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
                    "        <p class=\"content\" id=\"content\" onclick=\"getDetail(this, "+ content.id +")\">" + content.content + "</p>\n" +
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