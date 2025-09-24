namespace App\Listeners;

use Laravel\Horizon\Events\JobFailed;
use Notification;
use App\Notifications\HorizonTelegramNotification;

class NotifyHorizonViaTelegram
{
    public function handle(JobFailed $event): void
    {
        Notification::route('telegram', config('services.telegram-bot.chat_id'))
            ->notify(new HorizonTelegramNotification($event));
    }
}
