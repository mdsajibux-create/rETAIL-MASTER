<?php

namespace App\Repositories;

use App\Interfaces\SubscriberInterface;
use App\Jobs\SendDynamicEmailJob;
use App\Mail\SubscribedMail;
use App\Mail\UnsubscribedMail;
use App\Models\Subscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\SystemCore\app\Models\EmailTemplate;

class SubscriberRepository implements SubscriberInterface
{
    public function __construct(protected Subscriber $subscriber)
    {

    }

    public function subscribe(array $data)
    {
        $subscriber = Subscriber::updateOrCreate(
            ['email' => $data['email']],
            [
                'is_subscribed' => true,
                'unsubscribed_at' => null,
            ]
        );

        $message = __('Thank you for subscribing!');

        try {
            Mail::to($subscriber->email)->send(new SubscribedMail($subscriber, $message));
        } catch (\Exception $e) {
        }

        return $subscriber;
    }

    public function unsubscribe(string $email)
    {
        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber && $subscriber->is_subscribed) {
            $subscriber->update([
                'is_subscribed' => false,
                'unsubscribed_at' => Carbon::now(),
            ]);
            try {
                Mail::to($subscriber->email)->send(new UnsubscribedMail($subscriber));
            } catch (\Exception $e) {
            }
            return $subscriber;
        }

        return null;
    }

    public function getSubscribers(array $filters)
    {
        $subscribers = Subscriber::query();

        if (isset($filters['status'])) {
            $subscribers->where('is_subscribed', $filters['status']);
        }
        if (isset($filters['created_at'])) {
            $subscribers->where('created_at', $filters['subscribed_at']);
        }

        if (isset($filters['email'])) {
            $subscribers->where('email', 'like', "%{$filters['email']}%");
        }

        $subscribersList = $subscribers->orderBy('created_at', $filters['sortOrder'] ?? 'desc')->paginate($filters['per_page'] ?? 10);

        return $subscribersList;
    }

    public function changeStatus(array $data)
    {
        // Retrieve subscribers whose current status doesn't match the requested status
        $subscribersToUpdate = Subscriber::whereIn('id', $data['ids'])
            ->where('is_subscribed', '!=', $data['status'])
            ->get();

        // If no subscribers need updating, return early
        if ($subscribersToUpdate->isEmpty()) {
            return false;
        }

        // Update the status of these subscribers
        Subscriber::whereIn('id', $subscribersToUpdate->pluck('id'))
            ->update(['is_subscribed' => $data['status'], 'unsubscribed_at' => $data['status'] == 0 ? now() : null]);

        $web_site_name = com_option_get('com_site_title');

        // Send appropriate email notifications
        foreach ($subscribersToUpdate as $subscriber) {
            if ($data['status'] == 0) {
                try {
                    $email_template = EmailTemplate::where('type', 'unsubscribe')->where('status', 1)->first();
                    if ($email_template) {
                        $subject = $email_template->subject;
                        $message = str_replace(
                            ["@website_name", "@website_name"],
                            [$web_site_name,$web_site_name],
                            $email_template->body
                        );

                        dispatch(new SendDynamicEmailJob($subscriber->email, $subject, $message));
                    }
                } catch (\Exception $ex) { }
            } else {
                try {
                    $email_template = EmailTemplate::where('type', 'subscribers-bulk-mail')->where('status', 1)->first();
                    if ($email_template) {
                        $subject = $email_template->subject;
                        $message = str_replace(
                            ["@website_name", "@website_name"],
                            [$web_site_name,$web_site_name],
                            $email_template->body
                        );

                        dispatch(new SendDynamicEmailJob($subscriber->email, $subject, $message));
                    }
                } catch (\Exception $ex) { }
            }
        }

        return true;
    }

    public function sendBulkMail(array $data)
    {
        $subscribers = Subscriber::whereIn('id', $data['ids'])->get();

        foreach ($subscribers as $subscriber) {
            try {
                $web_site_name = com_option_get('com_site_title');
                $email_template = EmailTemplate::where('type', 'subscribers-bulk-mail')->where('status', 1)->first();
                if ($email_template) {
                    $subject = $email_template->subject;
                    $message = str_replace(
                        ["@website_name", "@website_name"],
                        [$web_site_name,$web_site_name],
                        $email_template->body
                    );

                    dispatch(new SendDynamicEmailJob($subscriber->email, $subject, $message));
                }
            } catch (\Exception $ex) { }
        }
        return true;
    }

    public function delete(int|string $id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
