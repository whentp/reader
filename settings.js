/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

window.cache = {
  items: {},
  outlineTitle: {},
  max_id: -1
};

$(function(){init();});

function toDateTime(unix_timestamp){
  var date = new Date(unix_timestamp*1000);
  return date.toLocaleString();
}

function getTitleContent(tmpstr){
  var x = tmpstr.replace(/<(?:.|\n)*?>/gm, '');
  x = x.replace(/[ \t\n\r]+/gm, ' ');
  return $.trim(x).substring(0, 500);
}

function setTitle(unread){
  var titleOrigin = 'Reader';
  var title = (unread>0)?('* '+titleOrigin+' ('+unread+')'):titleOrigin;
  document.title = title;
}

function markstar(jobj, id, star){
  var url = star?'my/item/star':'my/item/unstar';
  $.post(url, {'id': id}, function(data){
    jobj.addClass(star?'icon-starred':'icon-unstarred').removeClass((!star)?'icon-starred':'icon-unstarred');
  }, 'json');
  return false;
}

function isTouchDevice(){
  try{
    document.createEvent("TouchEvent");
    return true;
  }catch(e){
    return false;
  }
}

function touchScroll(){
  if(isTouchDevice()){
    $("#nav, #list").each(function(){
      var el=this;
      var scrollStartPos=0;

      el.addEventListener("touchstart", function(event) {
        scrollStartPos=this.scrollTop+event.touches[0].pageY;
      },true);

      el.addEventListener("touchmove", function(event) {
        this.scrollTop=scrollStartPos-event.touches[0].pageY;
        event.preventDefault();
      },true);
    });
  }
}

function changeUserName(){
  $.get('my/user/user-name', {}, function(data){
    if(data.code){
      var username = data.username;
      var newname = prompt('Enter your new name', username);
      if(newname.length){
        $.post('my/user/user-name', {'username': newname}, function(data){
          if(data.code){
            alert('Successful');
            window.location.reload();
          } else {
            alert('Sorry. Name Invalid.')
          }
        }, 'json');
      }
    }
  }, 'json');
  return false;
}

function returnFalse(){
  return false;
}

function saveUserConfig(k, v){
  var p = {key:k, value: v};
  $.post('my/user/config', p, function(data){
    // nothing.
  },'json');
}

var tmpvaluexxx;

function setOutline(items, outline, callback){
  $.post('my/outlines/feeds-outline', {feeds: items, 'outline': outline}, function(data){
    if(data.code){
      callback();
    }
  }, 'json');
}

function feedsRemove(items, callback){
  $.post('my/outlines/feeds-remove', {feeds: items}, function(data){
    if(data.code){
      callback();
    }
  }, 'json');
}

function getFeedList(tmpstr){
  $.get('my/outlines/all', {}, function(data){ 
    var outlinestr = tmpstr;
    $('#framework').html($('#feedlisttpl').tmpl({outlines: outlinestr, feeds:data}));

    $('table.feedlist td select').change(function(){
      var data = $(this).parent().attr('data');
      setOutline([data], $(this).val(), function(){});
    });

    $('table.feedlist td select').change(function(){
      var data = [];
      var tmpjobj = [];
      $('table.feedlist').find('input:checked').each(function(){
        data.push($(this).attr('data'));
        tmpjobj.push($(this));
      });
      if(data.length<=0) return;
      var value = $(this).val();
      setOutline([data], $(this).val(), function(){
        $.each(data, function(a, b){
          $('table.feedlist td[data='+b+'] select').val(value);
        });

      });
    });

    $('table.feedlist td.selectoutline').each(function(){
      $(this).find('select').val($(this).attr('datab'));
    });
    $('table.feedlist tr').hover(function(){
      $(this).addClass('hover');
    }, function(){
      $(this).removeClass('hover');
    });

    $('a.feeddelete').click(function(){
      var data = $(this).attr('data');
      var rootjobj = $(this);
      feedsRemove([data], function(){
        rootjobj.parent().parent().hide(100);
      });
      return false;
    });

    $('#newoutline').click(function(){
      var newname = prompt('Name')
      if(newname.length){
        $.post('my/outlines/add-outline', {outline:newname}, function(data){
          if(data.code){
            window.location.reload();
          } else {
            alert('error.');
          }
        }, 'json');
      }
    });

    $('#removeselected').click(function(){
      var data = [];
      var tmpjobj = [];
      $('table.feedlist').find('input:checked').each(function(){
        data.push($(this).attr('data'));
        tmpjobj.push($(this));
      });
      if(data.length<=0) return;
      feedsRemove([data], function(){
        $.each(tmpjobj, function(a,b){
          b.parent().parent().hide(100);
        });
      });
    });
  }, 'json');
}

function init(){
  $.get('my/outlines/all-outlines', {}, function(data){
    var outlines = {};
    $.each(data, function(a,b){
      outlines[''+b.id] = b.text;
    });
    var outlinestr = $($('#outlinetpl').tmpl({'outlines': outlines})).wrapAll('<div></div>').parent().html();
    getFeedList(outlinestr);
  }, 'json');
}
