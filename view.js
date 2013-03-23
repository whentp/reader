/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

window.cache = {
  items: {},
  outlineTitle: {}
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
  var max = a.attr('max')
  var url = '';
  if (a.hasClass('feed')){
    url = 'my/outlines/feed-read';
  } else if (a.hasClass('feeds')) {
    url = 'my/outlines/feeds-read';
  }

  $.post(url, {'id': id, 'max': max}, function(data){
      $('div.item').addClass('item-read');
      $('span.title-unread').removeClass('title-unread');
      getUnreadCount();
    }, 'json');
}

function getUnreadCount(){
  $.get('my/outlines/unread-count', {}, function(data){
      var dict = {};
      var dictmax = {};
      $.each(data, function(a, b){
          dict[b.id] = b.unread;
          dictmax[b.id] = b.max;
        });
      var sum_all = 0;
      var max_id_all = 0;
      $('a.feeds').each(function(){
          var sum = 0;
          var max_id = 0;
          var tmpobj = $(this);
          //console.log(tmpobj.html());
          tmpobj.parent().find('a.feed').each(function(){
              var obj = $(this);
              var id = obj.attr('data');
              if (dict[id] > 0){
                sum += dict[id]-0;
                if(max_id < dictmax[id]) max_id = dictmax[id];
                obj.addClass('boldfont').attr('max', dictmax[id]).find('span').html('('+dict[id]+')').attr('data', dict[id]);
              } else {
                obj.removeClass('boldfont').find('span').empty();
              }
            });
          if (sum>0){
            tmpobj.addClass('boldfont').attr('max', max_id).find('span.unread').html(' ('+sum+')');
          } else {
            tmpobj.removeClass('boldfont').find('span.unread').empty();
          }
          sum_all += sum;
          if(max_id_all < max_id) max_id_all = max_id;
        });

      if (sum_all>0){
        $('a.all').addClass('boldfont').attr('max', max_id_all).find('span.unread').html(' ('+sum_all+')');
      } else {
        $('a.all').removeClass('boldfont').find('span.unread').empty();
      }
      ;
    }, 'json');
}

function getFeedList(){
  $.get('my/outlines/all', {}, function(data){
      var outlineGroup = {};
      $.each(data, function(a, b){
          var k = b.outline;
          cache.outlineTitle[b.feed_id] = b.title;
          if(!outlineGroup[k]){
            outlineGroup[k]={
              title: k,
              id: b.outline_id,
              items: []
            };
          }
          outlineGroup[k].items.push(b);
        });
      $('div#nav').html($('#outlines').tmpl({obj:outlineGroup}));
      getUnreadCount();

      $('a.feeds, a.feed').off().click(function(){
          $('a.selected').removeClass('selected');
          $(this).addClass('selected');
          var url = $(this).attr('href');
          loadItems(url, -1, -1);
          return false;
        });
    }, 'json');
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
      $('div#list').append($('#items').tmpl(data));
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
          var content = $(this).next();
          content.removeClass('hidediv').empty().append($('#displaycontent').tmpl(tmpobj));
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

function resize(){
  var windowheight = $(window).height();
  var top = 40;
  $('#nav, #split, #main, #toolbar, #list').addClass('clearfix');
  $('#nav, #split, #main').height(windowheight - top);
  $('#list').height(windowheight - top - $('#toolbar').height());
  $('#list, #toolbar').width($(window).width() - $('#split').width() - $('#nav').width());
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


function init(){
  resize();

  $(document).ajaxStart(function() {
      showBottomLoader(true);
    })
  .ajaxStop(function() {
      showBottomLoader(false);
    });

  $('#refresh').click(getUnreadCount);
  $('#markread').click(markasread);
  $('#showall').change(function(){$('a.selected').click();});
  $('#list').scroll(function() {
      if (($(this).height() + this.scrollTop) >= this.scrollHeight) {
        //root.list.showBottomLoader(true);
        var jobj = $('a.selected');
        var url = jobj.attr('href');
        var id = $('div.item:last').attr('data');
        loadItems(url, id, cache.items[id].pubDate);
      }
    }).scrollTop(0);

  $(window).resize(resize);
  getFeedList();
}
