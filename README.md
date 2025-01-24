# RKY Student Onboarding Plugin

## Overview
A secure WordPress REST API endpoint for student onboarding with comprehensive validation and email notification.

## Installation
1. Download the plugin files
2. Upload to `wp-content/plugins/rky-student-onboarding`
3. Activate the plugin in WordPress admin

## Configuration
Add these to `wp-config.php`:
```php
define('RKY_API_USERNAME', 'your_api_username');
define('RKY_API_PASSWORD', 'your_api_password');
```

## API Usage
### Endpoint
- **URL**: `/wp-json/v1/rky-student-onboarding/students`
- **Method**: POST
- **Authentication**: Basic Auth

### Request Payload
```json
{
  "student_name": "Akinbode Abitogun",
  "email": "abitoguna@gmail.com",
  "course": "Web Development"
}
```

## Hooks and Filters
### Actions
- `plugin_name_pre_validate_student`
- `plugin_name_pre_create_student`
- `plugin_name_post_create_student`
- `plugin_name_pre_send_welcome_email`
- `plugin_name_post_send_welcome_email`

### Filters
- `plugin_name_student_validation_rules`
- `plugin_name_pre_user_data`
- `plugin_name_welcome_email_content`

## Example Hook Usage
```php
add_filter('plugin_name_welcome_email_content', function($content, $user_id) {
    return "Custom welcome message for user $user_id";
}, 10, 2);
```

## Security
- Basic authentication
- Input sanitization
- WordPress security best practices

## Error Handling
Comprehensive error responses with appropriate HTTP status codes
