<?php

namespace App\Console\Commands;

use App\Models\Task\User;
use Illuminate\Console\Command;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;

class TaskUsersConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-users-consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $consumer = Kafka::createConsumer()->subscribe('auth-user-streaming')
            ->withAutoCommit()
            ->withHandler(function(KafkaConsumerMessage $message) {
                $data = json_decode($message->getBody(), true);
                switch ($message->getKey()) {
                    case 'user_created':
                        $user = new User(
                            [
                                'id' => $data['id'],
                                'role' => $data['role'],
                                'created_at' => $data['created_at'],
                                'updated_at' => $data['updated_at'],
                            ]
                        );
                        $user->save();
                        break;
                    case 'user_updated':
                        $user = User::find($data['id']);
                        $user->role = $data['role'];
                        $user->created_at = $data['created_at'];
                        $user->updated_at = $data['updated_at'];
                        $user->save();
                        break;
                    case 'user_deleted':
                        $user = User::find($data['id']);
                        if ($user) {
                            $user->delete();
                        }
                        break;
                }
            })
            ->build();

        $consumer->consume();
    }
}
