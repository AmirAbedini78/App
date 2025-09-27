"use strict";

$(document).on('click', '.flexform-setup-block-list__cta', function() {
    const obj = $(this);
    const preview = $('.flexform-setup-block-preview');
    const img = $(this).data('img');
    const heading = $(this).data('heading');
    const description = $(this).data('description');
    $('#flexform-setup-block-type').val($(obj).data('key'));
    $(preview).find('img').attr('src', img).show();
    $(preview).find('h4').html(heading);
    $(preview).find('p').html(description);
    $(preview).removeClass('hidden');
    $(obj).closest('.flexform-setup-block-list').find('.flexform-setup-block-list__item').removeClass('active');
    $(obj).parent().addClass('active');
    return false;
});

$(document).on('click', '#flexform-setup-use-this-block', function() {
    const block_type = $('#flexform-setup-block-type').val();
    const id = $('#flexform-id').val();
    const msg = $(this).data('msg');
    if (block_type === '') {
        alert_float('danger', msg);
        return false;
    }
    const data = {action : 'add_new_block',block_type: block_type, id: id};
    const url = $('#flexform-ajax-url').val();
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
         if(r.status === 'error') {
             alert_float('danger', r.message);
            return false;
         }
        alert_float('success', r.message);
        $('#flexform-add-block-modal').modal('hide');
        //append the new block
        $('.flexform_blocks_list').append(r.left_hand_side);
        $('.flexform-setup-panel-body_middle').html(r.middle_content);
        $('.flexform-setup-panel-body_rhs').html(r.right_hand_side);
        //reset the form
        $('#flexform-setup-block-type').val('');
        $('.flexform-setup-block-preview').addClass('hidden');
        $('.flexform-setup-block-list__item').removeClass('active');
        flexform_inits();

     });
    return false;
});

//load block block by id
$(document).on('click', '.flexform-block__cta', function() {
    const obj = $(this);
    const id = $(obj).data('id');
    const block_to_be_active = $(obj).closest('.ff-each-block');
    flexform_load_block_by_id(id,block_to_be_active);
    return false;
});
function flexform_load_block_by_id(id,block_to_be_active) {
    const url = $('#flexform-ajax-url').val();
    const data = {action : 'load_block', id: id};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        $('#flexform-block-id').val(id);
        $('.flexform-setup-panel-body_middle').html(r.middle_content);
        $('.flexform-setup-panel-body_rhs').html(r.right_hand_side);
        $('.flexform-block__cta').closest('.ff-each-block').removeClass('active');
        $(block_to_be_active).addClass('active');
        flexform_inits()
    });
}
//listen to when quecstion field changes and update the question UI and auto submit the form
$(document).on('keyup',
    '.flexform-question-title,' +
    '.flexform-question-desc,' +
    '.ff-options-wrapper input,' +
    '.flexform-question-number-min,' +
    '.flexform-question-number-max,' +
    '.flexform-question-button-text,' +
    '.flexform-thankyou-redirect-url,' +
    '.flexform-thankyou-redirect-message,' +
    '.flexform-file-types,' +
    '.flexform-question-left-right-label-text,' +
    '.flexform-question-placeholder', function() {
    const obj = $(this);
    let type = '';
    if($(obj).hasClass('flexform-question-title')) {
        type = 'question';
    }else if($(obj).hasClass('flexform-question-desc')) {
        type = 'desc';
    }else if($(obj).hasClass('flexform-question-placeholder')) {
        type = 'placeholder';
    }else if ($(obj).hasClass('flexform-question-button-text')) {
        type = 'button';
    }
    flexform_update_setup_preview_ui(obj, type);
    flexform_ajax_autosubmit_form(obj);
    return true;
});

