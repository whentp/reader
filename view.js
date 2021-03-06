/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

window.cache = {
  items: {},
  folderTitle: {},
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

function markasread(){
  var a = $('#nav a.selected');
  var id = a.attr('data');
  var max = cache.max_id;
  var url = '';
  if (a.hasClass('feed')){
    url = 'my/folders/feed-read';
  } else if (a.hasClass('feeds') && a.hasClass('all')) {
    url = 'my/folders/all-read';
  } else if (a.hasClass('feeds') && !a.hasClass('all')) {
    url = 'my/folders/feeds-read';
  }

  $.post(url, {'id': id, 'max': max}, function(data){
    $('div.item').addClass('item-read');
    $('span.title-unread').removeClass('title-unread');
    getUnreadCount();
  }, 'json');
}

function getUnreadCount(){
  $.get('my/folders/unread-count', {}, function(data){
    var dict = {};
    $.each(data, function(a, b){
      dict[b.id] = b.unread;
      cache.max_id = (cache.max_id < b.max)?b.max:cache.max_id;
    });
    var sum_all = 0;
    var max_id_all = 0;
    $('a.feeds').each(function(){
      var sum = 0;
      var tmpobj = $(this);
      tmpobj.parent().find('a.feed').each(function(){
        var obj = $(this);
        var id = obj.attr('data');
        if (dict[id] > 0){
          sum += dict[id]-0;
          obj.addClass('boldfont').find('span').html('('+dict[id]+')').attr('data', dict[id]);
        } else {
          obj.removeClass('boldfont').find('span').empty();
        }
      });
      if (sum>0){
        tmpobj.addClass('boldfont').find('span.unread').html(' ('+sum+')');
      } else {
        tmpobj.removeClass('boldfont').find('span.unread').empty();
      }
      sum_all -= 0;
      sum_all += sum-0;
    });
    setTitle(sum_all);
    if (sum_all>0){
      $('a.all').addClass('boldfont').find('span.unread').html(' ('+sum_all+')');
    } else {
      $('a.all').removeClass('boldfont').find('span.unread').empty();
    }
    ;
  }, 'json');
}

function setTitle(unread){
  var titleOrigin = 'Reader';
  var title = (unread>0)?('* '+titleOrigin+' ('+unread+')'):titleOrigin;
  document.title = title;
}

function getFeedList(){
  $.get('my/folders/all', {}, function(data){
    var folderGroup = {};
    var folderList = [];
    $.each(data, function(a, b){
      var k = b.folder;
      cache.folderTitle[b.feed_id] = b.title;
      if(!folderGroup[k]){
        folderList.push({
          title: k,
          id: b.folder_id,
          folded: b.folded
        });
        folderGroup[k]={
          title: k,
          id: b.folder_id,
          items: []
        };
      }
      folderGroup[k].items.push(b);
    });
    $('div#nav').html($('#folders').tmpl({folderlist: folderList, obj:folderGroup}));
    getUnreadCount();

    $('span.icon-folder').off().click(function(){
      $(this).parent().next().toggleClass('hidediv');
      var id = $(this).parent().attr('data');
      var folded = $(this).parent().next().hasClass('hidediv')?1:0;
      $.post('my/folders/fold', {'id': id , 'folded': folded});
      return false;
    });
    $('a.feeds, a.feed').off().click(function(){
      $('a.selected').removeClass('selected');
      $(this).addClass('selected');
      var url = $(this).attr('href');
      loadItems(url, -1, -1);

      var box = $('#mobilemode');
      var mobilemode = box.val() == 1;;
      if(mobilemode){
        $('#backtofeeds').css('display','inline');
        $('#nav, #split').css({width: 0});
        resize();
      }
      return false;
    });
    bindDrag();
  }, 'json');
  resize();
}

function loadItems(url, since_id, timestamp){
  var options = {unread: $('#showall').val()};
  var paging = (since_id > 0 & timestamp > 0)
  if(paging){
    options.since_id = since_id;
    options.timestamp = timestamp;
  } else {
    $('div#list').empty().html('<div class="item">Loading...</div>');
  }
  $.get(url, options,
    function(data){
      if(data.length == 0){
        if(!paging){
          $('div#list').html('No news is <b>REALLY GOOD</b>!!!');
          return false;
        }
      }
      $.each(data, function(a,b){
        cache.items[b.id] = b;
      });

      if(!paging){
        $('div#list').empty()
      }
      var itemtemplate = ($('#mobilemode').val() == 1)?'#itemsmobile':'#items';
      $('div#list').append($(itemtemplate).tmpl(data));
      $('div.unbinditem').removeClass('unbinditem').off().click(function(){
        if($(this).hasClass('item-reading')){
          $(this).next().addClass('hidediv').empty();
          return false;
        }
        var tmpobj = cache.items[$(this).attr('data')];
        var jobj = $(this);

        $.post('my/item/read', {id: tmpobj.id}, function(data){
          getUnreadCount();
          jobj.addClass('item-read');
          jobj.find('span.title-unread').removeClass('title-unread');
          //console.log(data);
        }, 'json');

        $('.item-reading').removeClass('item-reading');
        $('div.item-content').empty().addClass('hidediv');
        $(this).addClass('item-reading');

        // Scroll to the top
        var child = $(this);
        var parent = child.parent();
        parent.scrollTop(parent.scrollTop() + child.position().top - parent.position().top);

        var content = $(this).next();
        content.removeClass('hidediv').empty()
        .append($('#displaycontent').tmpl(tmpobj))
        .append($('#item-toolbar').tmpl(tmpobj));

        $('div.item-toolbar').find('input[name=unread]').click(function(){
          var tmpobj = cache.items[$(this).attr('data')];
          var checkbox = $(this);
          var unread = checkbox.is(':checked');
          var url = unread ?'my/item/unread':'my/item/read';
          $.post(url, {id: tmpobj.id}, function(data){
            getUnreadCount();
            var jobj = checkbox.parent().parent().prev();
            if(unread){
              jobj.find('div.title span:first').addClass('title-unread');
              jobj.removeClass('item-read');
            }else{
              jobj.addClass('item-read');
              jobj.find('span.title-unread').removeClass('title-unread');
            }
          }, 'json');
        });

        $('div.item-toolbar').find('input[name=shared]').click(function(){
          var tmpobj = cache.items[$(this).attr('data')];
          var checkbox = $(this);
          var shared = checkbox.is(':checked');
          var url = shared ?'my/item/share':'my/item/unshare';
          $.post(url, {id: tmpobj.id}, function(data){
            var jobj = checkbox.parent().parent().prev();
            if(shared){
              jobj.find('div.title span:first').after('<span class="item-shareicon">&nbsp;shared&nbsp;</spam>');
            }else{
              jobj.find('span.item-shareicon').remove();
            }
            return false;
          }, 'json');
        });
        return false;
      });
      $('div.markstar').off().click(function(){
        markstar($(this), $(this).attr('data'), $(this).hasClass('icon-unstarred'));
        return false;
      });
    }, 'json');
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

function resize(){
  var windowheight = $(window).height();
  var top = 40;
  $('#nav, #split, #main, #toolbar, #list').addClass('clearfix');
  $('#nav, #split, #main').height(windowheight - top);
  $('#list').height(windowheight - top - $('#toolbar').height());
  $('#list, #toolbar').width($(window).width() - $('#split').width() - $('#nav').width());
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

function showBottomLoader(show) {
  if(!$('#bottom-loader').size()){
    $('<div id="bottom-loader" style="display: none;"></div>').appendTo($('body'));
  }
  if (show) {
    var offset = $('#list').offset();
    var width = $('#list').width();
    var height = $('#list').height();
    $('#bottom-loader').css({
      top : ($(window).height() - 31),
      left : (offset.left + width / 2 - 40),
      display : 'block',
      visibility : 'visible'
    });
  } else {
    $('#bottom-loader').hide();
  }
}

function bindDrag(){
  $('#nav a.draggable').attr('draggable', 'true')
  .on('dragstart', function(ev) {
    feedDragStart(ev, $(this));
  })
  .on('dragend', function(ev) {
    return false;
  })
  .on('dragenter', function(ev) {
    $(ev.target).addClass('dragover');
    return false
  })
  .on('dragleave', function(ev) {
    $(ev.target).removeClass('dragover');
    return false;
  })
  .on('dragover', function(ev) {
    return false;
  })
  .on('drop', function(ev) {
    var obj = $(this);
    var dt = ev.originalEvent.dataTransfer;
    var tmp = JSON.parse(dt.getData('text/plain'));
    var data = {type: obj.hasClass('feeds')?1:0};
    data.feed = (data.type == 1)? -1 : obj.attr('data');
    data.folder = (data.type == 1)?obj.attr('data'):obj.attr('folder');

    $(ev.target).removeClass('dragover');
    if(tmp.type > data.type){
      alert('Sorry. category cannot be put into an item.');
    } else {
      data = {
        fromFolder: tmp.folder,
        toFolder: data.folder,
        fromFeed: tmp.feed,
        toFeed: data.feed
      };
      $.post('my/folders/order', data, function(data){
        getFeedList();
      }, 'json');
    }
    return false;
  });
}

function returnFalse(){
  return false;
}

function feedDragStart(ev, obj){
  var dt = ev.originalEvent.dataTransfer;
  var data = {type: obj.hasClass('feeds')?1:0};
  data.feed = (data.type == 1)? -1 : obj.attr('data');
  data.folder = (data.type == 1)?obj.attr('data'):obj.attr('folder');
  dt.setData("text/plain", JSON.stringify(data));
  return true;
}

function saveUserConfig(k, v){
  var p = {key:k, value: v};
  $.post('my/user/config', p, function(data){
    // nothing.
  },'json');
}

function cronjob(){
  $.get('cron.fetchfeed.php',{limit: 2},function (){getUnreadCount();});
}

function init(){
  resize();
  touchScroll();
  $('#backtofeeds').css('display','none');
  $('#backtofeeds').click(function(){
    var x = $('mobilemode').val();
    $('#nav').css({width:$(window).width()});
    $('#list').empty();
    resize();
    $('mobilemode').val(x);
    return false;
  });

  $('#mobilemode').change(function(){
    var box = $(this);
    box.val(box.val());
    var mobilemode = box.val() == 1;
    if(mobilemode){
      $('#backtofeeds').css('display','inline');
      $('#banner').find('a').css('display','none');
      $('#nav').css('width', $(window).width());
      resize();
    } else {
      $('#backtofeeds').css('display','none');
      $('#banner').find('a').css('display', 'inline');
      $('#nav').css({width: 200});
      $('#split').css({width:5});
      resize();
      $('a.selected').click();
    }
  });

  $(document).ajaxStart(function() {
    showBottomLoader(true);
  })
  .ajaxStop(function() {
    showBottomLoader(false);
  });

  $('a.add-feed').click(function(){
    var url = prompt('Enter the RSS address:');
    if(url){
      $.post('my/folders/add-feed', {'url':url}, function(data){
        if(data.code){
          getFeedList();
          alert('OK.');
        } else {
          alert('Failed');
        }
      },'json');
    }
    return false;
  });

  $('a.user-name').click(changeUserName);

  $.get('my/user/config', function(data){
    $('#showall').val(data.showall);
  },'json');

  $('#refresh').click(getUnreadCount);
  $('#markread').click(markasread);
  $('#showall').change(function(){
    $('a.selected').click(); 
    saveUserConfig('showall', $('#showall').val());
  });
  $('#list').scroll(function() {
    if (($(this).height() + this.scrollTop) >= this.scrollHeight) {
      var jobj = $('a.selected');
      var url = jobj.attr('href');
      var id = $('div.item:last').attr('data');
      if(!id || !cache.items[id]){  
        loadItems(url, -1, -1);
      } else {
        loadItems(url, id, cache.items[id].pubDate);
      }
    }
  }).scrollTop(0);
  $(window).resize(resize);
  getFeedList();
  setInterval(cronjob, 60*1000);
  setTimeout(resize, 1000);
}
