// 管理员登录页面表单验证
function doAdminLogin() {
    let user = $('#mail').val();
    let pwd = $('#password').val();
    if(!user || !pwd) {
        alert("请正确填写账号密码");
        return false;
    }
}

function doDelete(contentId) {
    let confirm = this.confirm("确定删除这条帖子？");
    if(confirm === true) {
        $.ajax({
           type: 'POST',
           url: '/doDelete',
           data: {
               'contentId': contentId
           },
           dataType: 'json',
           timeout: 5000,
           success: function (res) {
              if(res.status === 'success') {
                  alert(res.message);
                  location.reload();
              }
           }
        })
    }
}

function doBan(userId) {
    let confirm = this.confirm("确定封禁这个用户？");
    if(confirm === true) {
        $.ajax({
            type: 'POST',
            url: '/doBan',
            data: {
                'userId': userId
            },
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
                if(res.status === 'success') {
                    alert(res.message);
                    location.reload();
                }
            }
        })
    }
}

function doTop(contentId) {
    let confirm = this.confirm("确定置顶这条帖子？");
    if(confirm === true) {
        $.ajax({
            type: 'POST',
            url: '/doTop',
            data: {
                'contentId': contentId
            },
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
                if(res.status === 'success') {
                    alert(res.message);
                    location.reload();
                }
            }
        })
    }
}

function doDeleteTag(tag) {
    let confirm = this.confirm("确定删除这个标签？所有帖子这个标签将被删除！");
    if(confirm === true) {
        $.ajax({
            type: 'POST',
            url: '/doDeleteTag',
            data: {
                'tag': tag
            },
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
                if(res.status === 'success') {
                    alert(res.message);
                    location.reload();
                }
            }
        })
    }
}