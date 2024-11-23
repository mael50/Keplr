<?php

namespace App\Service;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct(WebPush $webPush)
    {
        $this->webPush = $webPush;
    }

    public function sendNotification(array $subscriptionData, string $title, string $body): void
    {
        $subscription = Subscription::create($subscriptionData);

        $this->webPush->sendOneNotification(
            $subscription,
            json_encode(['title' => $title, 'body' => $body])
        );

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                echo "[v] Message sent successfully to {$endpoint}.";
            } else {
                echo "[x] Message failed to {$endpoint}: {$report->getReason()}";
            }
        }
    }
}
