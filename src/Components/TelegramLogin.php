<?php

namespace Scriptoshi\LivewireTelegramAuth\Components;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use TgWebValid\TgWebValid;
use TgWebValid\Exceptions\BotException;
use TgWebValid\Exceptions\ValidationException;

class TelegramLogin extends Component
{
    public ?string $error = null;
    public bool $isLoaded = false;

    /**
     * Get the Telegram Bot ID from config
     */
    public function getBotIdProperty(): string
    {
        return str_replace('bot', '', config('services.telegram.telegramBotToken'));
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('telegram-auth::telegram-login');
    }

    /**
     * Handle Telegram authentication
     */
    public function telegramAuth($data): void
    {
        $this->error = null;

        try {
            $tgWebValid = new TgWebValid(config('services.telegram.telegramBotToken'), true);
            $userData = $tgWebValid->bot()->validateLoginWidget($data);

            // Authenticate or create user
            $user = $this->authenticate($userData);

            // Redirect to dashboard after successful login
            $this->redirect(route('dashboard', absolute: false));
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        } catch (BotException $e) {
            $this->error = 'Bot mismatch. Please contact support.';
        } catch (\Exception $e) {
            $this->error = 'An error occurred: ' . $e->getMessage();
        }
    }

    /**
     * Login or Register user
     */
    protected function authenticate($udata)
    {
        $user = User::where('telegramId', $udata->id)->first();
        if ($user) {
            return $this->login($user);
        }
        return $this->register($udata);
    }

    /**
     * Log in existing user
     */
    protected function login($user)
    {
        Auth::login($user);
        return $user;
    }

    /**
     * Register new user
     */
    protected function register($udata)
    {
        $telegram_photo_path = $this->saveProfilePhoto($udata);
        $disk = config('services.telegram.profile_photo_disk', 'public');
        $user = User::create([
            'name' => $udata->firstName . ' ' . ($udata->lastName ?? ''),
            'username' => $udata->username ?? null,
            'email' => null,
            'password' => null,
            'email_verified_at' => now()
        ]);
        $user->telegramId = $udata->id;
        $user->telegram_photo_path  = $telegram_photo_path;
        $user->telegram_avatar_url = $telegram_photo_path ? Storage::disk($disk)->url($telegram_photo_path) : null;
        $user->save();
        event(new Registered($user));
        Auth::login($user);
        return $user;
    }

    /**
     * Save profile photo from Telegram
     */
    protected function saveProfilePhoto($udata)
    {
        if (isset($udata->photoUrl)) {
            return $this->downloadProfilePhoto($udata->photoUrl, $udata->id);
        }

        $response = $this->getUserProfilePhotos($udata->id);
        if (!isset($response->ok) || !$response->ok) {
            return null;
        }

        $photos = $response->result->photos;
        $photo = $photos[0][2] ?? $photos[0][1] ?? $photos[0][0] ?? null;

        if (!$photo || !isset($photo->file_id)) {
            return null;
        }

        $fileUrl = $this->getFileUrl($photo->file_id);
        return $this->downloadProfilePhoto($fileUrl, $udata->id);
    }

    /**
     * Download profile photo from URL
     */
    protected function downloadProfilePhoto($url, $uid)
    {
        try {
            $file = file_get_contents($url);
            $extension = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
            $path = 'profile-photos/' . $uid . '.' . $extension;
            $disk = config('services.telegram.profile_photo_disk', 'public');

            if (Storage::disk($disk)->exists($path)) {
                return $path;
            }

            if (Storage::disk($disk)->put($path, $file, 'public')) {
                return $path;
            }
        } catch (\Exception $e) {
            // Silently fail and return null if unable to download
        }

        return null;
    }

    /**
     * Get Telegram API base URI
     */
    protected function telegramUri(): string
    {
        $token = config('services.telegram.telegramBotToken');
        return "https://api.telegram.org/bot$token";
    }

    /**
     * Get user profile photos from Telegram API
     */
    protected function getUserProfilePhotos($userId)
    {
        $url = $this->telegramUri() . '/getUserProfilePhotos';
        $data = ['user_id' => $userId];

        return $this->makeRequest($url, $data);
    }

    /**
     * Get file information from Telegram API
     */
    protected function getFile($fileId)
    {
        $url = $this->telegramUri() . '/getFile';
        $data = ['file_id' => $fileId];

        return $this->makeRequest($url, $data);
    }

    /**
     * Get file URL from Telegram API
     */
    protected function getFileUrl($fileId)
    {
        $file = $this->getFile($fileId);
        $token = config('services.telegram.telegramBotToken');

        if (!isset($file->result->file_path)) {
            return null;
        }

        return "https://api.telegram.org/file/bot{$token}/{$file->result->file_path}";
    }

    /**
     * Make an HTTP request to Telegram API
     */
    protected function makeRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }
}
