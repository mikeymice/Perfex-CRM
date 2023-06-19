<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
.hovering::before {
    content: "Drop here...";
    display: block;
    background-color: rgba(226, 232, 240, 0.4);
    padding: 1rem;
    border-radius: 0.5rem;
    border: 2px dotted #cbd5e0;
    text-align: center;
}
</style>

<div id="wrapper" class="flex">
    <div class="content w-[-webkit-fill-available]">

        <div class="container mx-auto py-5 h-full">

            <div class="flex flex-row gap-4 h-full">

                <!-- Field Types Section -->
                <div class="w-[350px] flex flex-col gap-4 p-4 border-2 border-solid border-gray-200 rounded bg-white">

                    <h3 class="text-lg font-semibold">Field Types</h3>
                    <div class="w-full border-b-2 border-dashed border-white-200"></div>
                    <div class="space-y-4" id="form-cart">
                        <?php
                        $field_types = [
                        ['type' => 'text', 'label' => 'Text Input'],
                        ['type' => 'textarea', 'label' => 'Text Area'],
                        ['type' => 'select', 'label' => 'Select'],
                        ['type' => 'numbers', 'label' => 'Numbers'],
                        ['type' => 'date', 'label' => 'Date'],
                        ['type' => 'checkbox', 'label' => 'Checkbox'],
                        ['type' => 'radio', 'label' => 'Radio Button'],
                        
                        // Add more field types here
                        ];
                        foreach ($field_types as $field_type) {
                        ?>
                        <div class="bg-gray-100 p-4 rounded-lg border-2 border-dotted border-gray-300 cursor-move form-field" data-field-type="<?php echo $field_type['type']; ?>">
                            <?php echo $field_type['label']; ?>
                        </div>
                        <?php
                        }
                        ?>
                    </div>

                </div>

                <!-- Form Preview Section -->
                <div class="w-full flex flex-col gap-4 p-4 border-2 border-solid border-gray-200 rounded bg-white">
                    <h3 class="text-lg font-semibold">Form Preview</h3>
                    <div class="w-full border-b-2 border-dashed border-white-200"></div>
                    <div class="bg-white rounded-lg border border-gray-300 h-full flex flex-col gap-4" id="form-preview">

                    <?php
                        $fieldsData = json_decode($form_fields[0]->fields_data, true);
                        foreach ($fieldsData as $field):

                        $fieldType = $field['type'];
                        $fieldName = $field['name'];
                        $fieldOptions = '';
                        if (isset($field['options'])) {
                            $fieldOptionsArray = json_decode($field['options'], true);
                            if(is_array($fieldOptionsArray)){
                                $fieldOptions = implode("/newline", $fieldOptionsArray);
                            }  
                        }

                        $fieldMin = isset($field['min']) ? $field['min'] : '';
                        $fieldMax = isset($field['max']) ? $field['max'] : '';
                        $fieldMaxLength = isset($field['maxLength']) ? $field['maxLength'] : '';
                    ?>
                        <div class="form-field bg-gray-100/40 rounded-lg border-2 border-dotted border-gray-300 flex flex-row justify-between items-center" data-field-type="<?= $fieldType; ?>" data-field-name="<?= $fieldName; ?>" data-field-options="<?= $fieldOptions; ?>" data-field-min="<?= $fieldMin; ?>" data-field-max="<?= $fieldMax; ?>" data-field-max-length="<?= $fieldMaxLength; ?>">
                            <div class="p-4 field-name capitalize"><?= $fieldName; ?></div>
                            <div class="flex flex-row gap-4 pr-4">
                                <button onclick='showFieldCustomizationModal($(this).closest(".form-field"));' class="edit-field"><i class="fas fa-pencil-alt"></i></button>
                                <button onclick='if (confirm("Are you sure you want to delete this field?")) {$(this).closest(`.form-field`).remove();};' class="delete-field"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>



                    </div>

                    <div class="bg-white h-12 flex flex-row justify-between gap-2">

                    <div>
                        <button class="rounded transition-all bg-blue-600 text-white hover:bg-white hover:text-blue-500 hover:border-blue-500 border border-solid px-4 py-2">Open tempelates</button>
                        <button class="rounded transition-all bg-blue-600 text-white hover:bg-white hover:text-blue-500 hover:border-blue-500 border border-solid px-4 py-2">Save as a template</button>
                    </div>

                    <div>
                        <a href="<?php echo admin_url("recruitment_portal/campaigns"); ?>"  class="inline-block rounded transition-all bg-blue-600 text-white hover:bg-white hover:text-blue-500 hover:border-blue-500 border border-solid px-4 py-2">Go Back</a>
                        <button id="save-form-fields" class="rounded transition-all bg-blue-600 text-white hover:bg-white hover:text-blue-500 hover:border-blue-500 border border-solid px-4 py-2">Save</button>
                    </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Modal for field customization -->
