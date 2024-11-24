<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;

class GitHubReliabilityTest extends TestCase
{
    private $driver;
    private const LOGIN_URL = 'https://github.com/login';
    private const CHECK_INTERVAL = 60; // Rate limit: 60 requests per hour in GitHub
    
    protected function setUp(): void
    {
        $options = new ChromeOptions();
        $options->addArguments(['--no-sandbox', '--headless']);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);
    }

    /**
     * Performs reliability testing of GitHub login page for a specified duration
     * 
     * @dataProvider reliabilityTestDataProvider
     * @param int $duration Duration in seconds to run the test
     * @throws \PHPUnit\Framework\AssertionFailedError If reliability falls below 99%
     * @return void
     */
    public function testLoginPageReliability($duration)
    {   
        $startTime = microtime(true);

        $errors = [];
        $checkResults = [];

        echo "\n[" . date('Y-m-d H:i:s') . "] Starting reliability test for {$duration} seconds of GitHub login page\n";
        
        while (time() - $startTime < $duration) {
            try {
                echo ".";
                $iterationStart = microtime(true);
                
                $this->driver->get(self::LOGIN_URL);
                $this->assertElementsPresent();
                $this->testFormValidation();
                
                $responseTime = microtime(true) - $iterationStart;
                echo sprintf("\nResponse time: %.2fs", $responseTime);
                
                $checkResults[] = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'success',
                    'response_time' => $responseTime
                ];

            } catch (\Exception $e) {
                echo "\n[ERROR] " . $e->getMessage() . "\n";
                $errors[] = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'error' => $e->getMessage()
                ];
            }
            $this->driver->manage()->deleteAllCookies();      
            // Show progress
            echo "\nCompleted: " . round(((time() - $startTime) / $duration) * 100) . "%";
            sleep(self::CHECK_INTERVAL); // One test per minute
        }

        echo "\nTest completed. Duration: {$duration} seconds\n";
        
        // Generate reliability report
        $this->generateReport($checkResults, $errors, $duration);        
        // Assert reliability threshold
        $reliability = (count($checkResults) - count($errors)) / count($checkResults) * 100;
        $this->assertGreaterThan(99, $reliability, "Reliability below 99%");
    }

    private function assertElementsPresent(): void
    {
        $elements = [
            'login_field' => WebDriverBy::id('login_field'),
            'password' => WebDriverBy::id('password'),
            'submit' => WebDriverBy::cssSelector('input[type="submit"]')
        ];

        foreach ($elements as $name => $locator) {
            $this->assertTrue(
                $this->driver->findElement($locator)->isDisplayed(),
                "Element '$name' not found"
            );
        }
    }

    private function testFormValidation(): void
    {
        $loginField = $this->driver->findElement(WebDriverBy::id('login_field'));
        $loginField->sendKeys('test@example.com');
        
        $passwordField = $this->driver->findElement(WebDriverBy::id('password'));
        $passwordField->sendKeys('invalid');
        
        $submit = $this->driver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $submit->click();
        
        // Verify error message appears
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.flash-error')
            )
        );
    }

    private function generateReport(array $checks, array $errors, int $duration): void
    {
        $report = [
            'test_duration' => [
                'seconds' => $duration,
                'hours' => round($duration / 3600, 2),
                'formatted' => sprintf('%d hour(s)', ceil($duration / 3600))
            ],
            'total_checks' => count($checks),
            'successful_checks' => count($checks) - count($errors),
            'errors' => count($errors), 
            'reliability_rate' => ((count($checks) - count($errors)) / count($checks)) * 100,
            'average_response_time' => array_sum(array_column($checks, 'response_time')) / count($checks),
            'error_details' => $errors
        ];

        file_put_contents(
            'output/reliability_test_report.json',
            json_encode($report, JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND
        );
    }

    public static function reliabilityTestDataProvider(): array
    {
        return [
            '1 hour test' => [3600],
            '4 hour test' => [14400],
            '8 hour test' => [28800]
        ];
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}