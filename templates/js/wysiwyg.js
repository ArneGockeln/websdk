/**
 * Created by arnegockeln on 12.10.15.
 */
$(function(){
    $('.wysiwyg').each(function(){
        var rows = parseInt($(this).attr('rows'));
        var isAdmin = false;
        if($(this).attr('showcode') != undefined){
            isAdmin = $(this).attr('showcode').toLowerCase() == 'true';
        }
        var _height = 300;
        if(rows > 0){
            _height = 20 * rows;
        }

        var _toolbar = "paste | bold italic underline subscript superscript | alignleft aligncenter alignright | bullist numlist table | link unlink";
        if(isAdmin){
            _toolbar += " | code";
        }

        $(this).tinymce({
            language: "de",
            height: _height,
            plugins: ["code", "link", "table", "paste", "wordcount", "lists", "autoresize"],
            statusbar: false,
            menubar: false,
            toolbar: _toolbar,
            content_css: getBaseUrl() + '/templates/css/wysiwyg_content_styles.css',
            setup: function(ed) { // set editor to readonly if textarea property readonly or disabled exists
                var textarea = $('#'+ed.id);
                if (textarea.prop('readonly') || textarea.prop('disabled')) {
                    ed.settings.readonly = true;
                }
            }
        });
    });
});