<div id="field-customization-modal" class="modal fade">

    <div class="modal-dialog">
        <div class="modal-content">     
            <div class="modal-header text-white py-3">
                <h5 class="modal-title text-xl" id="campaignAddLabel">Edit Field</h5>
            </div>
            <div class="modal-body">
                <!-- Text field customization form -->

                <div id="field-text-options" class="field-options p-4 border-t border-gray-200">

                    <div class="flex items-center mb-4">
                        <label for="text-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="text-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>

                    <div class="flex items-center mb-4">
                        <label for="text-max-length" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Max-Length:</label>
                        <input type="number" id="text-max-length" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>

                </div>

                <div id="field-textarea-options" class="field-options p-4 border-t border-gray-200">

                    <div class="flex items-center mb-4">
                        <label for="textarea-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="textarea-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>

                </div>

                <div id="field-select-options" class="field-options hidden">
                    <div class="flex items-center mb-4">
                        <label for="select-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="select-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>
                    <div class="flex mb-4">
                        <label for="select-options" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Options (one per line):</label>
                        <textarea placeholder="Enter the select field options" id="select-options" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div id="field-numbers-options" class="field-options hidden">
                    <div class="flex items-center mb-4">
                        <label for="numbers-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="numbers-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>
                    <div class="flex mb-4">
                        <label for="numbers-max" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Max:</label>
                        <input type="number" placeholder="Enter Max Number" id="numbers-max" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500" />
                    </div>
                    <div class="flex mb-4">
                        <label for="numbers-min" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Min:</label>
                        <input type="number" placeholder="Enter Min Number" id="numbers-min" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500" />
                    </div>
                </div>

                <div id="field-date-options" class="field-options p-4 border-t border-gray-200">

                    <div class="flex items-center mb-4">
                        <label for="date-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="date-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">c
                    </div>

                </div>

                <div id="field-checkbox-options" class="field-options hidden">
                    <div class="flex items-center mb-4">
                        <label for="checkbox-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="checkbox-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>
                    <div class="flex mb-4">
                        <label for="checkbox-options" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Options (one per line):</label>
                        <textarea placeholder="Enter the checkbox field options" id="checkbox-options" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div id="field-radio-options" class="field-options hidden">
                    <div class="flex items-center mb-4">
                        <label for="radio-name" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Name:</label>
                        <input type="text" id="radio-name" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500">
                    </div>
                    <div class="flex mb-4">
                        <label for="radio-options" class="w-1/4 font-bold text-gray-700 mr-4 text-lg">Options (one per line):</label>
                        <textarea placeholder="Enter the radio field options" id="radio-options" class="form-input w-3/4 border border-gray-400 py-2 px-3 rounded-lg transition duration-500 ease-in-out focus:outline-none focus:shadow-outline focus:border-blue-500"></textarea>
                    </div>
                </div>
                
                <!-- Add more forms for other field types -->
            </div>
            <div class="modal-footer flex justify-left">
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="save-field-options" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<?php init_tail(); ?>
<script>

