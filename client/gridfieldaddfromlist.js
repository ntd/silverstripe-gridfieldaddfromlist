window.jQuery.entwine('ss', ($) => {

  // Enable chosen.js support inside .grid-field
  $('.grid-field select.chosen').entwine({
    onmatch() {
      jQuery(this).chosen({
        width: '30%'
      });
    }
  });

  // GridFieldAddFromList client support
  $('.add-from-list select').entwine({
    onchange: function (evt, ui) {
      var id   = ui.selected,
          $div = $(this).parent(),
          $id  = $div.find('input[type="hidden"]');

      if ($id.length == 0) {
        // Add a new hidden field if not already present in the DOM
        $id = $('<input type="hidden" name="gridfield_addfromlist" class="no-change-track" />');
        $(this).after($id);
      }
      $id.val(id);

      // Disable the add action if no id is selected
      $div.find('button.action')
        .prop('disabled', id == 0);
    }
  });

});
