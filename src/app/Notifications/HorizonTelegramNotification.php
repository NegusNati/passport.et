namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Laravel\Horizon\Events\JobFailed;

class HorizonTelegramNotification extends Notification
{
    public function __construct(protected JobFailed $event) {}

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $job = class_basename($this->event->payload['displayName'] ?? 'job');

        return TelegramMessage::create()
            ->content("Horizon alert: {$job} failed on {$this->event->queue} queue.");
    }
}
