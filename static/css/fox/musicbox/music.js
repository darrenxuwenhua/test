;;;(function($, global, undefined){
  $.cookie = function(k,v){
    if(arguments.length > 1){
      document.cookie = k + "="+ encodeURIComponent(v) +"; expires=Thu, 31 Jan 2999 16:00:00 GMT; path=/";
      return this;
    }else{
      var ck = document.cookie||"";
      var va = ck.split(/;\s*/g);
      for(var i = va.length; i--;){
        if(va[i].substr(0,k.length) == k){
          return decodeURIComponent(va[i].substr(k.length+1));
        }
      }
      return undefined;
    }
  };
  function tab_page($tab, page){
    var count = global.parseInt($tab.attr("pagecount"), 8);
    if(page <= 0){
      $tab.children(".prev").addClass("inactive");
    }else{
      $tab.children(".prev").removeClass("inactive");
    }
    if(page >= count - 1){
      $tab.children(".next").addClass("inactive");
    }else{
      $tab.children(".next").removeClass("inactive");
    }
    if(page == parseInt($tab.attr("page"),8)) return;
    $tab.attr("page",page).children(".item").hide().filter("[page="+page+"]").fadeIn("fast");
  };
  $("#player .tab").on("click", ".item", function(e){
    $(this).addClass("cur").siblings(".cur").removeClass("cur");
    $("#player .wrapper").slideDown();
    $.cookie("MUSIC_CUS", $(this).attr("pid"));
  });
  $("#player .tab").on("click", ".button", function(e){
    var $this = $(this);
    var pagecount = $this.attr("pagecount");
    e.preventDefault();
  });
  ;;;(function tab_init($, global, undefined){
    var pagetabs = $(".pagetab");
    pagetabs.each(function(idx,elem){
      var $elem = $(elem);
      var $prev = $elem.children(".prev"), $next = $elem.children(".next");
      var count = global.parseInt($elem.attr("pagecount"), 8);
      $prev.on("click", function(e){
        var page = global.parseInt($elem.attr("page"),8);
        if(--page >= 0){
          tab_page($elem, page);
        }else{ page = 0; }
      });
      $next.on("click", function(e){
        var page = global.parseInt($elem.attr("page"),8);
        if(++page < count){
          tab_page($elem, page);
        }else{ page = count - 1; }
      });
    });
  })($, global, undefined);
  ;;;(function cookie_load($, global, undefined){
    var children = $("#player .tab").children(".item");
    var urlp = global.location.search.match(/[&\?]provider=([^&]+)/)?RegExp.$1:"";
    if(urlp && children.filter("[pid='"+urlp+"']").length){
      var cus = urlp;
    }else{
      var cus = $.cookie("MUSIC_CUS");
      if(cus === ""){
        tab_page(children.parent(), 0);
        return false;
      }else if(cus === undefined){
        cus = children.attr("pid");
      }else if(children.filter("[pid='"+cus+"']").length < 1){
        cus = children.attr("pid");
      }
    }
    var child = children.filter("[pid='"+cus+"']").eq(0);
    tab_page(children.parent(), child.attr("page"));
    var href = child.attr("href");
    if(urlp) href += ((href.indexOf("?")>-1) ? "&" : "?") + global.location.search.replace(/[&\?]provider=[^&]+/,"").substr(1);
    child.attr("href", href).trigger("click");
    $("#player-frame").attr("src", href);
  })($, global, undefined);
  ;;;(function bind_link($, global, undefined){
    $("#player .tab").on("click", ".item", function(e){
      $("#player-frame").attr("src", this.href);
      e.preventDefault();
    });
  })($, global, undefined);
  $("#player .close").on("click", function(e){
    $("#player-frame").attr("src", "about:blank").fadeOut();
    $("#player .wrapper").slideUp(function(){$("#player-frame").show()});
    $("#player .tab").children(".cur").removeClass("cur");
    $.cookie("MUSIC_CUS", "");
    e.preventDefault();
  });
  $("#mainlists .tab").on("mouseover", ".item", function(){
      var $this = $(this);
      $this.addClass("cur").siblings().removeClass("cur");
      $("#mainlists .page[key='" + $this.attr("key") + "']").addClass("cur").siblings().removeClass("cur");
  });
})(jQuery, this);
;;;(function data($, global, undefined){
  var playurl = "http://ting.baidu.com/box?__methodName=mboxCtrl.playSong&__argsValue=";
  global.music = {
    list: $.extend(
      function(key,data){
        var $page = $("#mainlists .page[key='"+key+"']");
        $page.empty();
        var cnt = global.Math.min(data.song_list.length,8);
        var arr = [];
        for(var i = 0; i < cnt; i++){
          var li = $('<li'+(i<3?' class="hilight"':'')+'>' +
            '<i>'+(i+1)+'</i><b><a href="' +
            playurl + data.song_list[i].song_id + '" target="_blank">'+
            data.song_list[i].title +'</a></b><p><a href="'+
            playurl + data.song_list[i].song_id + '" target="_blank"></a></p>' +
            '</li>');
          arr.push(data.song_list[i].song_id);
          $page.append(li);
        }
        var li=$('<li class="playall"><a href="'+playurl+arr.join('_')+'" target="_blank" class="button"></a></li>');
        $page.append(li);
      },{
        "hotest":function(data){ this("hot", data); },
        "newest":function(data){ this("new", data); },
        "ktv":function(data){ this("ktv", data); },
        "tv":function(data){ this("tv", data); }
      }
    )
  };
  $.getScript("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.billboard.billList&type=2&format=jsonp&callback=music.list.hotest");
  $.getScript("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.billboard.billList&type=1&format=jsonp&callback=music.list.newest");
  $.getScript("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.billboard.billList&type=6&format=jsonp&callback=music.list.ktv");
  $.getScript("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.billboard.billList&type=14&format=jsonp&callback=music.list.tv");
})(jQuery, this);
