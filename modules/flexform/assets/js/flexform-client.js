"use strict";

var flexform_files = [];

function flexform_init_dropzone(limit = 1){
    if ($('#dropzoneDragArea').length > 0) {
        //prevent dropzon already initialized
        if (Dropzone.instances.length > 0) {
            Dropzone.instances.forEach(function(instance) {
                instance.destroy();
            });
        }
        const form = $('#dropzoneDragArea').closest('form');
        const formAction = $(form).attr('action');
        const removeButton = $('#flexform-remove-files');
        Dropzone.autoDiscover = false;
        // Initialize Dropzone
        // Initialize Dropzone
        var myDropzone = new Dropzone("#dropzoneDragArea", {
            url: formAction,
            autoProcessQueue: false,
            maxFiles: limit,
            previewsContainer: '.dropzone-previews',
            init: function() {
                var myDropzone = this; // Closure

                // Remove button event
                removeButton.on("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    myDropzone.removeAllFiles(true); // Remove all files from the Dropzone
                    // Hide the remove button
                    $(removeButton).hide();
                    // Clear the global array
                    flexform_files = [];
                });

                this.on("addedfile", function(file) {
                    //add the file to the global array
                    flexform_files.push(file);
                    $(removeButton).show();
                });

                this.on("maxfilesexceeded", function(file) {
                    this.removeFile(file); // Remove the file that exceeds the limit
                });

                this.on("complete", function(file) {
                    // Handle the response after file upload is complete
                });
            }
        })
    }
}
function flexform_append_files_to_form(){
    if(flexform_files.length === 0) return false;
    var hiddenInputsContainer = $('#flexform-files-input');
    flexform_files.forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(event) {
            var base64String = event.target.result.replace(/^data:[a-zA-Z0-9]+\/[a-zA-Z0-9]+;base64,/, '');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'files[]';
            input.value = base64String;
            $(hiddenInputsContainer).append(input);

            var nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'file_names[]';
            nameInput.value = file.name;
            hiddenInputsContainer.append(nameInput);

            var typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'file_types[]';
            typeInput.value = file.type;
            hiddenInputsContainer.append(typeInput);
        };
        reader.readAsDataURL(file);
    });
}
$(document).on('click', '.flexform-client-block-container .ff-submit-button', function() {
    //check if there are files to upload
    const obj = $(this);
    //delay the form submission to allow the files to be appended to the form
    if(flexform_files.length > 0) {
        flexform_append_files_to_form();
        setTimeout(function() {
            flexform_submit_form_data(obj);
        }, 1000);
    }else{
        flexform_submit_form_data(obj);
    }
    return false;
});

function flexform_submit_form_data(obj){
    //disable the button
    $(obj).prop('disabled', true);
    var form = $(obj).closest('form');
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
                //enable the button
                $(obj).prop('disabled', false);
                return false;
            }
            if(r.status === 'info') {
                alert_float('info', r.message);
                //enable the button
                $(obj).prop('disabled', false);
                //shake the form
                return false;
            }
            flexform_update_client_ui(r);
        },
        error: function(xhr, status, error) {
        }
    });
}

//footer navigation
$(document).on('click', '.flexform-footer-actions button', function() {
    const type = $(this).data('type');
    const id = $(this).data('id');
    const url = $(this).data('url');

    //post the data
    $.ajax({
        url: url,
        type: 'GET',
        data: {
            type: type,
            current: id,
            ff_bnh: $('#ff_bnh').val(),
            svalue : $('#ff_svalue').val()
        },
        success: function(response) {
            const r = JSON.parse(response);
            if(r.status === 'error') {
                alert_float('danger', r.message);
                return false;
            }
            flexform_update_client_ui(r);
        },
        error: function(xhr, status, error) {
        }
    });
    return false;
});

