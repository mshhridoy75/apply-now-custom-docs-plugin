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

add_action('wpforms_process_before_save', 'ancd_auto_generate_student_id', 10, 4);

function ancd_auto_generate_student_id($fields, $entry, $form_data, $entry_id) {
    // Ensure this only runs for the correct form (replace 123 with your form ID)
    if ($form_data['id'] != 2997) {
        return;
    }

    // Define field IDs
     $date_field_id = 53; // Replace with the ID of the date field (e.g., application date)
     $passport_field_id = 54; // Replace with the ID of the passport number field
     $student_id_field_id = 68; // Replace with the ID of the hidden student ID field

    // Check if both passport number and date fields are filled
    if (!empty($fields[$passport_field_id]['value']) && !empty($fields[$date_field_id]['value'])) {
        // Get the passport number
        $passport_number = sanitize_text_field($fields[$passport_field_id]['value']);
		$first_three = substr($passport_number, 0, 3);

        // Get the date from the date field and parse it (expecting DD/MM/YYYY format)
        $date_value = sanitize_text_field($fields[$date_field_id]['value']);
        $date_parts = explode('/', $date_value); // Splits date as [DD, MM, YYYY]
        
        // Assign year and month based on parsed date
        $month = $date_parts[1];
        $year = $date_parts[2];

        // Generate the student ID using passport number, year, and month
        $student_id = $first_three . $month . $year ;

		// Debugging Output to check generated ID (only for testing, remove when done)
        error_log("Generated Student ID: " . $student_id);

        // Populate the student ID field
        $fields[$student_id_field_id]['value'] = $student_id;
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
