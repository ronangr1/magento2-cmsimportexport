define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';
    return function (config, element) {
        const $form = $(element),
            actionUrl = config.actionUrl,
            $submit = $('#cms-import-submit'),
            $result = $('#cms-import-result'),
            $dropZone = $(element);

        function handleFile(file) {
            if (!file) {
                alert({content: $t('Please select a file.')});
                return;
            }

            const entityType = $form.find('input[name="entity_type"]').val(),
                formData = new FormData();

            formData.append('import_file', file);
            formData.append('entity_type', entityType);
            formData.append('form_key', window.FORM_KEY);

            $submit.prop('disabled', true).text($t('Uploading…'));
            $result.empty();

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            })
                .done(function (response) {
                    if (response.success) {
                        $result.html(
                            '<div class="result-message result-message-success success">' +
                            response.message +
                            '</div>'
                        );
                    } else {
                        $result.html(
                            '<div class="result-message result-message-error error">' +
                            response.message +
                            '</div>'
                        );
                    }
                })
                .fail(function () {
                    $result.html(
                        '<div class="result-message result-message-error error">' +
                        $t('An error occurred while importing the data.') +
                        '</div>'
                    );
                })
                .always(function () {
                    $submit.prop('disabled', false).text($t('Import'));
                    $result.delay(1000).fadeOut(500)
                });
        }

        $submit.on('click', function () {
            const fileInput = $form.find('input[type=file]')[0];
            if (!fileInput.files.length) {
                alert({content: $t('Please select a file.')});
                return;
            }
            handleFile(fileInput.files[0]);
        });

        $dropZone.on('dragover', function (e) {
            e.preventDefault();
            $dropZone.addClass('drag-over');
        });

        $dropZone.on('drop', function (e) {
            e.preventDefault();
            $dropZone.removeClass('drag-over');

            const dt = e.originalEvent.dataTransfer;

            if (dt.items) {
                Array.from(dt.items).forEach(function (item) {
                    if (item.kind === 'file') {
                        handleFile(item.getAsFile());
                    }
                });
            } else if (dt.files) {
                Array.from(dt.files).forEach(function (file) {
                    handleFile(file);
                });
            }
        });
    };
});
