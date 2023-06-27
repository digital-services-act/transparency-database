<?php

namespace App\Servers;


use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class StatementWebSocketServer implements MessageComponentInterface
{

    public function onOpen(ConnectionInterface $connection)
    {
        // TODO: Implement onOpen() method.
    }

    public function onClose(ConnectionInterface $connection)
    {
        // TODO: Implement onClose() method.
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $msg)
    {
        $payload = $msg->getPayload();
        $out     = new \stdClass();
        $json    = null;

        try {
            $json = json_decode($payload);
        } catch (\Exception $e) {
            $out->status  = Response::HTTP_BAD_REQUEST;
            $out->message = 'Invalid JSON';
        }

        if ($json) {
            $token = $json->token ?? null;
            if ($token) {
                $tokenInstance = PersonalAccessToken::findToken($token);
                if (
                    $tokenInstance instanceof PersonalAccessToken &&
                    $tokenInstance->tokenable instanceof User
                ) {
                    $user      = $tokenInstance->tokenable;
                    if ($user->platform) {
                        $statement = $json->statement ?? null;
                        if ($statement && is_object($statement)) {
                            $statement = get_object_vars($statement);
                            $validator = Validator::make($statement, StatementStoreRequest::getRules());
                            if ($validator->errors()->count() === 0) {
                                try {
                                    $statement = $validator->validate();
                                    $statement['user_id'] = $user->id;
                                    $statement['platform_id'] = $user->platform->id;

                                    /** @var Statement $statement */
                                    $statement = Statement::create($statement);

                                    $out->status    = Response::HTTP_CREATED;
                                    $out->message   = 'Statement Created';
                                    $out->statement = $statement->toJson();

                                } catch(\Exception $e)
                                {
                                    $out->status    = Response::HTTP_BAD_REQUEST;
                                    $out->message   = $e->getMessage();
                                }
                            } else {
                                $out->status  = Response::HTTP_BAD_REQUEST;
                                $out->message = 'Statement Data Invalid';
                                $out->errors  = $validator->errors();
                            }
                        } else {
                            $out->status  = Response::HTTP_BAD_REQUEST;
                            $out->message = 'Statement Not Present';
                        }
                    } else {
                        $out->status    = Response::HTTP_BAD_REQUEST;
                        $out->message   = 'No platform associated with your account';
                    }
                } else {
                    $out->status  = Response::HTTP_FORBIDDEN;
                    $out->message = 'Invalid Token';
                }
            } else {
                $out->status  = Response::HTTP_BAD_REQUEST;
                $out->message = 'Token Not Present';
            }
        }

        $connection->send(json_encode($out));
    }
}