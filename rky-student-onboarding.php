<?php
/**
 * Plugin Name: RKY Student Onboarding
 * Description: Secure REST API endpoint for student onboarding
 * Version: 1.0.0
 * Author: Akinbode Abitogun
 * Author URI: https://rkycareers.com/
 * Copyright (C) 2025 RKY Careers.
 * 
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

  // Prevent direct access
if (!defined('ABSPATH')) exit;

class RKY_Student_Onboarding {
    private $plugin_name = 'rky-student-onboarding';

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route($this->plugin_name . '/v1', '/students', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_student_onboarding'],
            'permission_callback' => [$this, 'check_authentication']
        ]);
    }

    public function check_authentication() {
        // Basic authentication using wp-config.php credentials
        $valid_username = defined('RKY_API_USERNAME') ? RKY_API_USERNAME : false;
        $valid_password = defined('RKY_API_PASSWORD') ? RKY_API_PASSWORD : false;

        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

        return $username === $valid_username && $password === $valid_password;
    }

    public function handle_student_onboarding($request) {
        // Pre-validation action
        $student_data = $request->get_params();
        do_action('plugin_name_pre_validate_student', $student_data);

        // Validate student data
        $validation_rules = apply_filters('plugin_name_student_validation_rules', [
            'student_name' => 'required',
            'email' => 'required|email',
            'course' => 'required'
        ]);

        $errors = $this->validate_data($student_data, $validation_rules);
        if (!empty($errors)) {
            return new WP_REST_Response([
                'success' => false,
                'error_code' => 'validation_error',
                'message' => implode(', ', $errors)
            ], 400);
        }

        // Pre-create student action
        do_action('plugin_name_pre_create_student', $student_data);

        // Create user
        $user_id = $this->create_student_user($student_data);
        if (is_wp_error($user_id)) {
            return new WP_REST_Response([
                'success' => false,
                'error_code' => $user_id->get_error_code(),
                'message' => $user_id->get_error_message()
            ], 500);
        }

        // Post-create student action
        do_action('plugin_name_post_create_student', $user_id, $student_data);

        // Send welcome email
        do_action('plugin_name_pre_send_welcome_email', $user_id, $student_data);
        $email_status = $this->send_welcome_email($user_id, $student_data);
        do_action('plugin_name_post_send_welcome_email', $user_id, $email_status);

        return new WP_REST_Response([
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Student successfully registered',
            'email_status' => $email_status ? 'sent' : 'failed'
        ], 201);
    }

    private function validate_data($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $rules_array = explode('|', $rule);
            
            foreach ($rules_array as $single_rule) {
                switch ($single_rule) {
                    case 'required':
                        if (empty($data[$field])) {
                            $errors[] = "$field is required";
                        }
                        break;
                    case 'email':
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "$field must be a valid email";
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    private function create_student_user($student_data) {
        // Check if user exists
        $existing_user = get_user_by('email', $student_data['email']);
        if ($existing_user) {
            return new WP_Error('user_exists', 'User with this email already exists');
        }

        // Prepare user data
        $user_data = apply_filters('plugin_name_pre_user_data', [
            'user_login' => sanitize_user($student_data['email']),
            'user_email' => sanitize_email($student_data['email']),
            'first_name' => sanitize_text_field($student_data['student_name']),
            'role' => 'student'
        ]);

        // Create user
        $user_id = wp_insert_user($user_data);
        return $user_id;
    }

    private function send_welcome_email($user_id, $student_data) {
        $user = get_userdata($user_id);
        
        $default_content = sprintf(
            "Welcome %s!\n\nYou have been enrolled in the %s course.\n\nBest regards,\nYour Institution",
            $student_data['student_name'],
            $student_data['course']
        );

        $email_content = apply_filters(
            'plugin_name_welcome_email_content', 
            $default_content, 
            $user_id
        );

        $email_sent = wp_mail(
            $user->user_email, 
            'Welcome to Your Course', 
            $email_content
        );

        return $email_sent;
    }
}

new RKY_Student_Onboarding();