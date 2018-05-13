function formatTime(date) {
  var month = date.getMonth() + 1
  var day = date.getDate()
  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()
  return [month, day].map(formatNumber).join('-') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

function formatOffsetTime(d1, d2) {
  let date = new Date(d1);
  let date2 = new Date(d2);
  let diffDate = date2 - date;
  if (diffDate < 60 * 1000) {
    return '几秒前'
  } else if (diffDate < 3600 * 1000) {
    return parseInt(diffDate / 1000 / 60) + '分钟前'
  } else if (diffDate < 3600 * 1000 * 24) {
    return parseInt(diffDate / 1000 / 3600) + '小时前'
  } else if (diffDate < 3600 * 1000 * 24 * 3) {
    return parseInt(diffDate / 1000 / 3600 / 24) + '天前'
  } else {
    return formatTime(date);
  }
}

function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

function isArray(obj) {
  return typeof obj === 'object' && obj instanceof Array;
}

function cleanArray(arr) {
  if (arr instanceof Array) {
    arr = arr.filter((item) => item != null);
  } else {
    arr = [];
  }
  return arr;
}

module.exports = {
  formatTime,
  formatOffsetTime,
  formatNumber,
  isArray,
  cleanArray
}
