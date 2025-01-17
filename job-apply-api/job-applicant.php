<?php
// Shortcode to display applicant form
function display_applicant_registration_form($atts) {
    $atts = shortcode_atts(array(
        'job_id' => '',
    ), $atts, 'applicant_registration_form');

    global $wpdb;
    $table_name = $wpdb->prefix . 'applicants'; // Define your table name

    // Form submission processing
    if (isset($_POST['submit_application'])) {
        $name = sanitize_text_field($_POST['applicant_name']);
        $email = sanitize_email($_POST['applicant_email']);
        $company_name = sanitize_text_field($_POST['applicant_company_name']);
        $resume = $_FILES['applicant_resume'];

        // Save uploaded file
        if (!empty($resume['name'])) {
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['basedir'] . '/resumes/';
            wp_mkdir_p($upload_path); // Ensure the directory exists

            $file_name = time() . '-' . basename($resume['name']);
            $target_file = $upload_path . $file_name;

            if (move_uploaded_file($resume['tmp_name'], $target_file)) {
                $resume_url = $upload_dir['baseurl'] . '/resumes/' . $file_name;
            }
        }

        // Insert data into the database
        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'company_name' => $company_name,
            'resume_url' => $resume_url,
            'job_id' => $atts['job_id'],
            'applied_at' => current_time('mysql'),
        ));

        echo '<p>Thank you for your application, ' . esc_html($name) . '!</p>';
    }

    ob_start();
    ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <form method="POST" enctype="multipart/form-data">
        <div class="container mt-5">
            <h3>Job Application Form</h3>
            <!-- Full Name Field -->
            <div class="form-group">
                <label for="applicant_name">Full Name:</label>
                <input type="text" name="applicant_name" id="applicant_name" class="form-control" required />
            </div>

            <!-- Email Address Field -->
            <div class="form-group">
                <label for="applicant_email">Email Address:</label>
                <input type="email" name="applicant_email" id="applicant_email" class="form-control" required />
            </div>

            <!-- Company Name Field -->
            <div class="form-group">
                <label for="applicant_company_name">Company Name:</label>
                <input type="text" name="applicant_company_name" id="applicant_company_name" class="form-control" required />
            </div>

            <!-- Resume Upload Field -->
            <div class="form-group">
                <label for="applicant_resume">Upload Resume:</label>
                <input type="file" name="applicant_resume" id="applicant_resume" class="form-control-file" required />
            </div>

            <!-- Submit Button -->
            <button type="submit" name="submit_application" class="btn btn-primary">Submit Application</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('applicant_registration_form', 'display_applicant_registration_form');

// Function to create the applicants table
function create_applicants_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'applicants';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        email text NOT NULL,
        company_name text NOT NULL,
        resume_url text NOT NULL,
        job_id text NOT NULL,
        applied_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_applicants_table');

// Shortcode to display the list of applicants
function display_applicants_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'applicants';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    ob_start();
    ?>
    <h3>Applicant List</h3>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company Name</th>
                <th>Resume</th>
                <th>Applied At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo esc_html($row->name); ?></td>
                    <td><?php echo esc_html($row->email); ?></td>
                    <td><?php echo esc_html($row->company_name); ?></td>
                    <td><a href="<?php echo esc_url($row->resume_url); ?>" target="_blank">View Resume</a></td>
                    <td><?php echo esc_html($row->applied_at); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}
add_shortcode('applicants_list', 'display_applicants_list');
?>
