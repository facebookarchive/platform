<fb:js-string var="slider_handle">&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;|&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;</fb:js-string>

<script>
function slider(attach, size, input, min, max, initial) {
  this.container = document.createElement('div');
  this.container.setStyle('width', size + 'px');
  this.container.setClassName('slider');

  this.obj = document.createElement('div');

  this.handle = document.createElement('span');
  this.handle.setInnerFBML(slider_handle);

  document.getElementById(attach).appendChild(this.container);
  this.container.appendChild(this.obj);
  this.obj.appendChild(this.handle);

  this.mousedown = 0;
  this.lastPos = 0;
  this.min = min;
  this.max = max;
  this.input = document.getElementById(input);
  this.width = parseInt(this.obj.getScrollWidth()) - parseInt(this.handle.getScrollWidth()) - ((this.handle.getAbsoluteLeft() - this.obj.getAbsoluteLeft()) * 2);

  document.getRootElement().addEventListener('mouseup', this.mouseup.bind(this))
                           .addEventListener('mousemove', this.mousemove.bind(this));
  this.input.addEventListener('keydown', this.change.bind(this));
  this.obj.addEventListener('mousedown', this.mdown.bind(this));

  var num = parseInt(initial);
  if (num > this.max)
    num = this.max;
  if (num < this.min)
    num = this.min;
  var move = parseInt(((num - this.min) / (this.max - this.min)) * (this.width));
  this.handle.setStyle('marginLeft', move + 'px');
  this.input.setValue(num);
}

slider.prototype.mousemove = function(e) {
  if (this.mousedown != 0) {
    var move = e.pageX - this.lastPos;
    this.lastPos = e.pageX;
    if (this.handle.getStyle('marginLeft'))
      move += parseInt(this.handle.getStyle('marginLeft'));
    if (move > this.width)
      move = this.width;
    if (move < 0)
      move = 0;
    this.handle.setStyle('marginLeft', move + 'px');
    this.input.setValue(parseInt(((move / this.width) * (this.max - this.min)) + this.min))
    e.preventDefault();
  }
};

slider.prototype.mouseup = function(e) {
  this.mousedown = 0;
};

slider.prototype.mdown = function(e) {
  this.mousedown = 1;
  this.lastPos = e.pageX;
  var move = parseInt(this.lastPos - this.obj.getAbsoluteLeft() - (this.handle.getScrollWidth() / 2));
  if (move > this.width)
    move = this.width;
  if (move < 0)
    move = 0;
  this.handle.setStyle('marginLeft', move + 'px');
  this.input.setValue(parseInt(((move / this.width) * (this.max - this.min)) + this.min));
  e.preventDefault();
};

// update slider on input box change
slider.prototype.change = function(e) {
  // don't intercept backspace and arrow keys
  if ((e.keyCode == 8) || (e.keyCode == 37) || (e.keyCode == 39)) return true;
  var num = 0;
  if (this.input.getValue())
    num = parseInt(this.input.getValue() + String.fromCharCode(e.keyCode));
  else
    num = parseInt(String.fromCharCode(e.keyCode));
  if (num > this.max)
    num = this.max;
  if (num < this.min)
    num = this.min;
  var move = parseInt(((num - this.min) / (this.max - this.min)) * (this.width));
  this.handle.setStyle('marginLeft', move + 'px');
  this.input.setValue(num);
  return false;
};
</script>

<style>
div.slider {
  border: 1px solid #ccc;
  background-color: #f5f5f5;
  padding: 3px;
  margin: 4px;
}

div.slider div {
  background-color: #fff;
  border: 1px solid #ccc;
  padding: 0px 3px;
  font-size: 6px;
  margin: 4px;
}

div.slider div span {
  background-color:#ddd;
  border: 1px solid #ccc;
  color: #999;
  padding-bottom: 1px;
}
div.slider div span:hover {
  cursor: pointer;
}
</style>
<div id="test"></div>
<script>new slider('test', '500', 'test', 0, 500, 50);</script>
