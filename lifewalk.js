/*
Copyright (C) 2018 Josh Ventura

This script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

For more details, see <https://www.gnu.org/licenses/>.
*/

var DIR = 'img/soft-scraps-icons/';

var nodes = null;
var edges = null;
var network = null;

function node(id, image, size, content, paragraphs, x0, y0) {
  return {
    id: id,
    shape: 'circularImage',
    image: image,
    color: "#FFF",
    size: size,
    mass: size / 16,
    x: x0,
    y: y0,
  }
}

function only(x) {
  if (x.length != 1) {
    console.log("Error: supplied object is not a singleton array:");
    console.log(x);
  }
  return x[0];
}

const VISIBLE_CLASS = "TLEntry graphbox-visible";
const HIDDEN_CLASS = "TLEntry graphbox-hidden";

// Called when the Visualization API is loaded.
function init_canvas() {
  // create people.
  // value corresponds with the age of the person
  
  const tl_data = compile_timeline();
  nodes = new vis.DataSet(tl_data.nodes);
  edges = new vis.DataSet(tl_data.edges);
  
  // create a network
  var container = document.getElementById('visualization');
  container.className = "";
  const cheight = Math.floor(container.offsetWidth * 1.25) + "px";
  container.style.height = cheight;
  console.log(cheight)
  
  var data = {
    nodes: nodes,
    edges: edges
  };
  var options = {
    nodes: {
      borderWidth:8,
      size:30,
      color: {
        border: '#406897',
        background: '#6AAFFF'
      },
      font:{color:'#eeeeee'},
      shapeProperties: {
        useBorderWithImage:true
      }
    },
    edges: {
      color: 'lightgray',
      width: 5,
      shadow: {
        enabled: true,
        color: 'rgba(255,255,255, 1)',
        size: 20,
        x: 0, y: 0,
      },
    },
    layout:{
      randomSeed: 15,
      improvedLayout: true,
    },
    interaction:{
      tooltipDelay: 3600000 // One hour--we manually show the title on click
    },
    //physics:{enabled:false},
    physics: {
      // solver: 'forceAtlas2Based'
      stabilization: {
        enabled: true,
        iterations: 500,
      }
    },
  };
  network = new vis.Network(container, data, options);
  function getBox(node_id) {
    console.log(node_id)
    return tl_data.elements[node_id];
  }
  function positionBox(node_id) {
    const box = getBox(node_id);
    let bounds = network.getBoundingBox(node_id);
    let orig = network.getViewPosition();
    let scale = network.getScale();
    box.style.left = ((bounds.left + bounds.right) / 2 - orig.x) * scale + network.canvas.canvasViewCenter.x - 35 + "px";
    box.style.top = ((bounds.top + bounds.bottom) / 2 - orig.y) * scale  + network.canvas.canvasViewCenter.y - 31 + "px";
    return box;
  }
  function expandNode(node_id) {
    let box = positionBox(node_id);
    box.style.transition = "";  // We disabled transitions earlier.
    box.className = VISIBLE_CLASS;
  }
  function collapseNode(node_id) {
    getBox(node_id).className = HIDDEN_CLASS;
  }
  const sel_state = [];
  for (let i in tl_data.nodes) sel_state[i] = false;
  function updateNodes(nodes) {
    const new_sel = [];
    for (let i in tl_data.nodes) new_sel[i] = false;
    for (const i of network.getSelection().nodes) new_sel[i] = true;
    for (let i in tl_data.nodes) {
      if (sel_state[i] && !new_sel[i]) {
        console.log("Selected");
        collapseNode(i);
        sel_state[i] = false;
      } else if (!sel_state[i] && new_sel[i]) {
        console.log("Deselected");
        expandNode(i);
        sel_state[i] = true;
      } else if (new_sel[i]) {
        positionBox(i);
      }
    }
    console.log(new_sel);
  }
  network.on('initRedraw', updateNodes);
}

function compile_timeline() {
  const ndata = [];
  const nelems = [];
  const nnames = {};
  for (const elem of document.querySelectorAll('.TLEntry')) {
    let image, title, paragraphs = [], edges = [], size = 10;
    for (const c of elem.children) {
      switch (c.tagName) {
        case 'IMG': image = c.src; break;
        case 'H1': size = 0; title = c.textContent; break;
        case 'H2': size = 1; title = c.textContent; break;
        case 'H3': size = 2; title = c.textContent; break;
        case 'P': case 'DIV': paragraphs.push(c); break;
        case 'EDGE':case 'TL-EDGE': edges.push(c.getAttribute('to')); break;
        default: throw 'Unknown tag ' + c.tagName;
      }
    }
    
    let rect = elem.getBoundingClientRect();
    
    nnames[title] = ndata.length;
    nelems.push(elem);
    ndata.push({
      id: nnames[title],
      title: title,
      image: image,
      size: [96,64,32][size],
      paragraphs: paragraphs,
      content: elem.innerHTML,
      edges: edges,
      x: rect.left,
      y: rect.top / 1.5,
    });
    elem.style.transition = "none";
    elem.className = HIDDEN_CLASS;
  }

  for (const elem of document.querySelectorAll('.TLEntry')) {
    elem.style.position = "absolute";
    elem.style.top = elem.style.left = 0;
  }
  
  const nodes = [];
  const edges = [];
  const root = ndata[nnames['Born']];
  const x0 = root.x;
  const y0 = root.y;
  
  for (const n of ndata) {
    const nx = n.x - x0, ny = n.y - y0;
    nodes.push(node(n.id, n.image, n.size, n.content, n.paragraphs, nx, ny));
    for (const e of n.edges) {
      edges.push({from: n.id, to: nnames[e]});
    }
  }
  
  const hideme = document.getElementById("hideme");
  if (hideme) hideme.style.float = "left";
  
  return {
    nodes: nodes,
    edges: edges,
    elements: nelems,
  };
}

