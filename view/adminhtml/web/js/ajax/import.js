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
            $result = $('#cms-import-result');

        $submit.on('click', function () {
            const fileInput = $form.find('input[type=file]')[0];
            if (!fileInput.files.length) {
                alert({content: $t('Please select a file.')});
                return;
            }

            const entityType = $form.find('input[name="entity_type"]').val();

            const formData = new FormData();
            formData.append('import_file', fileInput.files[0]);
            formData.append('entity_type', entityType);
            formData.append('form_key', window.FORM_KEY);

            $submit.prop('disabled', true);
            $submit.text($t('Uploading…'));

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            }).done(function (response) {
                if (response.success) {
                    $result.html('<div class="message message-success success">' + response.message + '</div>');
                } else {
                    $result.html('<div class="message message-error error">' + response.message + '</div>');
                }
            }).fail(function () {
                $result.html('<div class="message message-error error">' + $t('An error occurred while importing the data.') + '</div>');
            }).always(function () {
                $submit.prop('disabled', false);
                $submit.text($t('Import'));
            });
        });
    };
});
