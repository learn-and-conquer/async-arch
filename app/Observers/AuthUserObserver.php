<?php

namespace App\Observers;

use App\Models\Auth\User;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
class AuthUserObserver
{
    public function created(User $user): void
    {
        $message = new Message(
            body: ['user_created' => json_encode([
                'id' => $user->id,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ])],
        );

        $producer = Kafka::publishOn('auth-user-streaming')->withMessage($message);
        $producer->send();
    }

    public function updated(User $user): void
    {
        $message = new Message(
            body: ['user_updated' => json_encode([
                'id' => $user->id,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ])],
        );

        $producer = Kafka::publishOn('auth-user-streaming')->withMessage($message);
        $producer->send();
    }

    public function deleted(User $user): void
    {
        $message = new Message(
            body: ['user_deleted' => json_encode([
                'id' => $user->id,
            ])],
        );

        $producer = Kafka::publishOn('auth-user-streaming')->withMessage($message);
        $producer->send();
    }
}
