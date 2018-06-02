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