<?php

namespace App\Notifications;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Services\CompanyFeature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

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

        // Riwayat internal selalu tersimpan, termasuk untuk SR/R/T.
        $channels = ['database'];

        if (app(CompanyFeature::class)->webPushEligible() && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        // Surat berklasifikasi SR/R/T tidak boleh bocor lewat kanal eksternal.
        if ($this->restricted()) {
            return $channels;
        }

        if (app(CompanyFeature::class)->enabled('email') && filled($notifiable->routeNotificationFor('mail'))) {
            $channels[] = 'mail';
        }

        if (app(CompanyFeature::class)->enabled('telegram') && filled($notifiable->routeNotificationForTelegram())) {
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
            'database' => self::QUEUE,
            WebPushChannel::class => self::QUEUE,
            'mail' => self::QUEUE,
            TelegramChannel::class => self::QUEUE,
        ];
    }

    /**
     * Baris riwayat internal yang dibaca halaman /notifications dan bell.
     *
     * @return array<string, string|int|null>
     */
    public function toDatabase(object $notifiable): array
    {
        $context = $this->context();

        $data = [
            'title' => $context['label'],
            'body' => $context['subject'],
            'url' => $context['url'],
            'item_type' => $this->itemType(),
            'item_id' => $this->item->id,
            'security_classification' => $this->classification(),
            'received_via' => $this->receivedVia(),
        ];

        if ($this->item instanceof Disposition || $this->item instanceof Memo) {
            $data['instruction'] = $this->item->instruction;
            $data['followup'] = $this->item->followup;
        }

        return $data;
    }

    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        $context = $this->context();
        $message = (new WebPushMessage)
            ->title($context['label'])
            ->body($this->restricted()
                ? 'Surat baru telah masuk. Buka e-Surat untuk melihat detail.'
                : $context['subject'])
            ->data([
                'url' => $this->openUrl($context['url']),
                'security_classification' => $this->classification(),
                'received_via' => $this->receivedVia(),
            ]);

        return $message;
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
            ->action('Buka e-Surat', $this->openUrl($context['url']))
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

        $lines[] = $this->openUrl($context['url']);
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

        if ($this->restricted()) {
            $subject = '(perihal dirahasiakan)';
        }

        if ($item instanceof LetterRecipient) {
            $undangan = $letter?->category === '2';

            return [
                'label' => $undangan ? 'Surat Undangan' : 'Surat Masuk Utama',
                'subject' => $subject,
                'url' => route($undangan ? 'inbox.undangan.show' : 'inbox.primary.show', [
                    'recipient' => $item->id,
                    'letter_id' => $letterId,
                ]),
            ];
        }

        if ($item instanceof Disposition) {
            return [
                'label' => 'Disposisi',
                'subject' => $subject,
                'url' => route('dispo.show', [
                    'recipient' => $item->id,
                    'letter_id' => $letterId,
                ]),
            ];
        }

        return [
            'label' => 'Nota Dinas',
            'subject' => $subject,
            'url' => route('memo.show', [
                'recipient' => $item->id,
                'letter_id' => $letterId,
            ]),
        ];
    }

    /**
     * URL klik kanal eksternal: tandai notifikasi dibaca lalu buka detail.
     */
    private function openUrl(string $fallback): string
    {
        return filled($this->id)
            ? route('notifications.open', $this->id)
            : $fallback;
    }

    /**
     * Nama pengirim: pengirim surat untuk item primary/undangan,
     * nama jabatan pengirim untuk disposisi dan nota dinas.
     */
    private function sender(): ?string
    {
        if ($this->restricted()) {
            return null;
        }

        $item = $this->item;

        if ($item instanceof LetterRecipient) {
            return filled($item->letter?->sender) ? (string) $item->letter->sender : null;
        }

        return filled($item->fromPosition?->name) ? (string) $item->fromPosition->name : null;
    }

    /**
     * Nilai klasifikasi yang disimpan; data legacy/null jatuh ke `B`.
     */
    private function classification(): string
    {
        $this->item->loadMissing('letter');
        $classification = $this->item->letter?->security_classification;

        return in_array($classification, ['SR', 'R', 'T', 'B'], true) ? $classification : 'B';
    }

    /**
     * Sumber penerimaan yang disimpan; data legacy/null jatuh ke `other`.
     */
    private function receivedVia(): string
    {
        $this->item->loadMissing('letter');
        $receivedVia = $this->item->letter?->received_via;

        return in_array($receivedVia, ['email', 'physical', 'internal', 'other'], true) ? $receivedVia : 'other';
    }

    private function itemType(): string
    {
        return match (true) {
            $this->item instanceof LetterRecipient => $this->item->letter?->category === '2' ? 'invitation' : 'primary',
            $this->item instanceof Disposition => 'disposition',
            default => 'memo',
        };
    }

    /**
     * Klasifikasi selain Biasa/Terbuka tidak boleh keluar lewat email/Telegram.
     */
    private function restricted(): bool
    {
        return in_array($this->classification(), ['SR', 'R', 'T'], true);
    }
}