$(document).on('change', '.ff-is-required,.ff-rating,.ff-redirect-delay,.ticket_list_type_select', function() {
    //check if the name of the field is is_country
    const obj = $(this);
    const name = $(obj).attr('name');
    if(name === 'is_country' || name === 'ticket_list_type') {
        const val = $(obj).val();
        if(name === 'is_country') {
            if(val == 0) {
                $('.flexform-new-options-wrapper-fields').removeClass('hidden');
                $('.ticket-list-types').removeClass('hidden');
            }else {
                $('.flexform-new-options-wrapper-fields').addClass('hidden');
                $('.ticket-list-types').addClass('hidden');
            }
        }else if(name === 'ticket_list_type') {
            if(val == '') {
                $('.flexform-new-options-wrapper-fields').removeClass('hidden');
            }else{
                $('.flexform-new-options-wrapper-fields').addClass('hidden');
            }
        }
    }
    flexform_ajax_autosubmit_form(this);
    return true;
});

function flexform_update_setup_preview_ui(obj, type) {
    const val = $(obj).val();
    const preview = $('#flexform_blocks_preview');
    if(type === 'question') {
        $(preview).find('.flexform-title-preview').html(val);
        //update the block title as well but limit it to only 20 characters
        const title = val.length > 30 ? val.substring(0, 30) + '...' : val;
        $('.ff-each-block.active').find('.flexform-block__cta span.ff-label').html(title);
    }
}

function flexform_ajax_autosubmit_form(obj) {
    const form = $(obj).closest('form');
    if(form.length === 0) {
        return false;
    }
    $.ajax({
        url: $(form).attr('action'),
        type: 'POST',
        data: new FormData(form[0]),
        contentType: false,
        processData: false,
        success: function(response) {
           //update the middle panel
            const r = JSON.parse(response);
            if(r.status === 'error') {
                alert_float('danger', r.message);
                return false;
            }
            $('.flexform-setup-panel-body_middle').html(r.middle_content);
            if(r.publish_status){
                $('.flexform-publish-status').html(r.publish_status);
            }
            flexform_inits();
        },
        error: function(xhr, status, error) {
        }
    });
}
//Align btn on statement Block
$(document).on('click', '.flexform-align-btn', function() {
    //uncheck all other radio buttons
    $(this).closest('.form-group').find('input').attr('checked', false);
    //remove all bg-neutral from all buttons
    $(this).closest('.form-group').find('.flexform-align-btn').removeClass('bg-primary');
    //add bg-neutral to this button
    $(this).addClass('bg-primary');
    //check the radio button
    $(this).closest('.ff-align-parent').find('input').attr('checked', true);
    //update the preview
    flexform_ajax_autosubmit_form(this);
});

//flexform_handle_image_upload
function flexform_handle_image_upload(input){
    //preview the image on the form
    const preview = $('.ff-image-preview img'); //preview on the form
    const file = input.files[0];
    const reader = new FileReader();
    reader.onloadend = function() {
        $(preview).attr('src', reader.result);
        //preview the form on the middle preview panel
    };
    if(file) {
        reader.readAsDataURL(file);
    }
    //show the image preview
    $('.ff-image-preview').removeClass('hidden');
    $('.ff-image-upload-wrapper').addClass('hidden');
    //upload the image
    flexform_ajax_autosubmit_form(input);
}
//remove the image
$(document).on('click', '.ff-image-preview_remove_btn', function() {
    if (confirm_delete()) {
        const preview = $('.ff-image-preview img'); //preview image
        $(preview).attr('src', '');
        $('.ff-image-preview').addClass('hidden');
        $('.ff-image-upload-wrapper').removeClass('hidden');

        //hide the image on the middle preview panel
        $('.ff-statement-image img').attr('src', '');
        $('.ff-statement-image').addClass('hidden');
        //ajax call to remove the image in the backend
        $.ajax({
            url: $('#flexform-ajax-url').val(),
            type: 'POST',
            data: {action: 'remove_image', id: $('#flexform-block-id').val()},
            success: function(response) {
                //console.log(response);
            },
            error: function(xhr, status, error) {
            }
        });
    }
});

