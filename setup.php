#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * TastyIgniter Extension Setup Script
 *
 * This script configures a new TastyIgniter extension from the template.
 * Run: php setup.php
 */
class ExtensionSetup
{
    private const TEMPLATE_VALUES = [
        'composer_package' => 'tipowerup/ti-ext-template',
        'namespace' => 'Tipowerup\\Template',
        'namespace_escaped' => 'Tipowerup\\\\Template',
        'extension_slug' => 'ti-ext-template',
        'extension_code' => 'tipowerup.template',
        'extension_name' => 'TiPowerUp Template',
        'extension_description' => 'TastyIgniter extension template for TiPowerUp',
        'translation_key' => 'tipowerup.template',
    ];

    private array $config = [];

    private bool $isWindows;

    private bool $supportsColor;

    public function __construct()
    {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $this->supportsColor = $this->detectColorSupport();
    }

    /**
     * Detect if terminal supports ANSI colors
     */
    private function detectColorSupport(): bool
    {
        if (getenv('NO_COLOR') !== false) {
            return false;
        }

        if (getenv('ANSICON') !== false ||
            getenv('WT_SESSION') !== false ||
            getenv('ConEmuANSI') === 'ON' ||
            getenv('TERM_PROGRAM') === 'Hyper' ||
            getenv('TERM_PROGRAM') === 'vscode' ||
            getenv('TERM_PROGRAM') === 'Apple_Terminal') {
            return true;
        }

        if ($this->isWindows) {
            return getenv('ANSICON') !== false ||
                   getenv('WT_SESSION') !== false ||
                   getenv('ConEmuANSI') === 'ON';
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    public function run(): void
    {
        $this->printHeader('TastyIgniter Extension Setup');
        echo "This script will help you set up a new extension from the template.\n\n";
        $this->printWarning('This will modify multiple files in the current directory.');
        echo "\n";

        if (!$this->confirm('Do you want to continue?')) {
            $this->printInfo('Setup cancelled.');
            exit(0);
        }

        $this->collectInformation();
        $this->showSummary();

        if (!$this->confirm('Is this correct?')) {
            $this->printInfo('Setup cancelled.');
            exit(0);
        }

        $this->applyConfiguration();
        $this->showSuccessMessage();
        $this->cleanup();
    }

    private function collectInformation(): void
    {
        $this->printHeader('Extension Information');

        // Extension name
        $this->config['extension_name'] = $this->prompt('Enter extension name (e.g., "My Awesome Extension")', true);

        // Extension slug
        do {
            $this->config['extension_slug'] = $this->prompt('Enter extension slug (e.g., "my-extension")', true);
            $valid = $this->validateSlug($this->config['extension_slug']);
        } while (!$valid);

        // Vendor name
        do {
            $this->config['vendor_name'] = $this->prompt('Enter vendor name (e.g., "tipowerup")', true);
            $valid = $this->validateSlug($this->config['vendor_name']);
        } while (!$valid);

        // Generate namespace suggestion
        $suggestedNamespace = $this->generateNamespace(
            $this->config['vendor_name'],
            $this->config['extension_slug']
        );

        // PHP namespace
        do {
            $default = $suggestedNamespace;
            $this->config['php_namespace'] = $this->prompt(
                "Enter PHP namespace (e.g., '$suggestedNamespace')",
                false,
                $default
            );
            $valid = $this->validateNamespace($this->config['php_namespace']);
        } while (!$valid);

        // Extension description
        do {
            $this->config['extension_description'] = $this->prompt(
                'Enter extension description (optional)',
                false,
                'A custom TastyIgniter extension'
            );
            $valid = $this->validateDescription($this->config['extension_description']);
        } while (!$valid);

        // License type (free or paid)
        $this->printHeader('License Type');
        echo "Choose the license type for your extension:\n";
        echo '  '.$this->colorize('1', 'yellow')." - Free (MIT License) - Open source, can be freely distributed\n";
        echo '  '.$this->colorize('2', 'yellow')." - Paid (TI Powerup License) - Commercial, restricted distribution\n";
        echo "\n";

        do {
            $licenseChoice = $this->prompt('Enter choice (1 or 2)', true);
            $valid = in_array($licenseChoice, ['1', '2'], true);
            if (!$valid) {
                $this->printError('Please enter 1 for Free or 2 for Paid');
            }
        } while (!$valid);

        $this->config['is_free'] = ($licenseChoice === '1');
        $this->config['license_type'] = $this->config['is_free'] ? 'MIT' : 'TI Powerup License';

        // Generate derived values
        $this->config['composer_package'] = $this->config['vendor_name'].'/ti-ext-'.$this->config['extension_slug'];
        $this->config['extension_code'] = $this->config['vendor_name'].'.'.str_replace('-', '', $this->config['extension_slug']);
        $this->config['translation_key'] = $this->config['extension_code'];
        $this->config['namespace_escaped'] = str_replace('\\', '\\\\', $this->config['php_namespace']);
        $this->config['full_slug'] = 'ti-ext-'.$this->config['extension_slug'];
    }

    private function showSummary(): void
    {
        $this->printHeader('Configuration Summary');
        echo sprintf("Extension Name:     %s\n", $this->colorize($this->config['extension_name'], 'green'));
        echo sprintf("Extension Slug:     %s\n", $this->colorize($this->config['extension_slug'], 'green'));
        echo sprintf("Vendor:             %s\n", $this->colorize($this->config['vendor_name'], 'green'));
        echo sprintf("Composer Package:   %s\n", $this->colorize($this->config['composer_package'], 'green'));
        echo sprintf("Extension Code:     %s\n", $this->colorize($this->config['extension_code'], 'green'));
        echo sprintf("PHP Namespace:      %s\n", $this->colorize($this->config['php_namespace'], 'green'));
        echo sprintf("Translation Key:    %s\n", $this->colorize($this->config['translation_key'], 'green'));
        echo sprintf("Description:        %s\n", $this->colorize($this->config['extension_description'], 'green'));
        echo sprintf("License:            %s\n", $this->colorize($this->config['license_type'], $this->config['is_free'] ? 'green' : 'yellow'));
        echo "\n";
    }

    private function applyConfiguration(): void
    {
        $this->printHeader('Applying Configuration');

        // Handle license files
        $this->printInfo('Setting up license...');
        $this->setupLicense();
        $this->printSuccess('License configured ('.$this->config['license_type'].')');

        // Update composer.json
        $this->printInfo('Updating composer.json...');
        $this->replaceInFile('composer.json', [
            self::TEMPLATE_VALUES['composer_package'] => $this->config['composer_package'],
            self::TEMPLATE_VALUES['extension_description'] => $this->config['extension_description'],
            self::TEMPLATE_VALUES['namespace_escaped'] => $this->config['namespace_escaped'],
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
            self::TEMPLATE_VALUES['extension_code'] => $this->config['extension_code'],
            self::TEMPLATE_VALUES['extension_name'] => $this->config['extension_name'],
            '"license": "MIT"' => '"license": "'.($this->config['is_free'] ? 'MIT' : 'proprietary').'"',
        ]);
        $this->printSuccess('composer.json updated');

        // Update Extension.php
        $this->printInfo('Updating src/Extension.php...');
        $this->replaceInFile('src/Extension.php', [
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
        ]);
        $this->printSuccess('Extension.php updated');

        // Swap README files
        $this->printInfo('Setting up README.md...');
        if (file_exists('README-TEMPLATE.md')) {
            @unlink('README.md');
            rename('README-TEMPLATE.md', 'README.md');
        }
        $this->replaceInFile('README.md', [
            self::TEMPLATE_VALUES['extension_name'] => $this->config['extension_name'],
            self::TEMPLATE_VALUES['extension_description'] => $this->config['extension_description'],
            self::TEMPLATE_VALUES['composer_package'] => $this->config['composer_package'],
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
            self::TEMPLATE_VALUES['extension_code'] => $this->config['extension_code'],
            self::TEMPLATE_VALUES['extension_slug'] => $this->config['full_slug'],
        ]);
        $this->printSuccess('README.md updated');

        // Update resources/lang/en/default.php
        $this->printInfo('Updating resources/lang/en/default.php...');
        $this->replaceInFile('resources/lang/en/default.php', [
            self::TEMPLATE_VALUES['translation_key'] => $this->config['translation_key'],
        ]);
        $this->printSuccess('default.php updated');

        // Update test files
        $this->printInfo('Updating test files...');
        $this->replaceInFile('tests/TestCase.php', [
            self::TEMPLATE_VALUES['namespace_escaped'] => $this->config['namespace_escaped'],
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
        ]);
        $this->replaceInFile('tests/Pest.php', [
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
        ]);
        $this->replaceInFile('tests/Feature/ExtensionTest.php', [
            self::TEMPLATE_VALUES['namespace'] => $this->config['php_namespace'],
        ]);
        $this->printSuccess('Test files updated');
    }

    private function setupLicense(): void
    {
        $year = date('Y');

        if ($this->config['is_free']) {
            $sourceFile = 'LICENSE-TEMPLATE-FREE.md';
            $deleteFile = 'LICENSE-TEMPLATE-PAID.md';
        } else {
            $sourceFile = 'LICENSE-TEMPLATE-PAID.md';
            $deleteFile = 'LICENSE-TEMPLATE-FREE.md';
        }

        // Delete existing LICENSE.md if present
        if (file_exists('LICENSE.md')) {
            @unlink('LICENSE.md');
        }

        // Read the source license and replace year placeholder
        if (file_exists($sourceFile)) {
            $content = file_get_contents($sourceFile);
            $content = str_replace('[YEAR]', $year, $content);
            file_put_contents('LICENSE.md', $content);
            @unlink($sourceFile);
            $this->printSuccess("Created LICENSE.md from {$sourceFile}");
        }

        // Delete the other license file
        if (file_exists($deleteFile)) {
            @unlink($deleteFile);
        }
    }

    private function cleanup(): void
    {
        $this->printHeader('Cleanup');

        echo "\n";
        $this->printWarning('The setup files (setup.php and SETUP.md) can now be removed.');
        echo "\n";

        if ($this->confirm('Do you want to delete the setup files? (RECOMMENDED)', true)) {
            $this->printInfo('Removing setup files...');

            if (file_exists('SETUP.md') && @unlink('SETUP.md')) {
                $this->printSuccess('SETUP.md removed');
            }

            if (file_exists('license-headers.md') && @unlink('license-headers.md')) {
                $this->printSuccess('license-headers.md removed');
            }

            if (file_exists('tipowerup-license.md') && @unlink('tipowerup-license.md')) {
                $this->printSuccess('tipowerup-license.md removed');
            }

            // Self-destruct
            $this->printInfo('Removing setup script...');
            @unlink(__FILE__);
            $this->printSuccess('Setup files removed');
        } else {
            $this->printInfo('Setup files kept. You can delete them manually later:');
            echo '  - '.$this->colorize('setup.php', 'yellow')."\n";
            echo '  - '.$this->colorize('SETUP.md', 'yellow')."\n";
            echo '  - '.$this->colorize('license-headers.md', 'yellow')."\n";
            echo '  - '.$this->colorize('tipowerup-license.md', 'yellow')."\n";
        }
    }

    private function showSuccessMessage(): void
    {
        $this->printHeader('Setup Complete!');
        echo $this->colorize("Your extension '{$this->config['extension_name']}' has been configured successfully!", 'green')."\n\n";

        $this->printInfo('Next steps:');
        echo "  1. Install dependencies:\n";
        echo '     '.$this->colorize('composer install', 'yellow')."\n";
        echo "\n";
        echo "  2. Start building your extension in:\n";
        echo '     - '.$this->colorize('src/Extension.php', 'yellow')." for main extension class\n";
        echo '     - '.$this->colorize('src/Models/', 'yellow')." for Eloquent models\n";
        echo '     - '.$this->colorize('src/Http/Controllers/', 'yellow')." for controllers\n";
        echo '     - '.$this->colorize('database/migrations/', 'yellow')." for migrations\n";
        echo '     - '.$this->colorize('resources/views/', 'yellow')." for Blade views\n";
        echo '     - '.$this->colorize('resources/lang/', 'yellow')." for translations\n";
        echo "\n";
        echo "  3. Run tests:\n";
        echo '     '.$this->colorize('composer test', 'yellow')."\n";
        echo "\n";
        echo "  4. Install in TastyIgniter:\n";
        echo '     '.$this->colorize('php artisan igniter:extension-install '.$this->config['extension_code'], 'yellow')."\n";
        echo "\n";

        $this->printSuccess('Happy coding!');
    }

    // Utility methods

    private function prompt(string $message, bool $required = false, string $default = ''): string
    {
        if (!defined('STDIN') || !is_resource(STDIN)) {
            if ($default !== '') {
                return $default;
            }

            throw new RuntimeException('Cannot prompt for input in non-interactive environment');
        }

        do {
            echo $message;
            if ($default) {
                echo " [{$default}]";
            }
            echo ': ';

            $input = fgets(STDIN);
            if ($input === false) {
                throw new RuntimeException('Failed to read input from STDIN');
            }
            $input = trim($input);

            if ($input === '' && $default !== '') {
                $input = $default;
            }

            if (!$required || $input !== '') {
                return $input;
            }

            $this->printError('This field is required');
        } while (true);
    }

    private function confirm(string $message, bool $defaultYes = false): bool
    {
        if (!defined('STDIN') || !is_resource(STDIN)) {
            return $defaultYes;
        }

        $hint = $defaultYes ? '[Y/n]' : '[y/N]';
        echo "{$message} {$hint}: ";

        $input = strtolower(trim(fgets(STDIN) ?: ''));

        if ($input === '') {
            return $defaultYes;
        }

        return $input === 'y' || $input === 'yes';
    }

    private function validateSlug(string $slug): bool
    {
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            $this->printError("Invalid format. Use lowercase letters, numbers, and hyphens only (e.g., 'my-extension')");

            return false;
        }

        return true;
    }

    private function validateNamespace(string $namespace): bool
    {
        $segments = explode('\\', $namespace);
        foreach ($segments as $segment) {
            if (preg_match('/^[0-9]/', $segment)) {
                $this->printError("Invalid format. Namespace segments cannot start with numbers (segment: '{$segment}')");

                return false;
            }
        }

        if (!preg_match('/^[A-Z][a-zA-Z0-9]*(\\\\[A-Z][a-zA-Z0-9]*)*$/', $namespace)) {
            $this->printError("Invalid format. Use PascalCase with backslashes (e.g., 'MyCompany\\MyExtension')");

            return false;
        }

        return true;
    }

    private function validateDescription(string $description): bool
    {
        if (trim($description) === '') {
            $this->printError('Description cannot be empty or whitespace only');

            return false;
        }

        return true;
    }

    private function generateNamespace(string $vendor, string $slug): string
    {
        $vendorPascal = str_replace(' ', '', ucwords(str_replace('-', ' ', $vendor)));
        $slugPascal = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));

