<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Exception\TimeOutException;

class GitHubLoginSmokeTest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        // Use absolute path without environment variable
        $options = new ChromeOptions();
        $options->addArguments([
            '--no-sandbox',
            '--disable-dev-shm-usage'
        ]);
        
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        
        // Connect to running ChromeDriver instance
        $this->driver = RemoteWebDriver::create(
            'http://localhost:9515', 
            $capabilities
        );
    }

    public function testGitHubLoginValidation(string $email, string $password)
    {
        // Navigate to GitHub login page
        $this->driver->get('https://github.com/login');
        
        // Find and fill username field
        $loginField = $this->driver->findElement(WebDriverBy::id('login_field'));
        $loginField->sendKeys($email);
        
        // Find and fill password field
        $passwordField = $this->driver->findElement(WebDriverBy::id('password'));
        $passwordField->sendKeys($password);
        
        // Click sign-in button
        $signInButton = $this->driver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $signInButton->click();
        
        // Wait for page load (max 10 seconds)
        $wait = new WebDriverWait($this->driver, 10);
        
        try {
            // Check for error message
            $errorMessage = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('.flash-error')
                )
            );
            return ['success' => false, 'message' => $errorMessage->getText()];
        } catch (TimeOutException $e) {
            // Check if login successful by looking for avatar menu
            try {
                $avatar = $wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('.avatar')
                    )
                );
                return ['success' => true, 'message' => 'Login successful'];
            } catch (TimeOutException $e) {
                return ['success' => false, 'message' => 'Unknown login result'];
            }
        }
    }

    public function testLoginWithCredentials()
    {
        $result = $this->testGitHubLoginValidation('email@example.com', 'password123');
        $this->assertTrue($result['success'], $result['message']);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

}