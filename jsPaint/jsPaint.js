var jsPaint = (function(){
  var tools = [
    {
      icon: "Icon_Select_Rectangle.png",
      name: "Select Rectangle",
      tooltip: "<b>Rectangle Select:</b> Select a rectangular region"
    }, {
      icon: "Icon_Select_Ellipse.png",
      name: "Select Ellipse",
      tooltip: "<b>Ellipse Select:</b> Select an eliptical region"
    }, {
      icon: "Icon_Select_Lasso.png",
      name: "Free Select",
      tooltip: "<b>Free Select:</b> Select a rectangular region"
    }, {
      icon: "Icon_Draw_Pencil.png",
      name: "Pencil",
      tooltip: "<b>Pencil:</b> Draw freely"
    }, {
      icon: "Icon_Draw_Pen.png",
      name: "Pen",
      tooltip: "<b>Pen:</b> Create pen strokes"
    }, {
      icon: "Icon_Draw_Paintbrush.png",
      name: "Brush",
      tooltip: "<b>Brush:</b> Draw thick freeform lines"
    }, {
      icon: "Icon_Draw_Rectangle.png",
      name: "Rectangle",
      tooltip: "<b>Rectangle:</b> Draw thick freeform lines"
    }, {
      icon: "Icon_Draw_Circle.png",
      name: "Ellipse",
      tooltip: "<b>Ellipse:</b> Draw thick freeform lines"
    }
  ];
  
  function makePaint(id) {
    var res = '\n\
    <div id="jsPaint_toolbox' + id + '" class="jsPaint_toolbox">\n\
      <div class="jsPaint_toolpad">\n\
        ';
    
    for (var itemk in tools) item = tools[itemk],
      res +=
       '<button type="button" class="toolbutton">\n\
          <img src="images/' + item.icon + '" alt="RS" class="toolimage">\n\
          <span class="tooltext">' + item.name + '</span>\n\
        </button>';
    
    res += '\n\
      </div>\n\
    </div>\n\
    <div class="jsPaint_canvarea">\n\
      <canvas id="jsPaint_canvas' + id + '" class="jsPaint_canvas"></canvas>\n\
    </div>\n';
    
    return res;
  }
  
  function init(id, canvid) {
    var e = document.getElementById(id);
    if (!e) return;
    
    if (canvid == undefined)
      canvid = "_" + id;
    e.innerHTML = makePaint(canvid);
    
    var canvas = document.getElementById("jsPaint_" + canvid);
    
  }
  
  return {
    makePaint: makePaint,
    init: init
  };
})();

