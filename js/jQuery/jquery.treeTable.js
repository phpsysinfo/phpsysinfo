/* 
Copyright: Paul Hanlon

Released under the MIT/BSD licence which means you can do anything you want 
with it, as long as you keep this copyright notice on the page 
*/
(function(jq){
  jq.fn.jqTreeTable=function(map, options){
    var opts = jq.extend({openImg:"",shutImg:"",leafImg:"",lastOpenImg:"",lastShutImg:"",lastLeafImg:"",vertLineImg:"",blankImg:"",collapse:false,column:0,striped:false,highlight:false,state:true},options),
    mapa=[],mapb=[],tid=this.attr("id"),collarr=[],
	stripe=function(){
      if(opts.striped){
  	    $("#"+tid+" tr:not(.collapsed)").filter(":even").addClass("even").removeClass("odd").end().filter(":odd").removeClass("even").addClass("odd");
      }
	},
    buildText = function(parno, preStr){//Recursively build up the text for the images that make it work
      var mp=mapa[parno], ro=0, pre="", pref, img;
      for (var y=0,yl=mp.length;y<yl;y++){
        ro = mp[y];
        if (mapa[ro]){//It's a parent as well. Build it's string and move on to it's children
          pre=(y==yl-1)? opts.blankImg: opts.vertLineImg;
          img=(y==yl-1)? opts.lastOpenImg: opts.openImg;
          mapb[ro-1] = preStr + '<span class="treeimg"><img src="'+img+'" class="parimg" id="'+tid+ro+'"></span>';
          pref = preStr + '<span class="treeimg"><img src="'+pre+'" class="preimg"></span>';
          arguments.callee(ro, pref);
        }else{//it's a child
          img = (y==yl-1)? opts.lastLeafImg: opts.leafImg;//It's the last child, It's child will have a blank field behind it
          mapb[ro-1] = preStr + '<span class="treeimg"><img src="'+img+'" class="ttimage" id="'+tid+ro+'"></span>';
        }
      }
    },
    expandKids = function(num, last){//Expands immediate children, and their uncollapsed children
      jq("#"+tid+num).attr("src", (last)? opts.lastOpenImg: opts.openImg);//
      for (var x=0, xl=mapa[num].length;x<xl;x++){
        var mnx = mapa[num][x];
        jq("#"+tid+mnx).parents("tr").removeClass("collapsed");
  			if (mapa[mnx] && opts.state && jq.inArray(mnx, collarr)<0){////If it is a parent and its number is not in the collapsed array
          arguments.callee(mnx,(x==xl-1));//Expand it. More intuitive way of displaying the tree
        }
      }
    },
    collapseKids = function(num, last){//Recursively collapses all children and their children and change icon
    if (mapa[num]){
        jq("#"+tid+num).attr("src", (last)? opts.lastShutImg: opts.shutImg);
        for (var x=0, xl=mapa[num].length;x<xl;x++){
          var mnx = mapa[num][x];
          jq("#"+tid+mnx).parents("tr").addClass("collapsed");
          if (mapa[mnx]){//If it is a parent
            arguments.callee(mnx,(x==xl-1));
          }
        }
      }
    },
  	creset = function(num, exp){//Resets the collapse array
  		var o = (exp)? collarr.splice(jq.inArray(num, collarr), 1): collarr.push(num);
      cset(tid,collarr);
  	},
  	cget = function(n){
	  	var v='',c=' '+document.cookie+';',s=c.indexOf(' '+n+'=');
	    if (s>=0) {
	    	s+=n.length+2;
	      v=(c.substring(s,c.indexOf(';',s))).split("|");
	    }
	    return v||[];
  	},
    cset = function (n,v) {
  		v = arrUniq(v);
	  	document.cookie = n+"="+v.join("|")+";";
	},
    arrUniq = function(a) {/* http://www.shamasis.net/2009/09/fast-algorithm-to-find-unique-items-in-javascript-array/ */
      var o = {}, i, l = a.length, r = [];
      for(i=0; i<l;i++) o[a[i]] = a[i];
        for(i in o) r.push(o[i]);
          return r;
	};

	var imgtmp = new Image(); //path normalize for ie6
	imgtmp.src = opts.lastOpenImg;
	opts.lastOpenImg = imgtmp.src;
	
	imgtmp.src = opts.lastShutImg;
	opts.lastShutImg = imgtmp.src;
	
	for (var x=0,xl=map.length; x<xl;x++){//From map of parents, get map of kids
      num = map[x];
      if (!mapa[num]){
        mapa[num]=[];
      }
      mapa[num].push(x+1);
    }
    buildText(0,"");
    jq("tbody tr", this).each(function(i){//Inject the images into the column to make it work
      jq(this).children("td").eq(opts.column).prepend(mapb[i]);
    });
    if(opts.state) collarr = cget(tid);
    if (!collarr.length){
        if(opts.collapse.constructor == Array) {
            collarr=collarr.concat(opts.collapse);
            if ((collarr.length) && (opts.state)){
                 cset(tid,collarr);
            }
        }
    }
    if (collarr.length){
        for (var y=0,yl=collarr.length;y<yl;y++){
            collapseKids(collarr[y], $("#"+tid+collarr[y]+ ".parimg").attr("src")==opts.lastOpenImg);
        }
    }
    stripe();
    jq(".parimg", this).each(function(i){
      var jqt = jq(this),last;
      jqt.click(function(){
        var num = parseInt(jqt.attr("id").substr(tid.length));//Number of the row
        if (jqt.parents("tr").next().is(".collapsed")){//If the table row directly below is collapsed
          expandKids(num, (jqt.attr("src")==opts.lastShutImg));//Then expand all children not in collarr
					if(opts.state){creset(num,true);}//If state is set, store in cookie
        }else{//Collapse all and set image to opts.shutImg or opts.lastShutImg on parents
          collapseKids(num, (jqt.attr("src")==opts.lastOpenImg));
					if(opts.state){creset(num,false);}//If state is set, store in cookie
        }
        stripe();//Restripe the rows
      });
    });
    if (opts.highlight){//This is where it highlights the rows
      jq("tr", this).hover(
        function(){jq(this).addClass("over");},
        function(){jq(this).removeClass("over");}
      );
    };
  };
  return this;
})(jQuery);
