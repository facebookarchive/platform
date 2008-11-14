<a href="#" id="helloWorld" onclick="do_colors(this); return false">Hello World</a>
<script><!--
function random_int(lo, hi) {
   return Math.floor((Math.random() * (hi - lo)) + lo);
}

function do_colors(obj) {
  var r = random_int(0, 255);
  var other = r<129?r+128:r-128;
  obj.setInnerXHTML("<div>" + r + "," + other + "</div>");
}
//-->
</script>
