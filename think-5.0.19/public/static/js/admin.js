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

}

function doBan(contentId) {

}

function doTop(contentId) {

}