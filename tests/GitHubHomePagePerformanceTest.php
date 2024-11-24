<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\WebDriverCurlException;

class GitHubHomePagePerformanceTest extends TestCase
{
    private $webDriver;
    private $metrics = [];
    private $startTime;
    private const MAX_RETRIES = 3;
    private const TIMEOUT = 60; // seconds

    protected function setUp(): void
    {
        $options = new ChromeOptions();
        $options->addArguments([
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--ignore-certificate-errors'
        ]);
        
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        
        // Initialize WebDriver with retry logic
        $this->initializeWebDriver($capabilities);
        $this->startTime = microtime(true);
    }

    private function initializeWebDriver($capabilities)
    {
        $retry = 0;
        while ($retry < self::MAX_RETRIES) {
            try {
                $this->webDriver = RemoteWebDriver::create(
                    'http://localhost:9515',
                    $capabilities,
                    self::TIMEOUT * 1000, // connection timeout in milliseconds
                    self::TIMEOUT * 1000  // request timeout in milliseconds
                );
                return;
            } catch (\Exception $e) {
                $retry++;
                if ($retry === self::MAX_RETRIES) {
                    throw new RuntimeException(
                        "Failed to initialize WebDriver after {$retry} attempts: " . $e->getMessage()
                    );
                }
                sleep(2); // Wait before retry
            }
        }
    }

    public function testGitHubHomepagePerformance()
    {
        try {
            // Initial page load time with retry
            $loadSuccess = false;
            $retry = 0;
            
            while (!$loadSuccess && $retry < self::MAX_RETRIES) {
                try {
                    $start = microtime(true);
                    $this->webDriver->get('https://github.com');
                    $initialLoadTime = microtime(true) - $start;
                    $this->metrics['initial_load'] = round($initialLoadTime, 2);
                    $loadSuccess = true;
                } catch (WebDriverCurlException $e) {
                    $retry++;
                    if ($retry === self::MAX_RETRIES) {
                        throw $e;
                    }
                    sleep(2);
                }
            }

            $domStart = microtime(true);
            $this->webDriver->wait(self::TIMEOUT)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('body')
                )
            );
            $domLoadTime = microtime(true) - $domStart;
            $this->metrics['dom_ready'] = round($domLoadTime, 2);
            
            $this->metrics['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2);
            
            // Performance assertions
            $this->assertLessThan(5.0, $initialLoadTime, 'Initial page load too slow');
            $this->assertLessThan(2.0, $domLoadTime, 'DOM ready time too slow');
            $this->assertLessThan(50, $this->metrics['memory_usage'], 'Memory usage too high');
            
            $this->webDriver->wait(self::TIMEOUT)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('.logged-out, .logged-in')
                )
            );
        } catch (\Exception $e) {
            $this->metrics['error'] = $e->getMessage();
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        $this->metrics['total_time'] = round($totalTime, 2);
        
        file_put_contents(
            __DIR__ . 'output/homepage_performance_report.json',
            json_encode($this->metrics, JSON_PRETTY_PRINT)
        );
        
        if ($this->webDriver) {
            $this->webDriver->quit();
        }
    }
}