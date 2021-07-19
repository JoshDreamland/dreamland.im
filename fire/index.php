<html>

<head>
<title>WebGL Fire</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

<body onload="startup()">
<script id="vertex-shader" type="x-shader/x-vertex">
  attribute vec3 aVertexPosition;
  varying lowp float vAlpha;

  uniform vec2 uScalingFactor;
  uniform vec2 uRotationVector;

  void main() {
    vec2 rotatedPosition = vec2(
      aVertexPosition.x * uRotationVector.y +
            aVertexPosition.y * uRotationVector.x,
      aVertexPosition.y * uRotationVector.y -
            aVertexPosition.x * uRotationVector.x
    );

    gl_Position = vec4(rotatedPosition * uScalingFactor, 0.0, 1.0);
    vAlpha = aVertexPosition.z;
  }
</script>

<script id="fragment-shader" type="x-shader/x-fragment">
  #ifdef GL_ES
    precision highp float;
  #endif

  uniform vec4 uGlobalColor;
  varying lowp float vAlpha;

  void main() {
    gl_FragColor = uGlobalColor;
    gl_FragColor.w = vAlpha;
  }
</script>

<canvas id="glcanvas" width="600" height="460">
  Oh no! Your browser doesn't support canvas!
</canvas>

<script type="text/javascript"><html>

<head>
<title>WebGL Hello World</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

<script type="text/javascript" src="glMatrix-0.9.5.min.js"></script>

<body onload="startup()">
<script id="vertex-shader" type="x-shader/x-vertex">
  attribute vec3 aVertexPosition;
  varying lowp float vAlpha;

  uniform vec2 uScalingFactor;
  uniform vec2 uRotationVector;

  void main() {
    vec2 rotatedPosition = vec2(
      aVertexPosition.x * uRotationVector.y +
            aVertexPosition.y * uRotationVector.x,
      aVertexPosition.y * uRotationVector.y -
            aVertexPosition.x * uRotationVector.x
    );

    gl_Position = vec4(rotatedPosition * uScalingFactor, 0.0, 1.0);
    vAlpha = aVertexPosition.z;
  }
</script>

<script id="fragment-shader" type="x-shader/x-fragment">
  #ifdef GL_ES
    precision highp float;
  #endif

  uniform vec4 uGlobalColor;
  varying lowp float vAlpha;

  void main() {
    gl_FragColor = uGlobalColor;
    gl_FragColor.w = vAlpha;
  }
</script>

<canvas id="glcanvas" width="600" height="460">
  Oh no! Your browser doesn't support canvas!
</canvas>

<script type="text/javascript">
let gl = null;
let glCanvas = null;

// Aspect ratio and coordinate system
// details

let aspectRatio;
let currentRotation = [0, 1];
let currentScale = [1.0, 1.0];

// Vertex information

let vertexArray;
let vertexBuffer;
let vertexNumComponents;
let vertexCount;

// Rendering data shared with the
// scalers.

let uScalingFactor;
let uGlobalColor;
let uRotationVector;
let aVertexPosition;

// Animation timing

let previousTime = 0.0;
let degreesPerSecond = 90.0;
window.addEventListener("load", startup, false);


triangles = [];
function random(x) { return Math.random() * x; }

function startup() {
  glCanvas = document.getElementById("glcanvas", {
    premultipliedAlpha: true, // Ask for non-premultiplied alpha
  });
  gl = glCanvas.getContext("webgl");
  gl.enable(gl.BLEND);
  gl.blendFunc(gl.SRC_ALPHA, gl.ONE); // Additive blend mode

  const shaderSet = [
    {
      type: gl.VERTEX_SHADER,
      id: "vertex-shader"
    },
    {
      type: gl.FRAGMENT_SHADER,
      id: "fragment-shader"
    }
  ];

  shaderProgram = buildShaderProgram(shaderSet);

  currentRotation = [0, 1];
  currentScale = [2 / glCanvas.width, -2 / glCanvas.height];

  vertexArray = new Float32Array([]);

  vertexBuffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, vertexBuffer);
  gl.bufferData(gl.ARRAY_BUFFER, vertexArray, gl.STREAM_DRAW);

  vertexNumComponents = 3;  // x, y, alpha
  vertexCount = vertexArray.length / vertexNumComponents;
  rotationRate = 6;

  animateScene();
}

