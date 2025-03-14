<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class GitHubStressTest extends TestCase
{
    private $driver;
    private const LOGIN_URL = 'https://github.com/login';
    private const MAX_RESPONSE_TIME = 5.0; // seconds

    protected function setUp(): void
    {
        $options = new ChromeOptions();
        $options->addArguments(['--no-sandbox', '--headless']);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        
        $this->driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);
    }

    /**
     * @test
     * @dataProvider loadTestDataProvider
     */
    public function testLoginPageLoadTime($iterations)
    {
        $loadTimes = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            
            try {
                $this->driver->get(self::LOGIN_URL);
                $loadTime = microtime(true) - $start;
                $loadTimes[] = $loadTime;
                
                $this->assertLessThan(
                    self::MAX_RESPONSE_TIME,
                    $loadTime,
                    "Response time exceeded threshold on iteration {$i}"
                );
                
                $this->logMetrics($i, $loadTime);
                $this->driver->manage()->deleteAllCookies();
                sleep(1); // Prevent rate limiting
                
            } catch (\Exception $e) {
                $this->fail("Iteration {$i} failed: " . $e->getMessage());
            }
        }
        
        $this->outputStats($loadTimes);
    }

    public static function loadTestDataProvider(): array
    {
        return [
            'small load test' => [10],
            'medium load test' => [50],
            'large load test' => [100]
        ];
    }

    private function logMetrics($iteration, $loadTime): void
    {
        $metrics = [
            'iteration' => $iteration,
            'timestamp' => date('Y-m-d H:i:s'),
            'response_time' => round($loadTime, 3),
            'memory_usage' => memory_get_usage(true)
        ];
        
        file_put_contents(
            'output/stress_test_metrics.log',
            json_encode($metrics) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function outputStats(array $loadTimes): void
    {
        $avg = array_sum($loadTimes) / count($loadTimes);
        $max = max($loadTimes);
        
        echo sprintf(
            "\nTest Statistics:\nAvg Response: %.2fs\nMax Response: %.2fs\n",
            $avg,
            $max
        );
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}