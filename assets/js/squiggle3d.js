/* The Wove squiggle as a 3D tube that pivots a little on its vertical axis.
 * Draws into every canvas[data-squiggle3d] with plain WebGL (no library).
 *
 * The path is traced from the brand "Wiggle B" artwork (750px square,
 * y down) and given some depth where the loops cross, so it reads as a
 * single bent tube when it turns. The ends are open and cut straight. With prefers-reduced-motion it is drawn
 * once, still. Without WebGL the canvas stays empty.
 */
(function () {
  var canvases = document.querySelectorAll('canvas[data-squiggle3d]');
  if (!canvases.length) return;

  // Points along the stroke in the artwork, start (top left) to end (top right)
  var TRACE = [
    [125, 240], [108, 330], [112, 440], [150, 545], [215, 615], [290, 640],
    [345, 610], [385, 530], [402, 440], [390, 340], [355, 255], [305, 210],
    [262, 230], [252, 300], [280, 385], [335, 460], [405, 505], [470, 515],
    [530, 470], [560, 390], [565, 300], [545, 215], [505, 145], [470, 128],
    [442, 160], [445, 230], [490, 290], [560, 322], [618, 300], [645, 230],
    [638, 140]
  ];
  // Depth at each point (artwork units): pulls the crossings apart
  // Each loop passes behind the stroke it crosses, like a coil
  var DEPTH = [
    0, 10, 20, 30, 40, 45, 50, 50, 45, 30, 10, -10,
    -30, -45, -55, -55, -45, -30, -10, 10, 30, 40, 40, 30,
    10, -10, -30, -30, -20, -10, 0
  ];
  var SCALE = 1 / 300, CX = 375, CY = 390;
  var RADIUS = 0.11, SEGMENTS = 420, SIDES = 18;
  var COLOUR = [42 / 255, 80 / 255, 243 / 255]; // #2a50f3

  function point(i) {
    var p = TRACE[Math.max(0, Math.min(TRACE.length - 1, i))];
    var z = DEPTH[Math.max(0, Math.min(DEPTH.length - 1, i))];
    return [(p[0] - CX) * SCALE, -(p[1] - CY) * SCALE, z * SCALE];
  }

  // Catmull-Rom through the traced points
  function curve(t) {
    var n = TRACE.length - 1, f = t * n, i = Math.min(n - 1, Math.floor(f)), u = f - i;
    var p0 = point(i - 1), p1 = point(i), p2 = point(i + 1), p3 = point(i + 2);
    var out = [0, 0, 0];
    for (var k = 0; k < 3; k++) {
      out[k] = 0.5 * ((2 * p1[k]) + (-p0[k] + p2[k]) * u +
        (2 * p0[k] - 5 * p1[k] + 4 * p2[k] - p3[k]) * u * u +
        (-p0[k] + 3 * p1[k] - 3 * p2[k] + p3[k]) * u * u * u);
    }
    return out;
  }

  function sub(a, b) { return [a[0] - b[0], a[1] - b[1], a[2] - b[2]]; }
  function cross(a, b) { return [a[1] * b[2] - a[2] * b[1], a[2] * b[0] - a[0] * b[2], a[0] * b[1] - a[1] * b[0]]; }
  function norm(a) { var l = Math.hypot(a[0], a[1], a[2]) || 1; return [a[0] / l, a[1] / l, a[2] / l]; }
  function dot(a, b) { return a[0] * b[0] + a[1] * b[1] + a[2] * b[2]; }

  // Open tube with parallel-transport frames: an outer wall, an inner wall
  // and a flat rim at each end, so the ends are cut straight and you can
  // see into the tube. Each end runs on straight for a short way.
  var WALL = 0.78; // inner radius as a share of the outer
  var LEAD = 0.16; // length of the straight run at each end
  function buildMesh() {
    var pos = [], nrm = [], shade = [], idx = [];
    var pts = [];
    for (var s = 0; s <= SEGMENTS; s++) pts.push(curve(s / SEGMENTS));
      // The straight runs carry on along the line of the curve
    var t0 = norm(sub(pts[0], pts[2])), t1 = norm(sub(pts[pts.length - 1], pts[pts.length - 3]));
    var head = [], tail = [];
    for (var k = 12; k >= 1; k--) head.push(pts[0].map(function (v, i) { return v + t0[i] * LEAD * k / 12; }));
    for (k = 1; k <= 12; k++) tail.push(pts[pts.length - 1].map(function (v, i) { return v + t1[i] * LEAD * k / 12; }));
    pts = head.concat(pts, tail);
    var last = pts.length - 1;
    var tangents = pts.map(function (p, i) {
      return norm(sub(pts[Math.min(last, i + 1)], pts[Math.max(0, i - 1)]));
    });
    var frames = [], n = norm(cross(tangents[0], [0, 0, 1]));
    for (var i = 0; i <= last; i++) {
      var t = tangents[i];
      n = norm(sub(n, t.map(function (v) { return v * dot(n, t); })));
      frames.push([n, cross(t, n)]);
    }
    function dir(i, j) {
      var a = j / SIDES * Math.PI * 2, c = Math.cos(a), sn = Math.sin(a), f = frames[i];
      return [f[0][0] * c + f[1][0] * sn, f[0][1] * c + f[1][1] * sn, f[0][2] * c + f[1][2] * sn];
    }
    function vert(p, nn, sh) { pos.push(p[0], p[1], p[2]); nrm.push(nn[0], nn[1], nn[2]); shade.push(sh); }
    function wall(radius, inward, sh) {
      var base = pos.length / 3;
      for (var i = 0; i <= last; i++) {
        for (var j = 0; j <= SIDES; j++) {
          var d = dir(i, j);
          vert([pts[i][0] + d[0] * radius, pts[i][1] + d[1] * radius, pts[i][2] + d[2] * radius],
            inward ? [-d[0], -d[1], -d[2]] : d, sh);
        }
      }
      for (i = 0; i < last; i++) {
        for (j = 0; j < SIDES; j++) {
          var r0 = base + i * (SIDES + 1) + j, r1 = r0 + SIDES + 1;
          idx.push(r0, r1, r0 + 1, r1, r1 + 1, r0 + 1);
        }
      }
    }
    wall(RADIUS, false, 1);
    wall(RADIUS * WALL, true, 0.55); // the inside reads darker
    // Flat rims joining the walls at both ends
    [[0, -1], [last, 1]].forEach(function (end) {
      var i = end[0], tn = tangents[i].map(function (v) { return v * end[1]; });
      var base = pos.length / 3;
      for (var j = 0; j <= SIDES; j++) {
        var d = dir(i, j);
        vert([pts[i][0] + d[0] * RADIUS, pts[i][1] + d[1] * RADIUS, pts[i][2] + d[2] * RADIUS], tn, 1);
        vert([pts[i][0] + d[0] * RADIUS * WALL, pts[i][1] + d[1] * RADIUS * WALL, pts[i][2] + d[2] * RADIUS * WALL], tn, 1);
      }
      for (j = 0; j < SIDES; j++) {
        var q = base + j * 2;
        idx.push(q, q + 1, q + 2, q + 1, q + 3, q + 2);
      }
    });
    return { pos: new Float32Array(pos), nrm: new Float32Array(nrm), shade: new Float32Array(shade), idx: new Uint32Array(idx) };
  }

  var VERT = 'attribute vec3 p; attribute vec3 n; attribute float s; uniform mat4 m; uniform mat4 proj; varying vec3 vn; varying vec3 vp; varying float vs;' +
    'void main(){ vec4 w = m * vec4(p,1.0); vn = mat3(m) * n; vp = w.xyz; vs = s; gl_Position = proj * w; }';
  var FRAG = 'precision mediump float; varying vec3 vn; varying vec3 vp; varying float vs; uniform vec3 c;' +
    'void main(){ vec3 N = normalize(vn); vec3 L = normalize(vec3(-0.5,0.7,0.6)); vec3 V = normalize(-vp);' +
    // Matte plastic: soft wrapped light, no highlight, edges a touch darker
    ' float d = max((dot(N,L) + 0.5) / 1.5, 0.0);' +
    ' float edge = mix(0.82, 1.0, max(dot(N,V),0.0));' +
    ' vec3 col = c * (0.62 + 0.45 * d) * edge * vs;' +
    ' gl_FragColor = vec4(col,1.0); }';

  function perspective(fov, aspect, near, far) {
    var f = 1 / Math.tan(fov / 2), nf = 1 / (near - far);
    return [f / aspect, 0, 0, 0, 0, f, 0, 0, 0, 0, (far + near) * nf, -1, 0, 0, 2 * far * near * nf, 0];
  }
  function model(yaw, pitch) {
    var cy = Math.cos(yaw), sy = Math.sin(yaw), cp = Math.cos(pitch), sp = Math.sin(pitch);
    // Rotate about Y, then X, then push back from the camera
    return [cy, sp * sy, -cp * sy, 0, 0, cp, sp, 0, sy, -sp * cy, cp * cy, 0, 0, 0, -3.6, 1];
  }

  var mesh = null;
  var still = window.matchMedia('(prefers-reduced-motion: reduce)');

  [].forEach.call(canvases, function (canvas) {
    var gl = canvas.getContext('webgl', { antialias: true, alpha: true, premultipliedAlpha: false });
    if (!gl || !gl.getExtension('OES_element_index_uint')) return;
    mesh = mesh || buildMesh();

    function shader(type, src) { var s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return s; }
    var prog = gl.createProgram();
    gl.attachShader(prog, shader(gl.VERTEX_SHADER, VERT));
    gl.attachShader(prog, shader(gl.FRAGMENT_SHADER, FRAG));
    gl.linkProgram(prog);
    gl.useProgram(prog);

    function attr(name, data, size) {
      var buf = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, buf);
      gl.bufferData(gl.ARRAY_BUFFER, data, gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(prog, name);
      gl.enableVertexAttribArray(loc);
      gl.vertexAttribPointer(loc, size || 3, gl.FLOAT, false, 0, 0);
    }
    attr('p', mesh.pos);
    attr('n', mesh.nrm);
    attr('s', mesh.shade, 1);
    var ibuf = gl.createBuffer();
    gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, ibuf);
    gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, mesh.idx, gl.STATIC_DRAW);

    var uM = gl.getUniformLocation(prog, 'm'), uP = gl.getUniformLocation(prog, 'proj');
    gl.uniform3fv(gl.getUniformLocation(prog, 'c'), COLOUR);
    gl.enable(gl.DEPTH_TEST);
    gl.clearColor(0, 0, 0, 0);

    function size() {
      var dpr = Math.min(2, window.devicePixelRatio || 1);
      var w = Math.round(canvas.clientWidth * dpr), h = Math.round(canvas.clientHeight * dpr);
      if (canvas.width !== w || canvas.height !== h) { canvas.width = w; canvas.height = h; }
      gl.viewport(0, 0, w, h);
      gl.uniformMatrix4fv(uP, false, perspective(0.62, w / (h || 1), 0.1, 20));
    }

    // A small back-and-forth turn: about 25 degrees each way over 9 seconds
    var start = performance.now(), visible = true;
    function frame(now) {
      var t = (now - start) / 1000;
      var yaw = still.matches ? -0.25 : Math.sin(t * Math.PI * 2 / 9) * 0.45;
      var pitch = still.matches ? 0.08 : 0.08 + Math.sin(t * Math.PI * 2 / 13) * 0.06;
      size();
      gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
      gl.uniformMatrix4fv(uM, false, model(yaw, pitch));
      gl.drawElements(gl.TRIANGLES, mesh.idx.length, gl.UNSIGNED_INT, 0);
      if (visible && !still.matches) requestAnimationFrame(frame);
    }

    // Only animate while on screen
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        var was = visible;
        visible = entries[0].isIntersecting;
        if (visible && !was) requestAnimationFrame(frame);
      }).observe(canvas);
    }
    window.addEventListener('resize', function () { requestAnimationFrame(frame); });
    if (still.addEventListener) still.addEventListener('change', function () { requestAnimationFrame(frame); });
    requestAnimationFrame(frame);
  });
})();
