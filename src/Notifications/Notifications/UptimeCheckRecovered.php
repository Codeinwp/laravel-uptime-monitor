<?php

namespace Spatie\UptimeMonitor\Notifications\Notifications;

use Carbon\Carbon;
use Spatie\UptimeMonitor\Notifications\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\UptimeMonitor\Models\Enums\UptimeStatus;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\UptimeMonitor\Notifications\BaseNotification;
use Spatie\UptimeMonitor\Events\UptimeCheckRecovered as MonitorRecoveredEvent;

class UptimeCheckRecovered extends BaseNotification
{
	/** @var \Spatie\UptimeMonitor\Events\UptimeCheckRecovered */
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
			->success()
			->to( $this->getEmail() )
			->subject($this->getSubject())
			->line($this->getMessageText())
			->line($this->getLocationDescription());

		foreach ($this->getMonitorProperties() as $name => $value) {
			$mailMessage->line($name.': '.$value);
		}
		$mailMessage->view('emails_uptime_up');
		return $mailMessage;
	}

	public function toSlack($notifiable)
	{
		return (new SlackMessage)
			->success()
			->attachment(function (SlackAttachment $attachment) {
				$attachment
					->title($this->getMessageText())
					->fallback($this->getMessageText())
					->footer($this->getLocationDescription())
					->timestamp(Carbon::now());
			});
	}

	public function getMonitorProperties($extraProperties = []): array
	{

		return parent::getMonitorProperties($extraProperties);
	}

	public function isStillRelevant(): bool
	{
		return $this->event->monitor->uptime_status == UptimeStatus::UP;
	}

	protected function getEmail(): string
	{
		return $this->event->monitor->email;
	}

	public function setEvent(MonitorRecoveredEvent $event)
	{
		$this->event = $event;

		return $this;
	}

	public function getSubject(): string
	{
		return "{$this->event->monitor->url} is UP";
	}
	public function getMessageText(): string
	{
		return "{$this->event->monitor->url} is UP after {$this->event->downtimePeriod->duration()}.";
	}
}
