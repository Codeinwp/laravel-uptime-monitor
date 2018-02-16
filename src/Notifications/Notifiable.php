<?php

namespace Spatie\UptimeMonitor\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    private $to_email;

    public function set_email( $email = '' ) {
    	$this->to_email = $email;
    }

	/**
     * @return string|null
     */
    public function routeNotificationForMail()
    {
        //return config('uptime-monitor.notifications.mail.to');

	    return $this->to_email;
    }

    /**
     * @return string|null
     */
    public function routeNotificationForSlack()
    {
        return config('uptime-monitor.notifications.slack.webhook_url');
    }

    public function getKey(): string
    {
        return static::class;
    }
}
