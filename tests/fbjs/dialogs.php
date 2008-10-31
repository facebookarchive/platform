<div id="dialog_body">

<a href="#" onclick="new Dialog().showMessage('Dialog', 'Hello World.'); return false;">
Vanilla DIALOG_POP.</a><br />

<div style="height: 100px; overflow: auto"><div style="height:200px">
<a href="#" onclick="new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(this).showChoice('Dialog', 'Hello World.', 'Foo', 'Bar'); return false;">
CONTEXTUAL_DIALOG with two buttons: Foo and Bar</a><br />
</div></div>

<a href="#" onclick="var dialog = new Dialog().showChoice('Important Dialog', dialog_color, 'Okay', 'Nevermind');
dialog.onconfirm = function() {
  var color = document.getElementById('dialog_color_select').getValue();
  document.getElementById('dialog_body').setStyle({background: color});
};
return false;">
A dialog that changes your colors...</a><br />
<style type="text/css">
.bold {
  font-weight: bold;
}
</style>
<fb:js-string var="dialog_color">
<span class="bold">What color would you like this set to be? (This line should be bold)</span><br />
<select id="dialog_color_select">
<option value="transparent">Default</option>
<option value="blue">Blue</option>
<option value="red">Red</option>
<option value="yellow">Yellow</option>
</select>
</fb:js-string>

<!-- Note also, we chain the onconfirm hook at the end -->
<a href="#" onclick="new Dialog().showChoice('Take Me Away!', dialog_redirect).onconfirm = function() {
  document.setLocation(document.getElementById('dialog_location').getValue());
  return false;
}">
Where do you want to go?</a><br />
<fb:js-string var="dialog_redirect">
<div style="text-align: center">
<input id="dialog_location" value="http://www.facebook.com/fbml/console.php" size="50" />
</div>
</fb:js-string>

</div>
