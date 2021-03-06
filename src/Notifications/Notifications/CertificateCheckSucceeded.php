<?php

namespace Spatie\UptimeMonitor\Notifications\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\UptimeMonitor\Notifications\BaseNotification;
use Spatie\UptimeMonitor\Events\CertificateCheckSucceeded as ValidCertificateFoundEvent;

class CertificateCheckSucceeded extends BaseNotification
{
    /** @var \Spatie\UptimeMonitor\Events\CertificateCheckSucceeded */
    public $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
	    $notifiable->set_email( $this->getEmail() );

        $mailMessage = (new MailMessage)
	        ->to( $this->getEmail() )
            ->subject($this->getMessageText())
            ->line($this->getMessageText());

        foreach ($this->getMonitorProperties() as $name => $value) {
            $mailMessage->line($name.': '.$value);
        }
	    $mailMessage->view('emails_uptime_up');
        return $mailMessage;
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getMessageText())
                    ->content("Expires {$this->getMonitor()->formattedCertificateExpirationDate('forHumans')}")
                    ->fallback($this->getMessageText())
                    ->footer($this->getMonitor()->certificate_issuer)
                    ->timestamp(Carbon::now());
            });
    }

    public function setEvent(ValidCertificateFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }

	protected function getEmail(): string
	{
		return $this->event->monitor->email;
	}

    public function getMessageText(): string
    {
        return "SSL certificate for {$this->event->monitor->url} is valid";
    }
}