function flexform_update_client_ui(r){
    //check if is_submit is returned
    if(r.is_submit) {
        //we are updating the button text to show that the form is being submitted
        $('.flexform-sumbit-button-wrapper').html(r.html);
        return false;
    }

    $('.flexform-client-block-container').fadeOut(400, function() {
        $('.flexform-client-block-container').html(r.html);
        $('.flexform-client-block-container').fadeIn(400);
        const upload_limit = r.upload_limit ? parseInt(r.upload_limit) : 1;
        flexform_client_inits(upload_limit);
    });
    //update the nav_footer_link
    $('.flexform-footer-actions').html(r.nav_footer_link);
    //update the navigation history
    $('#ff_bnh').val(r.nav_logs);
    //update the progress bar
    const percentage_complete = r.current_percentage_completed;
    //remove the previous progress bar and animate the new one
    $('body').find('.flexform-progress-bar').remove();
    //animate the progress bar
    const $progressBar = $('<div class="flexform-progress-bar"></div>').css({width: '0%', backgroundColor: '#000'});
    $('body').append($progressBar);
    $progressBar.animate({ width: percentage_complete + '%' }, 1000);

    //if the form is 100%, clear the local storage
    if(percentage_complete === 100){
        const formSessionName = $('#ff_sname').val();
        localStorage.removeItem(formSessionName);
    }
}

//active star rating
$(document).on('click', '.flexform-rating__stars label', function() {
    const obj = $(this);
    //remove active from all these star
    $(obj).closest('.flexform-rating__stars').find('label').removeClass('active-star');
    //add active to this star and all the stars before it
    $(obj).addClass('active-star');
    $(obj).prevAll().addClass('active-star');
});

function flexform_client_inits(limit = 1){
    appColorPicker();
    appDatepicker();
    $('select').selectpicker('destroy');
    appSelectPicker($('select'));
    $(".bootstrap-select").click(function () {
        $(this).addClass("open");
    });
    flexform_init_signature();
    flexform_init_dropzone(limit);
}

function flexform_init_signature() {
    // Check if signature UI is present
    if ($('.ff-signature-wrapper').length === 0) return false;

    SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
        var canvas = this._ctx.canvas;
        var croppedCanvas = document.createElement('canvas'),
            croppedCtx = croppedCanvas.getContext('2d');

        croppedCanvas.width = canvas.width;
        croppedCanvas.height = canvas.height;
        croppedCtx.drawImage(canvas, 0, 0);

        var w = croppedCanvas.width,
            h = croppedCanvas.height,
            pix = { x: [], y: [] },
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
        pix.x.sort((a, b) => a - b);
        pix.y.sort((a, b) => a - b);
        var n = pix.x.length - 1;

        w = pix.x[n] - pix.x[0];
        h = pix.y[n] - pix.y[0];
        var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

        croppedCanvas.width = w;
        croppedCanvas.height = h;
        croppedCtx.putImageData(cut, 0, 0);

        return croppedCanvas.toDataURL();
    };

    $('.ff-signature-wrapper').each(function(index, wrapper) {
        var $wrapper = $(wrapper);
        var canvas = $wrapper.find("canvas")[0];
        var clearButton = $wrapper.find("[data-action=clear]")[0];
        var undoButton = $wrapper.find("[data-action=undo]")[0];
        var input = $wrapper.find("input")[0];

        var signaturePad = new SignaturePad(canvas, {
            maxWidth: 2,
            onEnd: function() {
                signaturePadChanged(signaturePad, input);
            }
        });

        function signaturePadChanged(signaturePad, input) {
            if (signaturePad.isEmpty()) {
                $(input).val('');
                return false;
            }

            var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
            partBase64 = partBase64.split(',')[1];
            $(input).val(partBase64);
        }

        clearButton.addEventListener("click", function(event) {
            signaturePad.clear();
            signaturePadChanged(signaturePad, input);
        });

        undoButton.addEventListener("click", function(event) {
            var data = signaturePad.toData();
            if (data) {
                data.pop();
                signaturePad.fromData(data);
                signaturePadChanged(signaturePad, input);
            }
        });
    });

    $('#identityConfirmationForm').submit(function() {
        $('.ff-signature-wrapper').each(function(index, wrapper) {
            var canvas = $(wrapper).find("canvas")[0];
            var input = $(wrapper).find("input")[0];
            var signaturePad = new SignaturePad(canvas);
            signaturePadChanged(signaturePad, input);
        });
    });
}

