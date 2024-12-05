<?php
/**
 * Plugin Name: Apply Now Custom Docs
 * Plugin URI: https://wordpress.org/plugins/apply-now-custom-docs/
 * Description: Custom document generation for the Apply Now form.
 * Version: 1.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Msh Hridoy
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI: https://github.com/mshhridoy75/apply-now-custom-docs-plugin
 * Text Domain: ancd
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

