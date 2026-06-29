<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Mail\GeneralMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailSettingsController extends Controller
{
    public function smtpSettings(Request $request){
        if ($request->isMethod('POST')) {
            $fields = [
                'com_site_global_email',
                'com_site_smtp_mail_mailer',
                'com_site_smtp_mail_host',
                'com_site_smtp_mail_post',
                'com_site_smtp_mail_username',
                'com_site_smtp_mail_password',
                'com_site_smtp_mail_encryption',
            ];
            foreach ($fields as $field) {
                $value = $request->input($field) ?? null;
                com_option_update($field, $value);
            }
            updateEnvValues([
                'MAIL_DRIVER' => $request->com_site_smtp_mail_mailer,
                'MAIL_HOST' => $request->com_site_smtp_mail_host,
                'MAIL_PORT' => $request->com_site_smtp_mail_post,
                'MAIL_USERNAME' => $request->com_site_smtp_mail_username,
                'MAIL_PASSWORD' => $request->com_site_smtp_mail_password,
                'MAIL_ENCRYPTION' => $request->com_site_smtp_mail_encryption,
            ]);
            return $this->success(translate('messages.update_success', ['name' => 'SMTP Settings']));
        }else{
            $fields = [
                'com_site_global_email',
                'com_site_smtp_mail_mailer',
                'com_site_smtp_mail_host',
                'com_site_smtp_mail_post',
                'com_site_smtp_mail_username',
                'com_site_smtp_mail_password',
                'com_site_smtp_mail_encryption',
            ];

            $data = [];
            $demoMode = Config::get('demoMode.check');
            foreach ($fields as $field) {
                $value = com_option_get($field);
                // Mask only username and password if demo mode is ON
                if ($demoMode && in_array($field, ['com_site_smtp_mail_username', 'com_site_smtp_mail_password'])) {
                    $value = '';
                }
                $data[$field] = $value;
            }

            return $this->success($data);
        }

    }

    public function testMailSend(Request $request){
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $recipient = $request->input('test_email');

        try {
            Mail::to($recipient)->send(new GeneralMail([
                'subject' => __('Test Mail'),
                'body' => $recipient,
            ]));
            return response()->json([
                'status' => 'success',
                'message' => translate('messages.test_email_sent_success', ['name' => 'Test Mail']),
                'data' => [
                    'recipient' => $recipient
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => translate('messages.test_email_sent_failed', ['name' => 'Test Mail']),
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
