<?php
use PHPUnit\Framework\TestCase;
require_once 'tests\RKYStudentOnboardingTest.php';

class RKY_Student_Onboarding_Test extends TestCase {
    private $plugin;

    protected function setUp(): void {
        $this->plugin = new RKY_Student_Onboarding();
        
        define('RKY_API_USERNAME', 'test_user');
        define('RKY_API_PASSWORD', 'test_pass');
    }

    // Test Data Validation
    public function testValidStudentData() {
        $validData = [
            'student_name' => 'Akinbode Abitogun',
            'email' => 'abitoguna@gmail.com',
            'course' => 'Web Development'
        ];

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'validate_data');
        $reflectionMethod->setAccessible(true);

        $errors = $reflectionMethod->invoke($this->plugin, $validData, [
            'student_name' => 'required',
            'email' => 'required|email',
            'course' => 'required'
        ]);

        $this->assertEmpty($errors, 'Valid data should not produce validation errors');
    }

    public function testInvalidEmail() {
        $invalidData = [
            'student_name' => 'Akinbode Abitogun',
            'email' => 'invalid-email',
            'course' => 'Web Development'
        ];

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'validate_data');
        $reflectionMethod->setAccessible(true);

        $errors = $reflectionMethod->invoke($this->plugin, $invalidData, [
            'student_name' => 'required',
            'email' => 'required|email',
            'course' => 'required'
        ]);

        $this->assertNotEmpty($errors, 'Invalid email should produce validation errors');
    }

    // Test User Creation
    public function testUserCreation() {
        $studentData = [
            'student_name' => 'Jane Doe',
            'email' => 'abitoguna' . rand(100, 999) . '@gmail.com',
            'course' => 'Data Science'
        ];

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'create_student_user');
        $reflectionMethod->setAccessible(true);

        $userId = $reflectionMethod->invoke($this->plugin, $studentData);

        $this->assertIsInt($userId, 'User creation should return a user ID');
        $this->assertGreaterThan(0, $userId, 'User ID should be positive');
    }

    // Test Authentication
    public function testAuthentication() {

        $_SERVER['PHP_AUTH_USER'] = 'test_user';
        $_SERVER['PHP_AUTH_PW'] = 'test_pass';

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'check_authentication');
        $reflectionMethod->setAccessible(true);

        $authResult = $reflectionMethod->invoke($this->plugin);

        $this->assertTrue($authResult, 'Valid credentials should pass authentication');
    }

    // Test Email Sending
    public function testWelcomeEmailSending() {

        $userId = wp_create_user('testuser', 'password', 'test@example.com');

        $studentData = [
            'student_name' => 'Test Student',
            'email' => 'test@example.com',
            'course' => 'Testing'
        ];

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'send_welcome_email');
        $reflectionMethod->setAccessible(true);

        $emailStatus = $reflectionMethod->invoke($this->plugin, $userId, $studentData);

        $this->assertTrue($emailStatus, 'Welcome email should be sent successfully');
    }

    // Test Filter Customization
    public function testEmailContentFilter() {
        $originalContent = 'Original Welcome Message';
        
        add_filter('plugin_name_welcome_email_content', function($content, $userId) {
            return 'Filtered Welcome Message';
        }, 10, 2);

        $studentData = [
            'student_name' => 'Filtered Student',
            'email' => 'filtered@example.com',
            'course' => 'Filtering'
        ];

        $reflectionMethod = new ReflectionMethod(RKY_Student_Onboarding::class, 'send_welcome_email');
        $reflectionMethod->setAccessible(true);

        $userId = wp_create_user('filtereduser', 'password', 'filtered@example.com');

        $emailStatus = $reflectionMethod->invoke($this->plugin, $userId, $studentData);

        $this->assertTrue($emailStatus, 'Filtered email should be sent successfully');
    }

    protected function tearDown(): void {
        // Clean up any created users or reset WordPress state
        wp_delete_user(get_user_by('email', 'abitoguna@gmail.com')->ID);
        wp_delete_user(get_user_by('email', 'test@example.com')->ID);
        wp_delete_user(get_user_by('email', 'filtered@example.com')->ID);
    }
}