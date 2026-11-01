<?php

namespace App\Notifications;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class InboxItemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Delivery eksternal (email & Telegram) memakai queue tersendiri agar
     * tidak memperlambat request pembuat item.
     */
    public const QUEUE = 'notifications';

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60];

    public function __construct(
        public LetterRecipient|Disposition|Memo $item
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        $channels = [];

        if (filled($notifiable->routeNotificationFor('mail'))) {
            $channels[] = 'mail';
        }

        if (filled($notifiable->routeNotificationForTelegram())) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    /**
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => self::QUEUE,
            TelegramChannel::class => self::QUEUE,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $context = $this->context();
        $sender = $this->sender();

        $mail = (new MailMessage)
            ->subject($context['label'].': '.$context['subject'])
            ->greeting('Halo '.($notifiable instanceof User ? $notifiable->name : 'Bapak/Ibu').',')
            ->line($context['label'].' telah masuk ke inbox jabatan Anda.')
            ->line('Perihal: '.$context['subject']);

        if ($sender !== null) {
            $mail->line('Pengirim: '.$sender);
        }

        return $mail
            ->action('Buka e-Surat', $context['url'])
            ->line('Email ini dikirim otomatis oleh sistem e-Surat.');
    }

    /**
     * Teks polos untuk Telegram, dijaga di bawah batas 4096 karakter.
     */
    public function toTelegram(object $notifiable): string
    {
        $context = $this->context();

        $lines = [
            $context['label'],
            'Perihal: '.$context['subject'],
        ];

        if (($sender = $this->sender()) !== null) {
            $lines[] = 'Pengirim: '.$sender;
        }

        $lines[] = $context['url'];
        $lines[] = 'Pesan otomatis sistem e-Surat.';

        return Str::limit(implode("\n", $lines), 3500);
    }

    /**
     * Jenis item, label, dan URL detail yang dipakai kedua kanal.
     *
     * @return array{label: string, subject: string, url: string}
     */
    private function context(): array
    {
        $item = $this->item;
        $item->loadMissing(['letter', 'fromPosition', 'toPosition']);

        $letter = $item->letter;
        $subject = filled($letter?->subject) ? (string) $letter->subject : '(tanpa perihal)';
        $letterId = $letter === null ? '' : (string) $letter->letter_id;

        if ($item instanceof LetterRecipient) {
            $undangan = $letter?->category === '2';

            return [
                'label' => $undangan ? 'Surat undangan baru' : 'Surat masuk primary baru',
                'subject' => $subject,
                'url' => route($undangan ? 'inbox.undangan.show' : 'inbox.primary.show', [
                    'recipient' => $item->id,
                    'letter_id' => $letterId,
                ]),
            ];
        }

        if ($item instanceof Disposition) {
            return [
                'label' => 'Disposisi baru',
                'subject' => $subject,
                'url' => route('dispo.show', [
                    'recipient' => $item->id,
                    'letter_id' => $letterId,
                ]),
            ];
        }

        return [
            'label' => 'Nota dinas baru',
            'subject' => $subject,
            'url' => route('memo.show', [
                'recipient' => $item->id,
                'letter_id' => $letterId,
            ]),
        ];
    }

    /**
     * Nama pengirim: pengirim surat untuk item primary/undangan,
     * nama jabatan pengirim untuk disposisi dan nota dinas.
     */
    private function sender(): ?string
    {
        $item = $this->item;

        if ($item instanceof LetterRecipient) {
            return filled($item->letter?->sender) ? (string) $item->letter->sender : null;
        }

        return filled($item->fromPosition?->name) ? (string) $item->fromPosition->name : null;
    }
}
