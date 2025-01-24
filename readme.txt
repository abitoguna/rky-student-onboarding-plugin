RKY STUDENT ONBOARDING PLUGIN
=============================

OVERVIEW
--------
Secure WordPress REST API endpoint for student onboarding with validation and email notification.

INSTALLATION
------------
1. Download plugin files
2. Upload to wp-content/plugins/rky-student-onboarding
3. Activate in WordPress admin

CONFIGURATION
-------------
Add to wp-config.php:
RKY_API_USERNAME: API authentication username
RKY_API_PASSWORD: API authentication password

API ENDPOINT
------------
URL: /wp-json/rky-student-onboarding/v1/students
Method: POST
Authentication: Basic Auth

PAYLOAD EXAMPLE
---------------
{
  "student_name": "Akinbode Abitogun",
  "email": "abitoguna@gmail.com",
  "course": "Web Development"
}

HOOKS & FILTERS
---------------
ACTIONS:
- plugin_name_pre_validate_student
- plugin_name_pre_create_student
- plugin_name_post_create_student
- plugin_name_pre_send_welcome_email
- plugin_name_post_send_welcome_email

FILTERS:
- plugin_name_student_validation_rules
- plugin_name_pre_user_data
- plugin_name_welcome_email_content

SECURITY FEATURES
-----------------
- Basic authentication
- Input sanitization
- WordPress security best practices

ERROR HANDLING
--------------
Comprehensive error responses with appropriate HTTP status codes