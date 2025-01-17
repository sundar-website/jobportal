<?php
/**
 * Plugin Name: Job Portal
 * Description: A WordPress plugin to accept job applications via a REST API and display a form using a shortcode methods.
 * Version: Current Version
 * Author: Sundarrajan G
 * Autor URI: sundaruniverse5@gmail.com
 */
// Register custom post type and taxonomy
function register_job_application_post_type() {
    // Register 'job_application' custom post type
    register_post_type('job_application', array(
        'labels' => array(
            'name' => 'Jobs',
            'singular_name' => 'Jobs',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Jobs',
            'edit_item' => 'Edit Jobs',
            'new_item' => 'New Jobs',
            'view_item' => 'View Jobs',
            'search_items' => 'Search Jobs',
            'not_found' => 'No Jobs found',
            'not_found_in_trash' => 'No Job found in Trash',
            'all_items' => 'All Jobs',
            'menu_name' => 'Jobs',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'taxonomies' => array('job_category'), // Attach custom taxonomy here
    ));

    // Register 'job_category' taxonomy for job applications
    register_taxonomy('job_category', 'job_application', array(
        'labels' => array(
            'name' => 'Job Categories',
            'singular_name' => 'Job Category',
            'search_items' => 'Search Job Categories',
            'all_items' => 'All Job Categories',
            'edit_item' => 'Edit Job Category',
            'update_item' => 'Update Job Category',
            'add_new_item' => 'Add New Job Category',
            'new_item_name' => 'New Job Category Name',
            'menu_name' => 'Job Categories',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
    ));
}
add_action('init', 'register_job_application_post_type');

// Add meta boxes for job applications
add_action('add_meta_boxes', function () {
    add_meta_box('job_application_details', 'Job Application Details', 'job_application_details_meta_box', 'job_application', 'normal', 'high');
});

// Callback function to render the meta box content
function job_application_details_meta_box($post) {
    // Retrieve current values from the post meta
    $category = get_the_terms($post->ID, 'job_category');
    $expiration_date = get_post_meta($post->ID, '_expiration_date', true);
    $message = get_post_meta($post->ID, '_message', true);
    $company_name = get_post_meta($post->ID, '_company_name', true);
    $company_logo = get_post_meta($post->ID, '_company_logo', true);
    $location = get_post_meta($post->ID, '_location', true);
    $job_type = get_post_meta($post->ID, '_job_type', true);
    // List of countries (can be expanded or fetched from a plugin)
    $countries = array(
        'USA' => 'United States',
        'CA' => 'Canada',
        'GB' => 'United Kingdom',
        'AU' => 'Australia',
        'IN' => 'India',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'JP' => 'Japan',
        'CN' => 'China',
    );

    // Display the form fields in the meta box
    ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .job-application-meta-box .form-field {
            width: 100%;
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .job-application-meta-box label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .job-application-meta-box input,
        .job-application-meta-box select,
        .job-application-meta-box textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .job-application-meta-box textarea {
            resize: vertical;
            height: 100px;
        }

        .job-application-meta-box .button {
            margin-top: 1rem;
        }

        .job-application-meta-box .form-field input[type="button"] {
            max-width: 200px;
        }

        /* Style for the select fields */
        .job-application-meta-box select {
            max-width: 100%;
        }

        .form-field p.description {
            font-size: 12px;
            color: #666;
        }
    </style>

    <div class="job-application-meta-box">
        <!-- Job Category Field -->
        <div class="form-field">
            <label for="job_category">Category:</label>
            <select name="job_category" id="job_category">
            <option value="">Select a category</option>
                <option value="PHP Developer">PHP Developer</option>
                <option value="WordPress Developer">WordPress Developer</option>
                <option value="UI/UX Designer">UI/UX Designer</option>
                <option value="Fullstack Developer">Fullstack Developer</option>
</select>
        </div>

        <!-- Expiration Date Field -->
        <div class="form-field">
            <label for="expiration_date">Expiration Date:</label>
            <input type="date" name="expiration_date" id="expiration_date" value="<?php echo esc_attr($expiration_date); ?>" />
        </div>

        <!-- Message Field -->
        <div class="form-field">
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="5"><?php echo esc_textarea($message); ?></textarea>
        </div>

        <!-- Company Information Section -->
        <h3>Company Information</h3>
        <!-- Company Name Field -->

        <div class="form-field">
    <label for="job_type">Job Type:</label>
    <select name="job_type" id="job_type">
        <option value="Full Time" <?php selected($job_type, 'full_time'); ?>>Full Time</option>
        <option value="Part Time" <?php selected($job_type, 'part_time'); ?>>Part Time</option>
        <option value="Contract" <?php selected($job_type, 'contract'); ?>>Contract</option>
        <option value="Temporary" <?php selected($job_type, 'temporary'); ?>>Temporary</option>
    </select>
</div>
        <div class="form-field">
            <label for="company_name">Company Name:</label>
            <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr($company_name); ?>" />
        </div>

        <!-- Company Logo Field (File Upload) -->
        <div class="form-field">
            <label for="company_logo">Company Logo:</label>
            <input type="text" name="company_logo" id="company_logo" value="<?php echo esc_url($company_logo); ?>" />
            <input type="button" class="button" value="Upload Logo" id="upload_logo_button" />
            <p class="description">Upload your company logo (image file).</p>
        </div>

        <!-- Location Field (Country Dropdown) -->
        <div class="form-field">
            <label for="location">Location:</label>
            <select name="location" id="location">
                <option value="">Select a location</option>
                <?php foreach ($countries as $key => $country) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($location, $key); ?>><?php echo esc_html($country); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        // Featured Job Dropdown
<div class="form-field">
    <label for="featured_job">Featured Job:</label>
    <select name="featured_job" id="featured_job">
        <option value="1" <?php selected($featured_job, 1); ?>>Yes</option>
        <option value="0" <?php selected($featured_job, 0); ?>>No</option>
    </select>
    <p class="description">Select 'Yes' if this job is featured, otherwise select 'No'.</p>
</div>

    </div>
<?php
}

// Save the custom fields data
add_action('save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'job_application') {
        return;
    }

    // Save the category
    if (isset($_POST['job_category'])) {
        wp_set_object_terms($post_id, sanitize_text_field($_POST['job_category']), 'job_category');
    }

    // Save the expiration date
    if (isset($_POST['expiration_date'])) {
        update_post_meta($post_id, '_expiration_date', sanitize_text_field($_POST['expiration_date']));
    }

   // Check and save the featured job field
   if (isset($_POST['featured_job'])) {
    update_post_meta($post_id, '_featured_job', sanitize_text_field($_POST['featured_job']));
} else {
    update_post_meta($post_id, '_featured_job', '0'); // Default to "No" if not set
}

    // Save the message
    if (isset($_POST['message'])) {
        update_post_meta($post_id, '_message', sanitize_textarea_field($_POST['message']));
    }
    if (isset($_POST['job_type'])) {
        update_post_meta($post_id, '_job_type', sanitize_text_field($_POST['job_type']));
    }
    // Save the company name
    if (isset($_POST['company_name'])) {
        update_post_meta($post_id, '_company_name', sanitize_text_field($_POST['company_name']));
    }

    // Save the company logo (URL)
    if (isset($_POST['company_logo'])) {
        update_post_meta($post_id, '_company_logo', esc_url_raw($_POST['company_logo']));
    }

    // Save the location
    if (isset($_POST['location'])) {
        update_post_meta($post_id, '_location', sanitize_text_field($_POST['location']));
    }
});

add_action('manage_job_application_posts_custom_column', function ($column, $post_id) {
    if ($column === 'featured_job') {
        $featured_job = get_post_meta($post_id, '_featured_job', true);
        echo $featured_job === '1' ? 'Yes' : 'No';
    }
}, 10, 2);


add_filter('manage_job_application_posts_columns', function ($columns) {
    $columns['featured_job'] = 'Featured Job';
    return $columns;
});


$featured_job = get_post_meta($post_id, '_featured_job', true);
if ($featured_job === '1') {
    echo '<span class="featured-job-label">🌟 Featured Job</span>';
}

add_action('post_row_actions', function ($actions, $post) {
    if ($post->post_type === 'job_application') {
        $featured_job = get_post_meta($post->ID, '_featured_job', true);
        if ($featured_job === '1') {
            $actions['featured'] = '<span style="color: green; font-weight: bold;">Featured Job</span>';
        }
    }
    return $actions;
}, 10, 2);


// Add media uploader script for company logo
add_action('admin_footer', function () {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#upload_logo_button').click(function(e) {
                e.preventDefault();
                var imageUploader = wp.media({
                    title: 'Select or Upload Logo',
                    button: {
                        text: 'Use this logo'
                    },
                    multiple: false
                }).open().on('select', function() {
                    var attachment = imageUploader.state().get('selection').first().toJSON();
                    $('#company_logo').val(attachment.url);
                });
            });
        });
    </script>
    <?php
});
// Add JavaScript for handling the apply button click
add_action('wp_footer', function() {
    ?>
  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const applyButtons = document.querySelectorAll('.apply-button');
        applyButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const jobId = this.getAttribute('data-job-id');
                // Redirect to the form page with the jobId appended as a URL parameter
                window.location.href = 'http://localhost:8080/jobportal/career-form/?job_id=' + jobId;
            });
        });
    });
