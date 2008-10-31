<a href="#" onclick="do_colors(this); return false">Hello World!</a>
<script><!--
function random_int(lo, hi) {
  return Math.floor((Math.random() * (hi - lo)) + lo)
}

function do_colors(obj) {
  var r = random_int(0, 255), b = random_int(0, 255), g = random_int(0, 255);
  obj.setStyle({background: 'rgb('+[r, g, b].join(',')+')',
                     color: 'rgb('+[r<129?r+128:r-128, g<129?g+128:g-128, b<129?b+128:b-128].join(',')+')'});
}
//--></script>