//inits when the page loads
function flexform_get_user_session(){
    const formSessionName = $('#ff_sname').val();
    return localStorage.getItem(formSessionName);
}
$(document).ready(function() {
    flexform_client_inits();
    //save the form session name and session value in the local storage
    const formSessionName = $('#ff_sname').val();
    //check if the session already exists in the local storage
    //if it does, populate the form with the value from localstorage
    //this will help to persist the form data when the page is refreshed and the form is not submitted 100%
    var formSessionValue = localStorage.getItem(formSessionName);
    if(formSessionValue) {
        $('#ff_svalue').val(formSessionValue);
    }else{
        //save in the local storage
        formSessionValue = $('#ff_svalue').val();
        localStorage.setItem(formSessionName, formSessionValue);
    }
    //navigation history, clear navigation history when the page is refreshed
    //const navHisName = 'bnh_'+formSessionName;
    /*localStorage.removeItem(navHisName)
    //add the block now nav history;
    const cbi = $('#ff_cbi').val();
    //save the current in the block history
    localStorage.setItem(navHisName, cbi);*/

});

//listen to when an input is changed for spa
$(document).on('change', '.flexform-sp-client-form input,.flexform-sp-client-form textarea,.flexform-sp-client-form select', function(e) {
   //get the field value and name
    //if the input is a a file input, we will not send the data to the server
    if($(this).attr('type') === 'file') return false;
    //if the name is a checkbox and name is terms_and_conditions, we will not send the data to the server
    if($(this).attr('type') === 'checkbox' && $(this).attr('name') === 'terms_and_conditions') return false;
    const name = $(this).attr('name');
    var value = $(this).val();
    //check if name is checkbox
    if($(this).attr('type') == 'checkbox'){
        //use the name to get the value
        const escapedName = name.replace(/([\[\]])/g, '\\$1'); // Escape square brackets
        value =  $(`input[name='${escapedName}']:checked`).map(function () {
            return $(this).val(); // Return the value of each checked checkbox
        }).get();
    }

    //let us send
    //console.log('send the data to the server');
    const currentBlockId = $(this).closest('.flexform-single-page-each-block').data('block-id');
    const url = $('.flexform-single-page-layout').attr('data-spurl'); //skip questions url
    //search for the csrf token
    //any input with the name csrf token
    const csrfName = $('#flexform-spa-token').attr('name');
    const csrfHash = $('#flexform-spa-token').val();
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            name: name,
            value: value,
            id: currentBlockId,
            ff_bnh: $('#ff_bnh').val(),
            svalue : $('#ff_svalue').val(),
            [csrfName]: csrfHash
        },
        success: function(response) {
            const r = JSON.parse(response);
            if(r.status === 'error') {
                alert_float('danger', r.message);
                return false;
            }
            var currentBlockIdIndex = flexform_getblock_index(currentBlockId);
            //if the skipped_blockes array is returned, loop through it and hide the blocks
            if(r.skipped_blocks){
                $('.flexform-single-page-each-block').each(function(i){
                    const blockId = parseInt($(this).data('block-id'));
                    //show the blocks whose index is ahead of the current block
                    if(i > currentBlockIdIndex){
                        $(this).show();
                    }
                });

                r.skipped_blocks.forEach(function(block){
                    $('.flexform-single-page-each-block[data-block-id="'+block+'"]').hide();
                });
            }else{
                //if the skipped_blocks array is not returned, show all the blocks after the current block
                $('.flexform-single-page-each-block').each(function(i){
                    const blockId = parseInt($(this).data('block-id'));
                    //show the blocks whose index is ahead of the current block
                    if(i > currentBlockIdIndex){
                        $(this).show();
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', error);
        }
    });
});

function flexform_getblock_index(blockId){
    var blockIndex = -1;
    $('.flexform-single-page-each-block').each(function(index){
        if(parseInt($(this).data('block-id')) === blockId){
            blockIndex = index;
        }
    });
    return blockIndex;
}

//listen to when submit button is clicked for Spa
$(document).on('click', '.flexform-sp-client-form .ff-submit-button', function() {
    //get the field value and name
    const obj = $(this);
    $(obj).prop('disabled', true);
    var form = $(obj).closest('form');
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
                try{
                    grecaptcha.reset();
                }catch (e) {
                }
                //enable the button
                $(obj).prop('disabled', false);
                return false;
            }
            if(r.status === 'info') {
                alert_float('info', r.message);
                try{
                    grecaptcha.reset();
                }catch (e) {
                }
                //enable the button
                $(obj).prop('disabled', false);
                //shake the form
                return false;
            }
            if(r.html){
                $('.flexform-single-page-layout').html(r.html);
                flexform_client_inits();
                //remove the session value from the local storage
                const formSessionName = $('#ff_sname').val();
                localStorage.removeItem(formSessionName);
            }
        },
        error: function(xhr, status, error) {
        }
    });
});