</script>

    <?php
});

// Register the custom post type for job applications
add_action('init', function () {
    register_post_type('job_application', array(
        'labels' => array(
            'name' => 'Job Applications',
            'singular_name' => 'Job Application',
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
    ));

    // Register custom taxonomy (categories)
    register_taxonomy('job_category', 'job_application', array(
        'label' => 'Job Categories',
        'hierarchical' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'job-category'),
    ));
});





// Display job category and expiration date in the admin panel
add_action('manage_job_application_posts_custom_column', function ($column, $post_id) {
    if ($column == 'job_category') {
        $categories = wp_get_post_terms($post_id, 'job_category');
        echo $categories ? esc_html($categories[0]->name) : 'No category';
    }
    if ($column == 'expiration_date') {
        $expiration_date = get_post_meta($post_id, '_expiration_date', true);
        echo $expiration_date ? esc_html($expiration_date) : 'No expiration date';
    }
}, 10, 2);

add_action('manage_job_application_posts_custom_column', function ($column, $post_id) {
    if ($column == 'job_type') {
        $job_type = get_post_meta($post_id, '_job_type', true);
        echo $job_type ? esc_html(ucwords(str_replace('_', ' ', $job_type))) : 'Not specified';
    }

    if ($column == 'application_count') {
        $args = array(
            'post_type' => 'job_application',
            'meta_query' => array(
                array(
                    'key' => '_job_application_for_job',
                    'value' => $post_id,
                    'compare' => '='
                ),
            ),
            'posts_per_page' => -1,
        );
        $applications = new WP_Query($args);
        echo $applications->found_posts;
    }
}, 10, 2);


