
(function($){

  function ParentNode(element, parent) {

    var all_are_enabled = true;
    var all_are_disabled = true;
    var state = false;
    var class_name = null;

    var self = this;

    var children = [];

    function readState() {
      all_are_enabled = true;
      all_are_disabled = true;
      for (var i = 0; i < children.length; ++i) {
        var child_state = children[i].getState();
        if (true !== child_state) {
          all_are_enabled = false;
        }
        if (false !== child_state) {
          all_are_disabled = false;
        }
      }
      var class_name_before = class_name;
      if (all_are_disabled) {
        state = false;
        class_name = null;
        element.checked = false;
        element.indeterminate = false;
        element.disabled = false;
      }
      else if (all_are_enabled) {
        state = true;
        class_name = 'crumbs_ui-tristate-enabled';
        element.checked = true;
        element.indeterminate = false;
        element.disabled = false;
      }
      else {
        state = null;
        class_name = 'crumbs_ui-tristate-undetermined';
        element.checked = false;
        // HTML5 rocks!
        element.indeterminate = true;
        element.disabled = true;
      }
      if (class_name_before !== class_name) {
        if (class_name_before) {
          $(element).removeClass(class_name_before);
        }
        if (class_name) {
          $(element).addClass(class_name);
        }
      }
    }

    this.getState = function() {
      return state;
    };

    this.setState = function(state_new) {
      if (state_new === state) {
        return;
      }
      for (var i = 0; i < children.length; ++i) {
        children[i].setState(state_new);
      }
      self.update();
    };

    $(element).click(function(){
      if (state === false) {
        self.setState(true);
      }
      else if (state === true) {
        self.setState(false);
      }
    });

    this.addChild = function(child) {
      children.push(child);
      self.update();
    };

    this.update = function() {
      readState();
      if (parent) {
        parent.update();
      }
    }
  }

  function LeafNode(checkbox, parent) {
    this.getState = function() {
      console.log('LEAF', $(checkbox).attr('name'), $(checkbox).is(':checked'));
      return $(checkbox).is(':checked');
    };

    this.setState = function(new_state) {
      checkbox.checked = new_state;
    };

    $(checkbox).change(function(){
      if (parent) {
        parent.update();
      }
    });
  }

  Drupal.behaviors.crumbs_ui_checkboxtree = {
    attach: function(context) {

      var trail = [];

      $('.crumbs_ui-checkboxtree', context).each(function() {

        $('input:checkbox.crumbs_ui-checkboxtree_item', this).each(function() {
          var obj, parent;
          var depth = $(this).attr('data-crumbs_ui-tree_depth');
          var name = $(this).attr('name');
          if (depth === undefined) {
            throw "No depth specified for " + name;
          }
          while (trail.length > depth) {
            trail.pop();
          }
          if (trail.length < depth) {
            throw "Unexpected item depth.";
          }

          var is_parent = $(this).attr('data-crumbs_ui-is_parent');

          if (trail.length) {
            parent = trail[trail.length - 1];
          }

          if (is_parent) {
            obj = new ParentNode(this, parent);
          }
          else {
            obj = new LeafNode(this, parent);
          }

          if (parent) {
            parent.addChild(obj);
          }

          if (obj.addChild !== undefined) {
            trail.push(obj);
          }
        });
      });
    }
  };
})(jQuery);