        return "{$vendorPascal}\\{$slugPascal}";
    }

    private function replaceInFile(string $file, array $replacements): void
    {
        if (!file_exists($file)) {
            $this->printWarning("File not found: {$file}");

            return;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            $this->printError("Failed to read file: {$file}");

            throw new RuntimeException("Cannot read file: {$file}");
        }

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if (@file_put_contents($file, $content) === false) {
            $this->printError("Failed to write file: {$file}");

            throw new RuntimeException("Cannot write file: {$file}");
        }
    }

    // Output formatting methods

    private function colorize(string $text, string $color): string
    {
        if (!$this->supportsColor) {
            return $text;
        }

        $colors = [
            'red' => "\033[0;31m",
            'green' => "\033[0;32m",
            'yellow' => "\033[1;33m",
            'blue' => "\033[0;34m",
            'reset' => "\033[0m",
        ];

        return ($colors[$color] ?? '').$text.$colors['reset'];
    }

    private function printSuccess(string $message): void
    {
        $symbol = $this->supportsColor ? '✓' : '[OK]';
        echo $this->colorize($symbol.' ', 'green').$message."\n";
    }

    private function printError(string $message): void
    {
        $symbol = $this->supportsColor ? '✗' : '[ERROR]';
        echo $this->colorize($symbol.' ', 'red').$message."\n";
    }

    private function printInfo(string $message): void
    {
        $symbol = $this->supportsColor ? 'ℹ' : '[INFO]';
        echo $this->colorize($symbol.' ', 'blue').$message."\n";
    }

    private function printWarning(string $message): void
    {
        $symbol = $this->supportsColor ? '⚠' : '[WARN]';
        echo $this->colorize($symbol.' ', 'yellow').$message."\n";
    }

    private function printHeader(string $message): void
    {
        $divider = $this->supportsColor
            ? '═══════════════════════════════════════════════'
            : '===============================================';

        echo "\n".$this->colorize($divider, 'blue')."\n";
        echo $this->colorize("  {$message}", 'blue')."\n";
        echo $this->colorize($divider, 'blue')."\n\n";
    }
}

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    exit("This script must be run from the command line.\n");
}

// Run the setup
try {
    $setup = new ExtensionSetup;
    $setup->run();
} catch (Exception $e) {
    $tempSetup = new ExtensionSetup;
    $reflector = new ReflectionClass($tempSetup);
    $supportsColorProperty = $reflector->getProperty('supportsColor');
    $supportsColorProperty->setAccessible(true);
    $supportsColor = $supportsColorProperty->getValue($tempSetup);

    $errorSymbol = $supportsColor ? '✗' : '[ERROR]';
    $errorColor = $supportsColor ? "\033[0;31m" : '';
    $reset = $supportsColor ? "\033[0m" : '';

    echo "\n".$errorColor.$errorSymbol.' Error: '.$e->getMessage().$reset."\n";
    exit(1);
}