//add new option
$(document).on('click', '#ff-add-option', function() {
    const container = $('.ff-options-wrapper');
    $(container).append('<div class="option">\n' +
        '                <input type="text" class="form-control option__input" name="options[]" value="" />\n' +
        '                <a href="#" class="option__remove text-danger tw-p-1">\n' +
        '                    <i class="fa fa-trash"></i>\n' +
        '                </a>\n' +
        '            </div>');
    return false;
});

//remove option
$(document).on('click', '.option__remove', function() {
    if (confirm_delete()) {
        $(this).closest('.option').remove();
        flexform_ajax_autosubmit_form($('#flexform-block-id')); //auto submit the form
    }
    return false;
});

//if the middle is clicked
$(document).on('click', '#flexform_blocks_preview', function() {
    //shake this input
    $('.flexform-question-title').addClass('ff-shake');
    setTimeout(function() {
        $('.flexform-question-title').removeClass('ff-shake');
    }, 1000);
});

//active star rating
$(document).on('click', '.flexform-rating__stars label', function() {
    const obj = $(this);
    //remove active from all these star
    $(obj).closest('.flexform-rating__stars').find('label').removeClass('active-star');
    //add active to this star and all the stars before it
    $(obj).addClass('active-star');
    $(obj).prevAll().addClass('active-star');

});

$(document).on('change','#notify_form_submission', function() {
    $('.select-notification-settings').toggleClass('hide');
});

function flexform_inits(){
    init_datepicker();
    init_color_pickers();
    appSelectPicker();
    flexform_init_signature();
}

/** load block logic form **/
$(document).on('click', '.flexform-block-logic__cta', function() {
    const obj = $(this);
    const id = $(obj).data('id');
    const url = $('#flexform-ajax-url').val();
    const data = {action : 'load_block_logic', id: id};
    const modal = $('#flexform-logic-to-question');
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        $(modal).find('.modal-body').html(r.html);
        $(modal).modal('show');
        //init refresh selectpicker
        $('#flexform-logic-to-question form .selectpicker').selectpicker('refresh');
        flexform_inits();
    });
    return false;
});

//delete block
$(document).on('click', '.flexform-delete-block-cta', function() {
    if (confirm_delete()) {
        const obj = $(this);
        const id = $(obj).data('id');
        const url = $('#flexform-ajax-url').val();
        const data = {action : 'delete_block', id: id};
        $.post(url, data, function(response) {
            const r = JSON.parse(response);
            if(r.status === 'error') {
                alert_float('danger', r.message);
                return false;
            }
            const parent = $(obj).closest('.ff-each-block');
            if($(parent).hasClass('active')) {
                $('.flexform-setup-panel-body_middle').html('');
                $('.flexform-setup-panel-body_rhs').html('');
            }
            $(parent).remove();
            flexform_inits();
            //update the order
            flexform_update_actions_order();
        });
    }
    return false;
});

//when the question is changed, update the operator and value field
function flexform_logic_if_question_changed(obj,i) {
    const v = $(obj).val(); //block id
    //make ajax request to show the right operator and value field
    const url = $('#flexform-ajax-url').val();
    const data = {action : 'load_block_logic_operator_and_value_field', id: v, index: i};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        $(obj).closest('.flexform-condition-wrapper_each').find('.flexform-command-and-form-wrapper').html(r.html);
        //inits
        flexform_inits();
    });
}

//add new condition to a logic
$(document).on('click', '.flexform-add-logic-condition', function() {
    //get the index of the logic we are adding the condition to
    const container = $(this).closest('.flexform_block_logic_wrapper_each').find('.flexform-condition-wrapper');
    const index = $(this).data('index');
    //get new condition from the backend
    const url = $('#flexform-ajax-url').val();
    const data = {action : 'add_new_logic_condition_field', id: $('#flexform-block-id').val(), index: index};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        $(container).append(r.html);
        flexform_inits();
    });
    return false;
});

//remove condition when clcikc on the remove button
$(document).on('click', '.flexform-remove-next-logic-condition', function() {
    if (confirm_delete()) {
        $(this).closest('.flexform-condition-wrapper_each').remove();
    }
    return false;
});