function buildShaderProgram(shaderInfo) {
  let program = gl.createProgram();

  shaderInfo.forEach(function(desc) {
    let shader = compileShader(desc.id, desc.type);

    if (shader) {
      gl.attachShader(program, shader);
    }
  });

  gl.linkProgram(program)

  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
    console.log("Error linking shader program:");
    console.log(gl.getProgramInfoLog(program));
  }

  return program;
}

function compileShader(id, type) {
  let code = document.getElementById(id).firstChild.nodeValue;
  let shader = gl.createShader(type);

  gl.shaderSource(shader, code);
  gl.compileShader(shader);

  if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
    console.log(`Error compiling ${type === gl.VERTEX_SHADER
                 ? "vertex" : "fragment"} shader:`);
    console.log(gl.getShaderInfoLog(shader));
  }
  return shader;
}

function cos(x) { return Math.cos(x * Math.PI / 180); }
function sin(x) { return Math.sin(x * Math.PI / 180); }

function Triangle(x, y, a, vx, vy, va) {
  return {
    x:  x,  y:  y,  a:  a,
    vx: vx, vy: vy, va: va,
    h: 1, hr: .01 + random(.01)
  };
}

function tick(currentTime) {
  let deltaAngle = ((currentTime - previousTime) / 1000.0)
        * degreesPerSecond;
  
  const ntriangles = [];
  for (let triangle of triangles) {
    triangle.x += triangle.vx;
    triangle.y += triangle.vy;
    triangle.a += triangle.va;
    console.log(triangle.h + " " + triangle.hr)
    triangle.h -= triangle.hr;
    if (triangle.h > 0) ntriangles.push(triangle);
    else console.log("Destroyed a triangle");
  }
  triangles = ntriangles;
  for (let i = 0; i < 4 && triangles.length < 480; ++i) {
    triangles.push(Triangle(
        -32 + random(64), 96 + random(48), random(360),
        .2 - random(.4),   -.1 - random(2), random(.5)));
  }
  
  points = [];
  vertexCount = 0;
  const tr = 16;
  for (let triangle of triangles) {
    points.push(triangle.x + tr * cos(triangle.a));
    points.push(triangle.y - tr * sin(triangle.a));
    points.push(triangle.h);
    
    points.push(triangle.x + tr * cos(triangle.a + 120));
    points.push(triangle.y - tr * sin(triangle.a + 120));
    points.push(triangle.h);
    
    points.push(triangle.x + tr * cos(triangle.a + 240));
    points.push(triangle.y - tr * sin(triangle.a + 240));
    points.push(triangle.h);
  }
  
  vertexCount = points.length / vertexNumComponents;
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(points), gl.STREAM_DRAW);

  previousTime = currentTime;
  animateScene();
}
  
function animateScene() {
  gl.viewport(0, 0, glCanvas.width, glCanvas.height);
  gl.clearColor(0.1, 0.1, 0.1, 1.0);
  gl.colorMask(true, true, true, true);
  gl.clear(gl.COLOR_BUFFER_BIT);

  currentRotation[0] = Math.sin(0);
  currentRotation[1] = Math.cos(0);

  gl.useProgram(shaderProgram);

  uScalingFactor  = gl.getUniformLocation(shaderProgram, "uScalingFactor");
  uGlobalColor    = gl.getUniformLocation(shaderProgram, "uGlobalColor");
  uRotationVector = gl.getUniformLocation(shaderProgram, "uRotationVector");

  gl.uniform2fv(uScalingFactor, currentScale);
  gl.uniform2fv(uRotationVector, currentRotation);
  gl.uniform4fv(uGlobalColor, [1, 0.15, 0.05, 1.0]);

  gl.bindBuffer(gl.ARRAY_BUFFER, vertexBuffer);

  aVertexPosition = gl.getAttribLocation(shaderProgram, "aVertexPosition");

  gl.enableVertexAttribArray(aVertexPosition);
  gl.vertexAttribPointer(aVertexPosition, vertexNumComponents,
        gl.FLOAT, false, 0, 0);

  gl.drawArrays(gl.TRIANGLES, 0, vertexCount);

  window.requestAnimationFrame(tick);
}
</script>
</body>
</html>

