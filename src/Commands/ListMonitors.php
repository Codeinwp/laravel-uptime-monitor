<?php

namespace Spatie\UptimeMonitor\Commands;

use Spatie\UptimeMonitor\MonitorRepository;
use Spatie\UptimeMonitor\Commands\MonitorLists\Healthy;
use Spatie\UptimeMonitor\Commands\MonitorLists\Disabled;
use Spatie\UptimeMonitor\Commands\MonitorLists\Unchecked;
use Spatie\UptimeMonitor\Commands\MonitorLists\UptimeCheckFailed;
use Spatie\UptimeMonitor\Commands\MonitorLists\CertificateCheckFailed;

class ListMonitors extends BaseCommand
{
    protected $signature = 'monitor:list {--api}';

    protected $description = 'List all monitors';

    public function handle()
    {
        $this->line('');

        if (! MonitorRepository::getEnabled()->count()) {
            $this->warn('There are no monitors created or enabled.');
            $this->info('You create a monitor using the `monitor:create {url}` command');
        }

	    $isApiCall = $this->option('api');
	    if( isset( $isApiCall ) && $isApiCall == true ) {
		    $healthyMonitor = MonitorRepository::getEnabled();

		    $results = $healthyMonitor->map(function (Monitor $monitor) {
			    $url = (string) $monitor->url;
			    $email = (string) $monitor->email;

			    $reachable = $monitor->uptime_status;

			    $onlineSince = $monitor->formattedLastUpdatedStatusChangeDate('forHumans');

			    return compact( 'url','email', 'reachable', 'onlineSince' );
		    });

		    echo json_encode( array(
		    	'status' => 200,
			    'message' => 'Ok',
			    'data' => $results
		    ), true );
		    return;
	    }

        Unchecked::display();
        Disabled::display();
        UptimeCheckFailed::display();
        CertificateCheckFailed::display();
        Healthy::display();
    }
}
