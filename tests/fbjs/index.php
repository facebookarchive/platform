<style type="text/css">
.body {
  padding: 15px;
}
.body, p {
  font-size: 13px;
  line-height: 18px;
}
h1, h2, h3 {
  margin: 0px;
  padding: 0px;
}
h1 {
  font-size: 23px;
  font-weight: normal;
  margin: 0px 0px 10px;
  padding-top: 15px;
}
h2 {
  font-size: 15px;
  font-weight: bold;
  margin: 10px 0px 0px 0px;
}
h2 span {
  cursor: pointer;
}
h3 {
  font-size: 13px;
  font-weight: bold;
  margin: 0px;
}
.collapsed div {
  display: none;
}
small {
  color: #777;
}
</style>

<div class="body" id="body">
<h1>FBJS Test Suite</h1>
<p>Welcome to the FBJS test suite. I'm glad you could make it.</p>

<div><h2><span id="helloWorldTest">Hello World</span></h2><small>Math, setStyle</small><div>
<?php include 'helloworld.php' ?>
</div></div>

<div><h2><span id="ajaxTest">AJAX</span></h2><small>Ajax, serialize</small><div>
<?php include 'ajax.php' ?>
</div></div>

<div><h2><span id="typeaheadTest">Typeahead</span></h2><small>Events (focus, blur, keyup, keydown, keypress), DOM Manipulation, Prototyping</small><div>
<?php include 'typeahead.php' ?>
</div></div>

<div><h2><span id="sliderTest">Slider</span></h2><small>Events (mouse), DOM Manipulation, onload</small><div>
<?php include 'slider.php' ?>
</div></div>

<div><h2><span id="dialogsTest">Dialogs</span></h2><small>Ajax, setLocation, setStyle</small><div>
<?php include 'dialogs.php' ?>
</div></div>

<div><h2><span id="tableTest">Build a Table</span></h2><small>DOM Manipulation \w Tables...</small><div>
<?php include 'table.php' ?>
</div></div>

<div><h2><span>Facebook</span></h2><small>Facebook object test...</small><div>
<?php include 'facebook.php' ?>
</div></div>

<div><h2><span>Exploits</span></h2><small>Hopefully none of these work...</small><div>
<?php include 'exploits.php' ?>
</div></div>

</div>

<script><!--
var h2 = document.getElementById('body').getElementsByTagName('h2');
for (var i = 0; i < h2.length; i++) {
  h2[i].getParentNode().addClassName('collapsed');
  h2[i].getFirstChild().addEventListener('click', function(){this.getParentNode().toggleClassName('collapsed')}.bind(h2[i]));
}
//--></script>
