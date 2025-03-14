<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

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

    public function testGitHubLoginForm()
    {
        // Navigate to GitHub login page
        $this->driver->get('https://github.com/login');
        
        // Test username field
        $loginField = $this->driver->findElement(WebDriverBy::id('login_field'));
        $this->assertTrue($loginField->isDisplayed());
        $this->assertEquals('login_field', $loginField->getAttribute('id'));
        
        // Test password field
        $passwordField = $this->driver->findElement(WebDriverBy::id('password'));
        $this->assertTrue($passwordField->isDisplayed());
        $this->assertEquals('password', $passwordField->getAttribute('type'));
        
        // Test sign-in button
        $signInButton = $this->driver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $this->assertTrue($signInButton->isDisplayed());
        $this->assertEquals('Sign in', $signInButton->getAttribute('value'));
        
        // Test forgot password link
        $forgotPasswordLink = $this->driver->findElement(WebDriverBy::linkText('Forgot password?'));
        $this->assertTrue($forgotPasswordLink->isDisplayed());
        
        // Test page title
        $this->assertEquals('Sign in to GitHub · GitHub', $this->driver->getTitle());
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }
}