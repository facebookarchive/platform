var cur_picked = -1;
function over(id) {
  document.getElementById("sm_"+id).addClassName("box_over");
}
function out(id) {
  document.getElementById("sm_"+id).removeClassName("box_over");
}
function select(id, feed) {
  document.getElementById("sm_"+id).addClassName("box_selected");
  document.getElementById("picked").setValue(id);
}

function unselect(id) {
  document.getElementById("sm_"+id).removeClassName("box_selected");
}

function picked(i) {
  if (cur_picked!=-1) {
    unselect(cur_picked);
  }
  cur_picked = i;
  select(i);
}
