<script><!--
var autocomplete = ['Facebook','foo','bar','FBJS','Safari','Firefox','Internet Explorer','Opera','Mario','Yoshi'];
//--></script>

<div>
  <input id="typeahead" class="inputtext th_placeholder" value="Type something here..." onfocus="new typeahead(document.getElementById('typeahead'), autocomplete)" /><br />
</div>

<script><!--
// A very basic typeahead object
function typeahead(obj, options) {
  this.obj = obj;

  // Setup the events we're listening to
  this.obj.purgeEventListeners('focus') // we want to get rid of the focus event added in the FBML above
          .addEventListener('focus', this.onfocus.bind(this))
          .addEventListener('blur', this.onblur.bind(this))
          .addEventListener('keyup', this.onkeyup.bind(this))
          .addEventListener('keydown', this.onkeydown.bind(this))
          .addEventListener('keypress', this.onkeypress.bind(this));

  // Create the dropdown list that contains our suggestions
  this.list = document.createElement('div');
  this.list.setClassName('th_list')
           .setStyle({width: this.obj.getOffsetWidth()-2+'px',
                      display: 'none'});
  this.obj.getParentNode().insertBefore(this.list, this.obj.getNextSibling().getNextSibling());

  // Various flags
  this.focused = true;
  this.options = options;
  this.selectedindex = -1;

  // Styling foo
  this.obj.removeClassName('th_placeholder')
          .setValue('');

  this.update_results();
  this.show();
}
typeahead.prototype.max_results = 5;

// Show suggestions when the user focuses the text field
typeahead.prototype.onfocus = function(event) {
  this.focused = true;
  this.update_results();
  this.obj.removeClassName('th_found');
  this.show();
}

// ...and hide it when they leave the text field
typeahead.prototype.onblur = function() {
  this.focused = true;
  this.hide();
}

// Every keypress updates the suggestions
typeahead.prototype.onkeyup = function(event) {
  switch (event.keyCode) {
    case 27: // escape
      this.hide();
      this.obj.removeClassName('th_found');
      break;

    case 0:
    case 13: // enter
    case 37: // left
    case 38: // up
    case 39: // right
    case 40: // down
      break;

    default:
      this.update_results();
      this.show();
      this.obj.removeClassName('th_found');
      break;
  }
}

// We want interactive stuff to happen on keydown to make it feel snappy
typeahead.prototype.onkeydown = function(event) {
  switch (event.keyCode) {
    case 9: // tab
    case 13: // enter
      if (this.results[this.selectedindex]) {
        this.obj.addClassName('th_found')
                .setValue(this.results[this.selectedindex]);
        this.hide();
        event.preventDefault();
      }
      break;

    case 38: // up
      this.select(this.selectedindex - 1);
      event.preventDefault();
      break;

    case 40: // down
      this.select(this.selectedindex + 1);
      event.preventDefault();
      break;
  }
}

// Override these events so they don't actually do anything
typeahead.prototype.onkeypress = function(event) {
  switch (event.keyCode) {
    case 13: // return
    case 38: // up
    case 40: // down
      event.preventDefault();
      break;
  }
}

// This gets called from our code to select a given index... this is where we would do something interesting like fire off some AJAX or something
typeahead.prototype.select = function(index) {
  var children = this.list.getChildNodes();
  var found = false;
  for (var i = 0; i < children.length; i++) {
    if (i == index) {
      children[i].addClassName('th_selected');
      this.selectedindex = index;
      found = true;
    } else {
      children[i].removeClassName('th_selected');
    }
  }

  if (!found && children[this.selectedindex]) {
    children[this.selectedindex].addClassName('th_selected');
  }
}

// This is called every keypress to update the suggestions
typeahead.prototype.update_results = function() {

  // Search the list of potential results and find ones that match what we have so far
  var results = [];
  var val = this.obj.getValue().toLowerCase();
  this.selectedindex = -1;
  for (var i = 0; i < this.options.length; i++) {
    var prefix = this.options[i].substring(0, val.length).toLowerCase();
    if (prefix == val) {
      results.push(this.options[i]);
      if (results.length >= this.max_results) {
        break;
      }
    }
  }

  // Generate a list to display the elements to the user
  this.list.setTextValue('');
  for (var i = 0; i < results.length; i++) {
    this.list.appendChild(
      document.createElement('div')
        .setClassName('th_suggestion')
        .addEventListener('mouseover', function() {
          this[0].select(this[1]);
          }.bind([this, i]))
        .addEventListener('mousedown', function(event) {
          this.obj.addClassName('th_found')
                  .setValue(this.results[this.selectedindex]);
          this.hide();
          }.bind(this))
        .appendChild(document.createElement('em')
                             .setTextValue(results[i].substring(0, val.length)))
        .getParentNode()
        .appendChild(document.createElement('span')
                             .setTextValue(results[i].substring(val.length)))
        .getParentNode()
    );
  }
  this.results = results;
}

typeahead.prototype.show = function() {
  this.list.setStyle('display', 'block');
}

typeahead.prototype.hide = function() {
  this.list.setStyle('display', 'none');
}
//--></script>

<style>
.th_placeholder {
  color: #777;
}

.th_found {
  background: #e1e9f6;
}

.th_list {
  background: transparent;
  border: 1px solid #bdc7d8;
  border-top: none;
  font-size: 11px;
  margin-top: -1px;
  overflow: hidden;
  position: absolute;
  text-align: left;
  z-index: 102;
}

.th_list .th_suggestion {
  background: #fff;
  border-top: 1px solid #ddd;
  color: #000;
  cursor: pointer;
  filter: alpha(opacity=94);
  padding: 3px;
  opacity: 0.94;
  width: 100%;
}

.th_list .th_suggestion em {
  background: #d8dfea;
  color: black;
  font-style: normal;
  font-weight: bold;
}

.th_list .th_suggestion small {
  color: #808080;
  padding-left: 5px;
}

.th_list .th_selected {
  background: #3b5998;
  color: #fff;
  filter: alpha(opacity=100);
  opacity: 1;
}

.th_list .th_selected em {
  background: #5670a6;
  color: #fff;
}
</style>
