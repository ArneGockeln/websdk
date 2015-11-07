/**
 * Created by arnegockeln on 13.10.15.
 */
/**
 * Modal Handler
 */
function modalDialog(title, content, buttons){
    var modal = $('#modalHelp');
    modal.find('.modal-title').html(title);
    modal.find('.modal-body').html(content);
    // replay buttons only if buttons are given!
    if(buttons != undefined) {
        if(buttons.length > 0) {
            modal.find('.modal-footer').html(buttons.join(''));
        }
    }
    modal.modal('show');
}

/**
 * Delete Modal Button opens Modal
 */
function rebindModalDelete(ajaxCallback){
    $('.btn-modal-delete').click(function(e){
        e.preventDefault();
        var baseURL = $('#baseUrl').attr('href');
        var elementID = $(this).data('element-id');
        var confirmURI = $(this).data('confirm-uri');
        if(elementID != undefined && parseInt(elementID) > 0){
            if(confirmURI != undefined){
                // ajax get modal body
                $.getJSON(baseURL + confirmURI + '/delete_confirm/' + elementID, function(response){
                    var modalDialog = $('#modalDelete');
                    modalDialog.find('.modal-body').html(response.body);
                    // do we have a js callback?
                    if(typeof(ajaxCallback) == 'function'){
                        var dangerBtn = modalDialog.find('.btn-danger');
                        dangerBtn.attr('href', '#');
                        dangerBtn.attr('data-dismiss', 'modal');
                        dangerBtn.data('element-id', elementID);
                        dangerBtn.click(ajaxCallback);
                    } else {
                        // no callback found, add traditional link
                        modalDialog.find('.btn-danger').attr('href', baseURL + confirmURI + '/delete/' + elementID);
                    }
                    modalDialog.modal('show');
                });
            } else {
                modalDialog('Fehler', 'confirm-uri data element is undefined!');
            }
        } else {
            modalDialog('Fehler', 'element-id data element is undefined!');
        }
    });
}

/**
 * Get the base url
 * @returns string
 */
function getBaseUrl(){
    return $('#baseUrl').attr('href').toString();
}

$(function(){
    rebindModalDelete();

    /**
     * Add Spinner state on submit buttons
     */
    $('.btn-with-spinner').each(function(){
        $(this).attr('data-loading-text', "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span>");
        $(this).on('click', function(){
            $(this).button('loading');
        });
    });

    /**
     * Datepicker
     */
    $('.datepicker').each(function(){
        var _startDate = $(this).data('startdate');

        $(this).datepicker({
            format: "yyyy-mm-dd",
            startDate: (_startDate != undefined && _startDate.length > 0 ? _startDate : new Date().toJSON().slice(0, 10)),
            todayBtn: true,
            clearBtn: true,
            language: "de"
        });
    });

    /**
     * Clockpicker
     */
    $('.clockpicker').clockpicker({ donetext: "Fertig", autoclose: true, placement: "top" });

    /**
     * Reset Btn for Form Fields
     * add class "btn-reset", add data-field="fieldID"
     */
    $('.btn-reset').click(function(){
        var field = $(this).data('field');
        if(field != undefined && field.length > 0){
            $('#' + field).val('');
        }
    });

    /**
     * Reset Filter Button
     */
    $('.btn-filter-reset').click(function(e){
        $('#inputFilterReset').val(1);
        $('form#filterForm').submit();
    });

    /**
     * Tooltips
     */
    $('[data-toggle="tooltip"]').tooltip();

    /**
     * Help Button opens modal
     */
    $('.btn-question').parent('a').click(function(e){
        e.preventDefault();
        $('#modalHelp').modal('show');
    });


});