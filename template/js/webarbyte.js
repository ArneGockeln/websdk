/* 
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

/**
 * From phpjs.org
 * @param {type} delimiter
 * @param {type} string
 * @param {type} limit
 * @returns {@exp;@exp;s@pro;slice@call;@call;concat|Array|Boolean|@exp;s@pro;slice@call;@call;concat|explode.Anonym$0}
 */
function explode (delimiter, string, limit) {
  // From: http://phpjs.org/functions
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *     example 1: explode(' ', 'Kevin van Zonneveld');
  // *     returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}

  if ( arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined' ) return null;
  if ( delimiter === '' || delimiter === false || delimiter === null) return false;
  if ( typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string === 'object'){
    return { 0: '' };
  }
  if ( delimiter === true ) delimiter = '1';

  // Here we go...
  delimiter += '';
  string += '';

  var s = string.split( delimiter );


  if ( typeof limit === 'undefined' ) return s;

  // Support for limit
  if ( limit === 0 ) limit = 1;

  // Positive limit
  if ( limit > 0 ){
    if ( limit >= s.length ) return s;
    return s.slice( 0, limit - 1 ).concat( [ s.slice( limit - 1 ).join( delimiter ) ] );
  }

  // Negative limit
  if ( -limit >= s.length ) return [];

  s.splice( s.length + limit );
  return s;
}

/**
 * From phpjs.org
 * @param {type} haystack
 * @param {type} needle
 * @param {type} offset
 * @returns {String|Boolean}
 */
function strpos(haystack, needle, offset) {
  //  discuss at: http://phpjs.org/functions/strpos/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Daniel Esteban
  //   example 1: strpos('Kevin van Zonneveld', 'e', 5);
  //   returns 1: 14

  var i = (haystack + '')
    .indexOf(needle, (offset || 0));
  return i === -1 ? false : i;
}

/**
 * From phpjs.org
 * @param {type} number
 * @param {type} decimals
 * @param {type} dec_point
 * @param {type} thousands_sep
 * @returns {@exp;s@call;join}
 */
function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
          prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
          sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
          dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
          s = '',
          toFixedFix = function(n, prec) {
    var k = Math.pow(10, prec);
    return '' + Math.round(n * k) / k;
  };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

/**
 * Convert bytes value to human readable format
 * @param {type} bytes
 * @returns {String}
 */
function bytesToSize(bytes) {
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes == 0) return '0 Bytes';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

$(function() {
  $('[data-toggle=tooltip]').tooltip();
  $('[data-toggle=popover]').popover();

  /**
   * Show Delete Modal form and set yes button with data-deleteurl option
   */
  $('#deleteModal').on('show.bs.modal', function() {
    var data = $(this).data('bs.modal');

    $('#deleteModalConfirmBtn').click(function() {
      window.location.href = data.options.deleteurl;
    });
  });

  /**
   * Toggle input/select form fields
   * add html tags to buttons: 
   * class="toggleInputSelect"
   * data-inputselect-show="#id"
   * data-inputselect-hide="#id"
   */
  $('.toggleInputSelect').click(function() {
    var show = $(this).data('inputselect-show');
    var hide = $(this).data('inputselect-hide');

    $('#' + show).find('select').val(0);
    $('#' + hide).find('select').val(0);

    $('#' + show).show();
    $('#' + hide).hide();
  });

  /**
   * Toggle specific set of rows
   * <button data-toggle="subrows" data-subrowident="classname">
   */
  $('button[data-toggle="subrows"]').click(function() {
    var toggleClass = $(this).data('subrowident');
    if (toggleClass.length > 0) {
      $('tr.' + toggleClass).toggle('');
    }
  });
  
  /**
   * Toggle panel bodies
   * add html tags to buttons or panel headings
   * class="togglePanelBtn"
   * data-toggle-panel="#id" id of panel body
   */
  $('.togglePanelBtn').click(function(){
    var togglePanel = $(this).data('toggle-panel');
    $('#' + togglePanel).toggle();
  });
  
  /**
   * Add selected list items from one list to another
   * add html tags to buttons:
   * class="addSelectedToListBtn"
   * data-from-list="#id" id from select input
   * data-to-list="#id" id to select input
   */
  $('.addSelectedToListBtn').click(function(){
    var selectFromList = $(this).data('from-list');
    if($('#' + selectFromList).length <= 0){
      alert('data-from-list is not set!');
    }
    var selectToList = $(this).data('to-list');
    if($('#' + selectToList).length <= 0){
      alert('data-to-list is not set!');
    }
    var selectedOpts = $('#' + selectFromList + ' option:selected');
    if(selectedOpts.length > 0){
      $('#' + selectToList).append($(selectedOpts).clone());
      $(selectedOpts).remove();
    }
  });
  
  /**
   * Select all entries before submitting the form.
   * Add class "selectAllOnListBeforeSubmitHook" to form tag
   * Add data-selectall-list="#id" id of list to select
   * Add data-selectall-list="#id,#id,..." to add multiple lists
   */
  $('.selectAllOnListBeforeSubmitHook').submit(function(){
    var selectList = $(this).data('selectall-list');
    // test if string is empty
    if(selectList.length <= 0){
      alert('data-selectall-list not defined!');
    }
    
    // test if there are more ids in list
    if(strpos(selectList, ',') !== false){
      // more than one list found
      var lists = explode(',', selectList);
      $.each(lists, function(index, value){
        $('#' + value + ' option').each(function() {
          $(this).prop('selected', true);
        }); 
      });
    } else {
      $('#' + selectList + ' option').each(function() {
        $(this).prop('selected', true);
      }); 
    }
  });
});