//add new logic
$(document).on('click', '.flexform-block-add-logic-cta', function() {
    const url = $('#flexform-ajax-url').val();
    //get the last element on the page with class flexform_block_logic_wrapper_each and get the index from the data-index attribute
    var index = 0;
    if($('.flexform_block_logic_wrapper_each').length > 0) {
         index = $('.flexform_block_logic_wrapper_each').last().data('index') + 1;
    }

    const data = {action : 'add_new_logic', id: $('#flexform-block-id').val(), index: index};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        $('.flexform_block_logic_wrapper').append(r.html);
        flexform_inits();
    });
    return false;
});

//delete logic
$(document).on('click', '.flexform-remove-logic-btn', function() {
    if (confirm_delete()) {
        $(this).closest('.flexform_block_logic_wrapper_each').remove();
    }
    return false;
});

//order and update blocks arrangment
function flexform_initialize_sortable_action(){
    const container = $('#flexform_blocks_list_container');
    if($(container).length) {
        $(container).sortable({
            placeholder: "ui-state-highlight-flexform",
            update: function (event, ui) {
                console.log(event);
                console.log(ui);
                const id = $(ui.item).data('id');
                // Update actions order
                flexform_update_actions_order();
                //load the block
                flexform_load_block_by_id(id,ui.item);
            }
        });
    }
}

//update actions order
function flexform_update_actions_order() {
    const blocks = [];
    $('#flexform_blocks_list_container .ff-each-block').each(function() {
        blocks.push($(this).data('id'));
        //update the order number with this class ff-block-index-text
        $(this).find('.ff-block-index-text').html(blocks.length + '.');
    });
    const url = $('#flexform-ajax-url').val();
    const data = {
        action: 'update_block_order',
        blocks: blocks,
    };
    $.post(url, data);
}

//load publish_form_content via ajax and display it in the modal
$(document).on('click', '#flexform-publish-form-cta', function() {
    const url = $('#flexform-ajax-url').val();
    const data = {action : 'publish_form_content', id: $('#flexform-id').val()};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        const modal = $('#flexform_publish_modal');
        modal.find('.modal-body').html(r.html);
       modal.modal('show');
    });
    return false;
});

//copy to clipboard
function flexformcopyToClipboard(obj) {
    let type = $(obj).data('type');
    const success_msg = $(obj).data('smsg');
    const error_msg = $(obj).data('emsg');
    let value = '';
    if(type == 'link') {
        value = $('#flexform-share-link').val();
    }else if(type == 'embed-code') {
        //value is in the code tag
        value = $('#flexform-publish-form-embed-code').text();
    }
    // Use the Clipboard API to copy the content
    navigator.clipboard.writeText(value).then(function() {
        // Success feedback
        alert_float('success', success_msg);
    }).catch(function(err) {
        // Error feedback
        alert_float('danger', error_msg + ' ' + err);
    });
    return false
}

//delete responses
$(document).on('click', '.flexform-delete-responses', function() {
    const obj = $(this);
    if (confirm_delete()) {
        const url = $(obj).data('url');
        const session_id = $(obj).data('ssid');
        const fid = $(obj).data('fid');
        const data = {action : 'delete_responses', ssid : session_id, fid: fid};
        $.post(url, data, function(response) {
            const r = JSON.parse(response);
            if(r.status === 'error') {
                alert_float('danger', r.message);
                return false;
            }
            //remove the row
            $(obj).closest('tr').remove();
            alert_float('success', r.message);
            //reload the page
            location.reload();
        });
    }
    return false;
});

//on change of privacy, show the right customer or staff ids
$(document).on('change', '.flexform-privacy-select', function() {
    const val = $(this).val();
    //hide all
    $('.flexform-privacy-customer').hide();
    $('.flexform-privacy-staff').hide();
    if(val == 'customers') {
        $('.flexform-privacy-customer').show();
    }
    if(val == 'staff') {
        $('.flexform-privacy-staff').show();
    }
    console.log(val);
    
});

