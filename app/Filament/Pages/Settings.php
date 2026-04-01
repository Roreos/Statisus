<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title           = 'Application Settings';
    protected static ?string $slug            = 'settings';
    protected static ?int    $navigationSort  = 98;

    public static function getNavigationGroup(): ?string { return null; }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    // Public Livewire properties — all strings so wire:model works cleanly
    public string $APP_NAME      = '';
    public string $APP_URL       = '';
    public string $APP_ENV       = 'production';
    public string $APP_DEBUG     = 'false';

    public string $MAIL_MAILER       = 'log';
    public string $MAIL_HOST         = '';
    public string $MAIL_PORT         = '587';
    public string $MAIL_USERNAME     = '';
    public string $MAIL_PASSWORD     = '';
    public string $MAIL_FROM_ADDRESS = '';
    public string $MAIL_FROM_NAME    = '';

    public string $QUEUE_CONNECTION = 'database';

    public string $TWILIO_SID   = '';
    public string $TWILIO_TOKEN = '';
    public string $TWILIO_FROM  = '';

    public string $PUSHOVER_TOKEN = '';

    public function mount(): void
    {
        // Read directly from .env file to avoid cached config values
        $env = $this->parseEnvFile();

        $get = fn(string $key, string $default = '') => $env[$key] ?? $default;

        $this->APP_NAME  = $get('APP_NAME', 'Status Monitor');
        $this->APP_URL   = $get('APP_URL', 'http://localhost');
        $this->APP_ENV   = $get('APP_ENV', 'production');
        $this->APP_DEBUG = $get('APP_DEBUG', 'false');

        $this->MAIL_MAILER       = $get('MAIL_MAILER', 'log');
        $this->MAIL_HOST         = $get('MAIL_HOST');
        $this->MAIL_PORT         = $get('MAIL_PORT', '587');
        $this->MAIL_USERNAME     = $get('MAIL_USERNAME');
        $this->MAIL_PASSWORD     = $get('MAIL_PASSWORD');
        $this->MAIL_FROM_ADDRESS = $get('MAIL_FROM_ADDRESS');
        $this->MAIL_FROM_NAME    = $get('MAIL_FROM_NAME');

        $this->QUEUE_CONNECTION = $get('QUEUE_CONNECTION', 'database');

        $this->TWILIO_SID   = $get('TWILIO_SID');
        $this->TWILIO_TOKEN = $get('TWILIO_TOKEN');
        $this->TWILIO_FROM  = $get('TWILIO_FROM');

        $this->PUSHOVER_TOKEN = $get('PUSHOVER_TOKEN');
    }

    public function save(): void
    {
        $this->writeEnv([
            'APP_NAME'          => $this->APP_NAME,
            'APP_URL'           => $this->APP_URL,
            'APP_ENV'           => $this->APP_ENV,
            'APP_DEBUG'         => $this->APP_DEBUG,
            'MAIL_MAILER'       => $this->MAIL_MAILER,
            'MAIL_HOST'         => $this->MAIL_HOST,
            'MAIL_PORT'         => $this->MAIL_PORT,
            'MAIL_USERNAME'     => $this->MAIL_USERNAME ?: 'null',
            'MAIL_PASSWORD'     => $this->MAIL_PASSWORD ?: 'null',
            'MAIL_FROM_ADDRESS' => $this->MAIL_FROM_ADDRESS,
            'MAIL_FROM_NAME'    => $this->MAIL_FROM_NAME,
            'QUEUE_CONNECTION'  => $this->QUEUE_CONNECTION,
            'TWILIO_SID'        => $this->TWILIO_SID,
            'TWILIO_TOKEN'      => $this->TWILIO_TOKEN,
            'TWILIO_FROM'       => $this->TWILIO_FROM,
            'PUSHOVER_TOKEN'    => $this->PUSHOVER_TOKEN,
        ]);

        Artisan::call('config:clear');

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /** Parse .env file into key => value array, stripping quotes */
    private function parseEnvFile(): array
    {
        $result = [];
        $lines  = file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            // Strip surrounding quotes
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            // Treat literal "null" as empty
            if ($value === 'null') $value = '';

            $result[trim($key)] = $value;
        }

        return $result;
    }

    /** Write key=value pairs back to .env, always quoting values with spaces */
    private function writeEnv(array $values): void
    {
        $path    = base_path('.env');
        $content = file_get_contents($path);

        foreach ($values as $key => $value) {
            $escaped = (str_contains($value, ' ') || $value === '')
                ? "\"{$value}\""
                : $value;

            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $content);
            } else {
                $content .= "\n{$key}={$escaped}";
            }
        }

        file_put_contents($path, $content);
    }
}
