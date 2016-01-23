/**
 * Created by arnegockeln on 04.11.15.
 */

$(function(){
    var baseURL = $('#baseUrl').attr('href');

    // submit form in active tab!
    $('#btnOptionsSubmit').click(function(){
        $('#optiontabs > .active').find('form').submit();
    });

    // check next active tab to hide global save button
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if($(e.target).data('hidesavebtn') != undefined){
            $('#panelOptionsSubmit').hide();
        } else {
            $('#panelOptionsSubmit').show();
        }
    });

});