// Add custom columns to the job application admin table
add_filter('manage_job_application_posts_columns', function ($columns) {
    // Add custom columns
    $columns['job_category'] = 'Job Category';
    $columns['expiration_date'] = 'Expiration Date';
    $columns['application_count'] = 'Applications Count';
    return $columns;
});


// Register the custom REST API endpoint
function register_job_list_endpoint() {
    register_rest_route('job-apply/v1', '/job-list', [
        'methods' => 'GET',
        'callback' => 'get_job_list',
        'permission_callback' => '__return_true', // Optional: Adjust permission if needed
    ]);
}
add_action('rest_api_init', 'register_job_list_endpoint');



// Shortcode to list job applications in a table format
function job_apply_listing_shortcode($atts) {
    // Default arguments for the query
    $args = array(
        'post_type' => 'job_application',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_featured_job',
                'value' => '1',
                'compare' => '='
            )
        ),
    );

    // Query the job applications
    $query = new WP_Query($args);

    // If there are job applications, display them in a table
    if ($query->have_posts()) {
        ob_start();

        ?>
        <style>
            .job-applications-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-family: Arial, sans-serif;
            }
            .job-applications-table th,
            .job-applications-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }
            .job-applications-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .job-applications-table tbody tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .job-applications-table tbody tr:hover {
                background-color: #f1f1f1;
            }
            .apply-button {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 5px 10px;
                text-decoration: none;
                border-radius: 4px;
                text-align: center;
            }
            .apply-button:hover {
                background-color: #0056b3;
            }
        </style>

        <table class="job-applications-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Logo</th>
                    <th>Job Title</th>
                    <th>Category</th>
                    <th>Job Type</th>
                    <th>Apply</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial_no = 1;

                while ($query->have_posts()) {
                    $query->the_post();

                    // Get custom field values
                    $logo = get_post_meta(get_the_ID(), '_company_logo', true);
                    $category = get_the_terms(get_the_ID(), 'job_category');
                    $job_type = get_post_meta(get_the_ID(), '_job_type', true);

                    // Get category name
                    $category_name = !empty($category) ? $category[0]->name : 'N/A';

                    ?>
                  <tr>
    <td><?php echo $serial_no; ?></td>
    <td>
        <?php if ($logo) : ?>
            <img src="<?php echo esc_url($logo); ?>" alt="Logo" style="width: 50px; height: 50px; object-fit: cover;">
        <?php else : ?>
            No Logo
        <?php endif; ?>
    </td>
    <td><?php the_title(); ?></td>
    <td><?php echo esc_html($category_name); ?></td>
    <td><?php echo esc_html($job_type); ?></td>
    
    <td>
        <a href="<?php echo get_permalink(); ?>" class="apply-button">Apply Now</a>
    </td>
</tr>

                    <?php
                    $serial_no++;
                }
                ?>
            </tbody>
        </table>

        <?php

        // Reset post data
        wp_reset_postdata();

        return ob_get_clean();
    } else {
        return '<p>No job applications found.</p>';
    }
}
add_shortcode('job_apply_listing', 'job_apply_listing_shortcode');
