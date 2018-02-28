<?php

namespace Spatie\UptimeMonitor\Commands;

use Illuminate\Support\Facades\Mail;
use Spatie\Url\Url;
use Spatie\UptimeMonitor\Models\Monitor;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;

class CreateMonitor extends BaseCommand
{
    protected $signature = 'monitor:create {url} {--api} {--string=} {--email=}';

    protected $description = 'Create a monitor';

    public function handle()
    {
        $url = Url::fromString($this->argument('url'));
        $email = $this->option('email');

        if (! in_array($url->getScheme(), ['http', 'https'])) {
            if ($scheme = $this->choice("Which protocol needs to be used for checking `{$url}`?", [1 => 'https', 2 => 'http'], 1)) {
                $url = $url->withScheme($scheme);
            }
        }

	    $isApiCall = $this->option('api');
	    $string = $this->option('string');
	    if( isset( $isApiCall ) && $isApiCall == true ) {
		    if ( isset( $string ) && $string != '' ) {
			    $lookForString = $string;
		    }

		    if ( in_array($url->getHost(), ['localhost', '127.0.0.1']) ) {
			    echo json_encode( array(
				    'status' => 401,
				    'message' => "Localhost is not allowed."
			    ), true );
			    return;
		    }

		    $validator = new EmailValidator();
		    $multipleValidations = new MultipleValidationWithAnd([
			    new RFCValidation(),
			    new DNSCheckValidation(),
			    new SpoofCheckValidation(),
		    ]);
		    if ( ! isset( $email ) || $email === '' || !$validator->isValid( $email, $multipleValidations ) ) {
			    echo json_encode( array(
				    'status' => 401,
				    'message' => "No email provided or invalid email address."
			    ), true );
			    return;
		    }
	    } else {
		    if ($this->confirm('Should we look for a specific string on the response?')) {
			    $lookForString = $this->ask('Which string?');
		    }
	    }

	    try {
		    $monitor = Monitor::where('url', $url)->where('email', '<>', trim( $email ))->first();
		    if ( $monitor ) {
			    $monitor = Monitor::where('url', $url)->update( [ 'email' => trim( $email ) ] );

			    if ( isset( $isApiCall ) && $isApiCall == true ) {
				    echo json_encode( array(
					    'status'  => 200,
					    'message' => "{$url} updated email to {$email}!"
				    ), true );

				    return;
			    }
			    $this->warn( "{$url} updated email to {$email}" );
		    } else {

			    $data = array('name'=>"Bogdan Test");
			    var_dump( Mail::send('emails_confirm', $data, function($message) {
				    $message->to('bogdan.preda@themeisle.com', 'Test mails')->subject
				    ('Laravel Basic Testing Mail');
				    $message->from('monitor@themeisle.com','Uptime Monitor');
			    }) );
			    $monitor = Monitor::firstOrCreate( [
				    'url'                              => trim( $url, '/' ),
				    'email'                            => trim( $email ),
				    'look_for_string'                  => $lookForString ?? '',
				    'uptime_check_method'              => isset( $lookForString ) ? 'get' : 'head',
				    'certificate_check_enabled'        => $url->getScheme() === 'https',
				    'uptime_check_interval_in_minutes' => config( 'uptime-monitor.uptime_check.run_interval_in_minutes' ),
			    ] );

			    if ( isset( $isApiCall ) && $isApiCall == true ) {
				    echo json_encode( array(
					    'status'  => 200,
					    'message' => "{$monitor->url} will be monitored!"
				    ), true );

				    return;
			    }
			    $this->warn( "{$monitor->url} will be monitored!" );
		    }
	    } catch ( \Exception $e ) {
		    echo json_encode( array(
			    'status' => 500,
			    'message' => $e->getMessage()
		    ), true );
		    return;
	    }
    }
}