//load detail submimssion/response
$(document).on('click', '.flexform-view-response', function() {
    const obj = $(this);
    const url = $(obj).data('url');
    const session_id = $(obj).data('ssid');
    const fid = $(obj).data('fid');
    const active = $(obj).data('active');
    const data = {action : 'load_response', ssid : session_id, fid: fid, active: active};
    $.post(url, data, function(response) {
        const r = JSON.parse(response);
        if(r.status === 'error') {
            alert_float('danger', r.message);
            return false;
        }
        const modal = $('#flexform_view_response_modal');
        modal.find('.modal-body').html(r.html);
        modal.modal('show');
    });
    return false;
});


$(function(){
    flexform_initialize_sortable_action();
});
function flexform_init_signature(){
    //check if signature ui is present
    if($('.ff-signature-wrapper').length === 0) return false;
    SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
        var canvas = this._ctx.canvas;
        // First duplicate the canvas to not alter the original
        var croppedCanvas = document.createElement('canvas'),
            croppedCtx = croppedCanvas.getContext('2d');

        croppedCanvas.width = canvas.width;
        croppedCanvas.height = canvas.height;
        croppedCtx.drawImage(canvas, 0, 0);

        // Next do the actual cropping
        var w = croppedCanvas.width,
            h = croppedCanvas.height,
            pix = {
                x: [],
                y: []
            },
            imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
            x, y, index;

        for (y = 0; y < h; y++) {
            for (x = 0; x < w; x++) {
                index = (y * w + x) * 4;
                if (imageData.data[index + 3] > 0) {
                    pix.x.push(x);
                    pix.y.push(y);

                }
            }
        }
        pix.x.sort(function(a, b) {
            return a - b
        });
        pix.y.sort(function(a, b) {
            return a - b
        });
        var n = pix.x.length - 1;

        w = pix.x[n] - pix.x[0];
        h = pix.y[n] - pix.y[0];
        var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

        croppedCanvas.width = w;
        croppedCanvas.height = h;
        croppedCtx.putImageData(cut, 0, 0);

        return croppedCanvas.toDataURL();
    };


    function signaturePadChanged() {

        var input = document.getElementById('signatureInput');
        var $signatureLabel = $('#signatureLabel');
        $signatureLabel.removeClass('text-danger');

        if (signaturePad.isEmpty()) {
            $signatureLabel.addClass('text-danger');
            input.value = '';
            return false;
        }

        $('#signatureInput-error').remove();
        var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
        partBase64 = partBase64.split(',')[1];
        input.value = partBase64;
    }

    var canvas = document.getElementById("signature");
    var clearButton = wrapper.querySelector("[data-action=clear]");
    var undoButton = wrapper.querySelector("[data-action=undo]");
    var identityFormSubmit = document.getElementById('identityConfirmationForm');

    var signaturePad = new SignaturePad(canvas, {
        maxWidth: 2,
        onEnd:function(){
            signaturePadChanged();
        }
    });

    clearButton.addEventListener("click", function(event) {
        signaturePad.clear();
        signaturePadChanged();
    });

    undoButton.addEventListener("click", function(event) {
        var data = signaturePad.toData();
        if (data) {
            data.pop(); // remove the last dot or line
            signaturePad.fromData(data);
            signaturePadChanged();
        }
    });

    $('#identityConfirmationForm').submit(function() {
        signaturePadChanged();
    });

}

function flexformDownloadModal(obj){
    //disable the download button
    $(obj).prop('disabled', true);
    const modalContent = document.getElementById('flexformSubmissionAnswers');
    try{
        const opt = {
            margin:       [20, 10, 20, 10], // [top, left, bottom, right]
            filename:     'submission.pdf',
            html2canvas:  { scale: 2 },      // Improve quality
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf()
            .from(modalContent)
            .set(opt)
            .save();
        //enable the download button
        $(obj).prop('disabled', false);
    }catch (e){
        console.log(e);
    }
}