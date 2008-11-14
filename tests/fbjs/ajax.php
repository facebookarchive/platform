<div>
These links demonstrate the Ajax object:<br />

<form id="ajax_form">
<a href="#" id="json" onclick="do_ajax(Ajax.JSON); return false;">JSON</a><br />
<a href="#" id="raw" onclick="do_ajax(Ajax.RAW); return false;">RAW</a><br />
<a href="#" id="fbml" onclick="do_ajax(Ajax.FBML); return false;">FBML</a><br />
<label><input type="checkbox" id="requirelogin" /><span>Require Login?</span></label><br />
<div><span id="ajax1"></span><span id="ajax2"></span></div>
<input type="hidden" name="array[]" value="1" />
<input type="hidden" name="array[]" value="2" />
<input type="hidden" name="array[]" value="3" />
<input type="hidden" name="nested_array[1][]" value="4" />
<input type="hidden" name="nested_array[1][]" value="5" />
<input type="hidden" name="nested_array[foo][]" value="6" />
<input type="hidden" name="nested_array[bar]" value="7" />
</form>
</div>


<script><!--
function do_ajax(type) {
  var ajax = new Ajax();
  ajax.responseType = type;
  switch (type) {
    case Ajax.JSON:
      ajax.ondone = function(data) {
        document.getElementById('ajax1').setTextValue(data.message);
        document.getElementById('ajax2').setTextValue(data.test[0]);
      }
      break;

    case Ajax.FBML:
      ajax.ondone = function(data) {
        document.getElementById('ajax1').setInnerFBML(data);
        document.getElementById('ajax2').setTextValue('');
      }
      break;

    case Ajax.RAW:
      ajax.ondone = function(data) {
        document.getElementById('ajax1').setTextValue(data);
        document.getElementById('ajax2').setTextValue('');
      }
      break;
  }
  ajax.onerror = function() {
    new Dialog().showMessage('Ajax Error', ':(');
  }
  ajax.requireLogin = document.getElementById('requirelogin').getChecked();
  ajax.post('http://fbplatform.mancrushonmcslee.com/fbjstests/ajax_callback.php?t='+type, document.getElementById('ajax_form').serialize());
}
//--></script>
