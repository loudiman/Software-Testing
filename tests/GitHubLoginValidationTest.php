<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Exception\TimeOutException;

class GitHubLoginValidationTest extends TestCase
{
    private $driver;
    private $wait;

    protected function setUp(): void
    {
        $options = new ChromeOptions();
        $options->addArguments(['--no-sandbox', '--disable-dev-shm-usage']);
        
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        
        $this->driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);
        $this->wait = new WebDriverWait($this->driver, 20); // 20 second timeout
    }

    /**
     * @dataProvider loginCredentialsProvider
     */
    public function testGitHubLoginValidation(string $email, string $password)
    {
        try {
            // Navigate to GitHub login page
            $this->driver->get('https://github.com/login');
            
            // Wait for login form
            $loginField = $this->wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login_field'))
            );
            $this->assertNotNull($loginField, 'Login field should be present');
            
            // Fill login form
            $this->driver->findElement(WebDriverBy::id('login_field'))->sendKeys($email);
            $this->driver->findElement(WebDriverBy::id('password'))->sendKeys($password);
            
            // Click sign-in and wait
            $this->driver->findElement(WebDriverBy::cssSelector('input[type="submit"]'))->click();
            sleep(3);
            
            // Check for success or error
            try {
                $avatar = $this->wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('.avatar')
                    )
                );
                $this->assertNotNull($avatar, 'Avatar should be present after successful login');
                return ['success' => true, 'message' => 'Login successful'];
            } catch (TimeOutException $e) {
                $errorMessage = '';
                try {
                    $errorElement = $this->driver->findElement(WebDriverBy::cssSelector('.flash-error'));
                    $errorMessage = $errorElement->getText();
                    $this->fail("Login failed with message: $errorMessage");
                } catch (\Exception $inner) {
                    $this->fail('Login failed without error message');
                }
                return ['success' => false, 'message' => $errorMessage];
            }
        } catch (\Exception $e) {
            $this->fail("Test failed with exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function loginCredentialsProvider(): array
    {
        return [
            'valid_credentials' => ['test@gmail.com', 'test']
        ];
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}