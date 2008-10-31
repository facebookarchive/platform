<textarea id="table">Cell 1 | Cell 2
Cell 3 | Cell 4</textarea><br />
<input type="button" onclick="build_table(document.getElementById('table'))" value="Build" />
<script><!--
function build_table(text) {
  // Remove the previous table that was created 
  var ns = text.getNextSibling();
  if (ns.getTagName() == 'TABLE') {
    ns.getParentNode().removeChild(ns);
  }

  var table = document.createElement('table');
  var tbody = document.createElement('tbody');
  var rows = text.getValue().split('\n');
  table.appendChild(tbody);
  for (var i = 0; i < rows.length; i++) {
    var cols = rows[i].split('|');
    var row = document.createElement('tr');
    for (var j = 0; j < cols.length; j++) {
      var cell = document.createElement('td');
      cell.setStyle('border', '1px solid black');
      cell.setTextValue(cols[j]);
      row.appendChild(cell);
    }
    tbody.appendChild(row);
  }
  text.getParentNode().insertBefore(table, text.getNextSibling());
}
//--></script>
