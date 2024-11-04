<?php
/**
 * Plugin Name: Apply Now Custom Docs
 * Description: Custom document generation for the Apply Now form.
 * Version: 1.0
 * Author: Msh Hridoy
 */
/*
// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/document-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/qr-code-generator.php';

// Add actions and hooks
add_action('wp_enqueue_scripts', 'ancd_enqueue_scripts');
add_action('admin_menu', 'ancd_admin_menu');

// Enqueue JS and CSS
function ancd_enqueue_scripts() {
    wp_enqueue_script('ancd-custom-js', plugin_dir_url(__FILE__) . 'assets/js/custom.js', ['jquery'], null, true);
    wp_enqueue_style('ancd-custom-css', plugin_dir_url(__FILE__) . 'assets/css/custom.css');
}

// Create an admin menu for generated documents
function ancd_admin_menu() {
    add_menu_page('Documents', 'Documents', 'manage_options', 'ancd-docs', 'ancd_document_page');
}

function ancd_document_page() {
    echo '<h1>Generated Documents</h1>';
    // Display documents here
}*/

/*add_shortcode('ancd_dynamic_e2pdf', 'ancd_dynamic_e2pdf_handler');

function ancd_dynamic_e2pdf_handler() {
    // Get template ID and entry ID from the URL parameters
    $template_id = isset($_GET['template_id']) ? sanitize_text_field($_GET['template_id']) : '';
    $entry_id = isset($_GET['entry_id']) ? sanitize_text_field($_GET['entry_id']) : '';
    $form_id = '2997'; // Replace with your actual WPForms form ID

    if (!$template_id || !$entry_id) {
        return '<p>Invalid document request.</p>';
    }
 echo "<p>Template ID: $template_id</p>";
    echo "<p>Entry ID: $entry_id</p>";
    
    // Dynamically generate the E2PDF shortcode
    return do_shortcode('[e2pdf-download id="' . $template_id . '" dataset="wpforms-' . $form_id . '-' . $entry_id . '"]');
}
*/

add_action('wpforms_entry_save_data', 'ancd_auto_generate_student_id', 10, 3);

function ancd_auto_generate_student_id($fields, $entry, $form_data) {
    // Run only for the correct form ID (replace 2997 with your form ID)
    if ($form_data['id'] != 2997) {
        return $fields;
    }

    // Define field IDs
    $date_field_id = 53; // ID of the date field
    $passport_field_id = 54; // ID of the passport number field
    $student_id_field_id = 68; // ID of the Single Line Text (Student ID) field

    // Check if both passport number and date fields are filled
    if (!empty($fields[$passport_field_id]['value']) && !empty($fields[$date_field_id]['value'])) {
        // Get the passport number and first three characters
        $passport_number = sanitize_text_field($fields[$passport_field_id]['value']);
        $first_three = substr($passport_number, 0, 3);

        // Get the date value and parse it assuming DD/MM/YYYY format
        $date_value = sanitize_text_field($fields[$date_field_id]['value']);
        error_log("Raw Date Value: " . $date_value); // Log for debugging

        $date_parts = explode('/', $date_value);

        // Ensure correct format: check if date parts array has 3 elements and validate the year
        if (count($date_parts) == 3 && strlen($date_parts[2]) == 4) {
            $day = $date_parts[0];
            $month = $date_parts[1];
            $year = substr($date_parts[2], -2); // Get the last two digits of the year

            // Generate the student ID using the first three characters of passport, month, and last two digits of the year
            $student_id = $first_three . $month . $year;

            // Update the entry field with the generated student ID
            $fields[$student_id_field_id]['value'] = $student_id;

            // Log the generated ID for verification
            error_log("Generated Student ID: " . $student_id);
        } else {
            error_log("Error: Date field does not match DD/MM/YYYY format.");
        }
    } else {
        error_log("Error: Passport number or date field is empty.");
    }

    return $fields;
}




// Function to create E2PDF link for specific entry and document type
function ancd_get_e2pdf_link($entry_id, $document_type) {
    // Replace 'your_e2pdf_template_id' with the actual ID of each E2PDF template
    $template_id = '';

    switch ($document_type) {
        case 'offer_letter':
            $template_id = '5';
            break;
        case 'rejection_letter':
            $template_id = '6';
            break;
        case 'visa_kit':
            $template_id = '4';
            break;
        default:
            return '#';
    }
    
// Generate E2PDF download link
   return admin_url('admin.php?page=e2pdf&action=export&id=' . $template_id . '&dataset=wpforms-' . $entry_id . '&auto=1');
}


// Add custom buttons to WPForms entry detail page
add_action('wpforms_entry_details_content', 'ancd_add_custom_buttons_to_wpforms_entry');

function ancd_add_custom_buttons_to_wpforms_entry($entry) {
    // Check if the current entry is from the "Apply Now" form by ID (replace '123' with your form's ID)
    if ($entry->form_id != 2997) {
        return;
    }
    
    // Output the custom buttons
    ?>
    <div style="margin-top: 20px;">
        <h3>Generate Documents</h3>
        <button id="offer-letter-btn" class="button button-primary">Generate Offer Letter</button>
        <button id="rejection-letter-btn" class="button button-primary">Generate Rejection Letter</button>
        <button id="visa-kit-btn" class="button button-primary">Generate Visa Kit</button>
    </div> 
    
    <!-- <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#offer-letter-btn').click(function() {
            window.location.href = '<?php echo esc_url(add_query_arg(['template_id' => 5, 'entry_id' => $entry->entry_id], get_permalink(6236))); ?>';
        });
        $('#rejection-letter-btn').click(function() {
            window.location.href = '<?php echo esc_url(add_query_arg(['template_id' => 6, 'entry_id' => $entry->entry_id], get_permalink(6236))); ?>';
        });
        $('#visa-kit-btn').click(function() {
            window.location.href = '<?php echo esc_url(add_query_arg(['template_id' => 4, 'entry_id' => $entry->entry_id], get_permalink(6236))); ?>';
        });
    });
</script> -->
    

    
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#offer-letter-btn').click(function() {
                window.location.href = '<?php echo esc_url(ancd_get_e2pdf_link($entry->entry_id, 'offer_letter')); ?>';
            });
            $('#rejection-letter-btn').click(function() {
                window.location.href = '<?php echo esc_url(ancd_get_e2pdf_link($entry->entry_id, 'rejection_letter')); ?>';
            });
            $('#visa-kit-btn').click(function() {
                window.location.href = '<?php echo esc_url(ancd_get_e2pdf_link($entry->entry_id, 'visa_kit')); ?>';
            });
        });
        
    </script> 
    
    <?php
}
