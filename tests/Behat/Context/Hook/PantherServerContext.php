<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusPaymentFeePlugin\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\Process\Process;

final class PantherServerContext implements Context
{
    private static ?Process $webServer = null;
    private static ?int $port = null;

    /**
     * @BeforeScenario @javascript
     */
    public function startWebServerBeforeScenario(BeforeScenarioScope $scope): void
    {
        if (self::$webServer !== null && self::$webServer->isRunning()) {
            return; // Server already running
        }

        // Use fixed port 9080 to match PANTHER_EXTERNAL_BASE_URI
        self::$port = 9080;

        // Start PHP built-in web server
        $webServerDir = __DIR__ . '/../../../Application/public';
        if (!is_dir($webServerDir)) {
            throw new \RuntimeException(sprintf('Web server directory not found: %s', $webServerDir));
        }
        $webServerDir = realpath($webServerDir);

        $command = sprintf(
            'APP_ENV=test php -S 127.0.0.1:%d -t %s',
            self::$port,
            $webServerDir
        );

        self::$webServer = Process::fromShellCommandline($command);
        self::$webServer->setTimeout(null);
        self::$webServer->start();

        echo sprintf("\nStarting web server on port %d...\n", self::$port);

        // Wait for server to be ready
        $this->waitForServerReady();

        echo sprintf("Web server ready on http://127.0.0.1:%d\n", self::$port);
    }


    private function waitForServerReady(): void
    {
        $start = time();
        $timeout = 30;

        while (time() - $start < $timeout) {
            // Check if process is still running
            if (self::$webServer !== null && !self::$webServer->isRunning()) {
                $exitCode = self::$webServer->getExitCode();
                throw new \RuntimeException(sprintf(
                    'Web server process died with exit code: %s',
                    $exitCode ?? 'unknown'
                ));
            }

            if (self::$port !== null) {
                try {
                    $fp = @fsockopen('127.0.0.1', self::$port, $errno, $errstr, 1);
                    if ($fp !== false) {
                        fclose($fp);
                        usleep(500000); // Wait 500ms more for server to fully initialize
                        return;
                    }
                } catch (\Exception $e) {
                    // Server not ready yet
                }
            }
            usleep(100000); // Wait 100ms before retry
        }

        $status = (self::$webServer !== null && self::$webServer->isRunning()) ? 'running' : 'stopped';
        throw new \RuntimeException(sprintf(
            'Web server failed to start within timeout (process status: %s, port: %d)',
            $status,
            self::$port ?? 0
        ));
    }

    public static function getServerPort(): ?int
    {
        return self::$port;
    }
}
