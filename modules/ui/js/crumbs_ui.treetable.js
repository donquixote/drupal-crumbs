
(function($){

  function ParentNode(tr, control) {

    var expanded = true;
    var visible = true;
    var i;
    var self = this;
    $(tr).addClass('crumbs_ui-treetable-expanded');

    var children = [];

    this.addChild = function(child) {
      children.push(child);
    };

    this.show = function() {
      if (visible) {
        return;
      }
      visible = true;
      $(tr).show();
      if (expanded) {
        for (i = 0; i < children.length; ++i) {
          children[i].show();
        }
      }
    };

    this.hide = function() {
      if (!visible) {
        return;
      }
      visible = false;
      $(tr).hide();
      if (expanded) {
        for (i = 0; i < children.length; ++i) {
          children[i].hide();
        }
      }
    };

    this.collapse = function() {
      if (!expanded) {
        return;
      }
      expanded = false;
      $(tr).removeClass('crumbs_ui-treetable-expanded');
      if (visible) {
        for (i = 0; i < children.length; ++i) {
          children[i].hide();
        }
      }
    };

    this.collapseChildren = function(depth) {
      if (depth <= 0) {
        this.collapse();
      }
      for (i = 0; i < children.length; ++i) {
        children[i].collapseChildren(depth - 1);
      }
    };

    this.expand = function() {
      if (expanded) {
        return;
      }
      expanded = true;
      $(tr).addClass('crumbs_ui-treetable-expanded');
      if (visible) {
        for (i = 0; i < children.length; ++i) {
          children[i].show();
        }
      }
    };

    $(control).click(function(){
      if (expanded) {
        self.collapse();
      }
      else {
        self.expand();
      }
    });
  }

  function LeafNode(tr) {
    this.show = function() {
      $(tr).show();
    };

    this.hide = function() {
      $(tr).hide();
    };

    this.collapseChildren = function(depth) {
      // Do nothing.
    };

    $(tr).addClass('crumbs_ui-treetable-leaf');
  }

  function IndentMatrix() {

    var matrix = [];
    var trailIndices = [];

    this.buildRowIndentElements = function(depth, control) {

      // Build the elements.
      var indentContainer = $('<span class="crumbs_ui-treetable-indentContainer">');
      var row = [];
      for (var iCol = 0; iCol <= depth; ++iCol) {
        var indentElement = $('<span class="crumbs_ui-treetable-indent">').appendTo(indentContainer);
        row.push(indentElement);
      }
      matrix.push(row);

      if (control) {
        indentElement.append(control);
      }

      // Connect with previous nodes in the matrix.
      row[depth].addClass('crumbs_ui-NE');
      var trailIndex = null;
      while (trailIndices.length > depth) {
        trailIndex = trailIndices.pop();
      }
      trailIndices.push(matrix.length - 1);

      var iRow;
      if (trailIndex === null) {
        for (
          iRow = matrix.length - 2;
          matrix[iRow] && matrix[iRow][depth];
          --iRow
        ) {
          matrix[iRow][depth].addClass('crumbs_ui-NS');
        }
      }
      else {
        for (
          iRow = matrix.length - 2;
          iRow > trailIndex;
          --iRow
        ) {
          matrix[iRow][depth].addClass('crumbs_ui-NS');
        }

        matrix[trailIndex][depth].removeClass('crumbs_ui-NE');
        matrix[trailIndex][depth].addClass('crumbs_ui-NSE');
      }

      // Return.
      return indentContainer;
    }
  }

  Drupal.behaviors.crumbs_ui_treetable = {
    attach: function(context) {
      var trail = [];
      var indentMatrix = new IndentMatrix();
      var indentElements = [];
      var indentElement;
      var indentElementWrapper;
      $('.crumbs_ui-treetable', context).each(function(){
        $('> tbody > tr', this).each(function(){
          var tr = this;
          var depth = $(tr).attr('data-crumbs_ui-tree_depth');
          if (depth === undefined) {
            throw "No depth specified.";
          }
          var is_parent = $(tr).attr('data-crumbs_ui-is_parent');

          if (trail.length > depth) {
            $(indentElement).addClass('crumbs_ui-NE');
            while (trail.length > depth) {
              trail.pop();
            }

          }
          else if (trail.length < depth) {
            console.log('unexpected item depth');
            throw "Unexpected item depth.";
          }
          else {
            $(indentElement).addClass('crumbs_ui-NSE');
          }

          var parent = null;
          if (trail.length) {
            parent = trail[trail.length - 1];
          }

          var tdFirst = $('> td', tr)[0];

          var obj, control;
          if (is_parent) {
            control = $('<span class="crumbs_ui-treetable-control"></span>')[0];
            indentMatrix.buildRowIndentElements(depth, control).prependTo(tdFirst);
            obj = new ParentNode(this, control);
          }
          else {
            indentMatrix.buildRowIndentElements(depth).prependTo(tdFirst);
            obj = new LeafNode(this);
          }

          if (parent) {
            parent.addChild(obj);
          }

          if (obj.addChild !== undefined) {
            trail.push(obj);
          }
        });

        if (trail[0]) {
          trail[0].collapseChildren(1);
        }
      });
    }
  };

})(jQuery);

