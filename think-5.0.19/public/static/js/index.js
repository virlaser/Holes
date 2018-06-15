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

}

function getDetail(event, contentId) {
    console.log('click getDetail');
    window.location.href = '/detail?contentId=' + contentId;
}