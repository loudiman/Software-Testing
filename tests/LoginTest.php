<?php
namespace Tests\UI;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
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
        $this->driver->get('https://github.com/login');
        
        $this->assertTrue(
            $this->driver->findElement(WebDriverBy::id('login_field'))->isDisplayed()
        );
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }
}