$(function() {
    //Initialize the draggable fields
    $('#form-cart .form-field').draggable({
        helper: 'clone',
        connectToSortable: '#form-preview'
    });

    // Initialize the sortable container
    $('#form-preview').sortable({
        placeholder: 'drop-placeholder bg-gray-100/40 p-4 rounded-lg border-2 border-dotted border-gray-300',
        receive: function(event, ui) {
            // Generate the field div based on the data-field-type attribute
            var fieldType = ui.helper.data('field-type');
            var fieldHtml = '';

            fieldHtml = `
                <div id="newlyGeneratedField" class="form-field bg-gray-100/40 rounded-lg border-2 border-dotted border-gray-300 flex flex-row justify-between items-center" data-field-type="${fieldType}">
                    <div class="p-4 field-name capitalize">${fieldType} Field</div>
                    <div class="flex flex-row gap-4 pr-4">

                        <button class="edit-field"><i class="fas fa-pencil-alt"></i></button>
                        <button class="delete-field"><i class="fas fa-trash"></i></button>

                    </div>
                </div>`;
            

            // Add the field div to the form-preview container and remove the dragged helper
            ui.helper.replaceWith(fieldHtml);

            // Get the newly added field
            var newField = $('#newlyGeneratedField');

            // Add a click event listener to the edit button
            newField.find('.edit-field').on('click', function() {
                showFieldCustomizationModal($(this).closest('.form-field'));
            });

            // Add a click event listener to the delete button
            newField.find('.delete-field').on('click', function() {
                // Remove the field
                if (confirm("Are you sure you want to delete this field?")) {
                    $(this).closest('.form-field').remove();
                }
            });

            newField.attr("id", "");

            ui.helper.remove();
        }
    }).disableSelection();

    // Add a click event listener to the "Save" button
    $('#save-field-options').on('click', function() {
        const fieldType = $('#field-customization-modal').data('field-type');
        const fieldName = $(`#field-${fieldType}-options #${fieldType}-name`).val();

        // Update the field ID in the form preview
        const fieldElement = $('#field-customization-modal').data('clicked-field');
        fieldElement.data('field-name', (fieldName) ?? '');
        
        // Update the field text in the form preview
        fieldElement.children('.field-name').text(fieldName);


        if(fieldType == "text"){
            const maxLength = $(`#field-text-options #text-max-length`).val();
            fieldElement.data('field-max-length', maxLength);
        }

        if (fieldType === 'select') {
            const selectOptions = $(`#field-select-options #select-options`).val();

            fieldElement.data('field-options', selectOptions);
        }

        if (fieldType === 'numbers') {
            const max = $(`#field-numbers-options #numbers-max`).val();
            const min = $(`#field-numbers-options #numbers-min`).val();

            fieldElement.data('field-min', min);
            fieldElement.data('field-max', max);
        }

        if (fieldType === 'checkbox') {
            const checkboxOptions = $(`#field-checkbox-options #checkbox-options`).val();

            fieldElement.data('field-options', checkboxOptions);
        }

        if (fieldType === 'radio') {
            const radioOptions = $(`#field-radio-options #radio-options`).val();

            fieldElement.data('field-options', radioOptions);
        }

        

        
        // Hide the modal
        $('#field-customization-modal').modal('hide');
    });  

    $('#save-form-fields').on('click', function() {

        const formFieldsData = [];

        $('#form-preview .form-field').each(function(index) {

            const fieldName = $(this).data('field-name');
            const fieldType = $(this).data('field-type');

            // Add other field properties here
            const fieldData = {
                name: fieldName,
                type: fieldType,
                order: index + 1,
            };

            // Add field properties based on the field type
            if (fieldType === 'text') {
                fieldData.maxLength = $(this).data('field-max-length');
            } else if (fieldType === 'select') {
                
                
                const rawOptionsText = $(this).data('field-options');
                const optionsArray = rawOptionsText.split(/\n|\/newline/).map(option => option.trim());
                const optionsJSON = JSON.stringify(optionsArray);

                fieldData.options = optionsJSON;

            } else if (fieldType === 'numbers') {
                fieldData.min = $(this).data('field-min');
                fieldData.max = $(this).data('field-max');
            } else if (fieldType === 'checkbox' || fieldType === 'radio') {
                const rawOptionsText = $(this).data('field-options');
                const optionsArray = rawOptionsText.split(/\n|\/newline/).map(option => option.trim());
                const optionsJSON = JSON.stringify(optionsArray);

                fieldData.options = optionsJSON;
            }

            formFieldsData.push(fieldData);
        });



        // Save the formFieldsData to the database
        $.ajax({
            url: '<?= admin_url("recruitment_portal/save_form_fields"); ?>', // Replace this with the actual URL to your controller function
            method: 'POST',
            data: {
                form_fields_data: JSON.stringify(formFieldsData),
                campaign_id : <?= $campaign->id; ?>
            },
            success: function() {
                // Show a success message or perform any other action on successful save
                alert_float('success', 'Form fields saved successfully');
            },
            error: function() {
                // Show an error message or perform any other action on save failure
                alert('Error saving form fields');
            }
        });
    });

});
function showFieldCustomizationModal(fieldElement) {
    const fieldType = fieldElement.data('field-type');
    
    // Hide all field option forms
    $('#field-customization-modal .field-options').addClass('hidden');
    
    // Show the options form for the current field type
    $(`#field-${fieldType}-options`).removeClass('hidden');
    $("#campaignAddLabel").text(`Edit ${fieldType}`);
    
    // Show the modal
    $('#field-customization-modal').modal('show');
    $('#field-customization-modal').data('field-type', fieldType);
    
    // Set the ID input value to the current field ID
    $(`#field-${fieldType}-options #${fieldType}-name`).val(fieldElement.data('field-name'));
    if(fieldType == "text"){
        $(`#field-text-options #text-max-length`).val(fieldElement.data('field-max-length'));
    }
    else if(fieldType == "select"){
        if(fieldElement.data('field-options')){
            let formattedOptions = fieldElement.data('field-options').replace(/\/newline/g, '\n');
            $(`#field-select-options #select-options`).val(formattedOptions);
        }   
    }
    else if(fieldType == "numbers"){
        $(`#field-numbers-options #numbers-min`).val(fieldElement.data('field-min'));
        $(`#field-numbers-options #numbers-max`).val(fieldElement.data('field-max'));
    }
    else if(fieldType == "checkbox"){
        if(fieldElement.data('field-options')){
            let formattedOptions = fieldElement.data('field-options').replace(/\/newline/g, '\n');
            $(`#field-checkbox-options #checkbox-options`).val(formattedOptions);
        }  
    }
    else if(fieldType == "radio"){
        if(fieldElement.data('field-options')){
            let formattedOptions = fieldElement.data('field-options').replace(/\/newline/g, '\n');
            $(`#field-radio-options #radio-options`).val(formattedOptions);
        }  
    }
    
    $('#field-customization-modal').data('clicked-field', fieldElement);
    
}



</script>


</body>
</html>
