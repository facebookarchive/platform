<?php
include 'lib/core.php';

echo render_fbconnect_init_js();

echo '<h1>Invite Friends</h1>'
  .'<div id="request_form" style="visibility:hidden;" >
    <fb:serverfbml style="width: 755px;">
    <script type="text/fbml">
     <fb:fbml>
     <fb:request-form action="thanks.php" method="POST" invite="true" type="Run Around"
                      content="
     Invite your friends to Run Around
      <fb:req-choice url="url">
                     label="Invite Your Friends" />">
      <fb:multi-friend-selector
         showborder="false"
         actiontext="Invite your friends to use Connect.">
     </fb:request-form>
     </fb:fbml>
     </script>
     </fb:serverfbml>
     </div>
  <script type="text/javascript">ensure_init(function() { console.log("hey"); }); </script>
  ';
