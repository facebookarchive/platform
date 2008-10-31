<script><!--
function update_facebook() {
  document.getElementById('fb_user').setTextValue(Facebook.getUser());
  document.getElementById('fb_added').setTextValue(Facebook.isApplicationAdded());
  document.getElementById('fb_loggedin').setTextValue(Facebook.isLoggedIn());
}
//--></script>
<div><b>User: </b><span id="fb_user"></span></div>
<div><b>Added: </b><span id="fb_added"></span></div>
<div><b>Logged In: </b><span id="fb_loggedin"></span></div>
<input type="button" onclick="update_facebook()" value="Update" />
