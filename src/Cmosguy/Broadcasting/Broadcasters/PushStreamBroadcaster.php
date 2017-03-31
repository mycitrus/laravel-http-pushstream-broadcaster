<?php

namespace Cmosguy\Broadcasting\Broadcasters;


use GuzzleHttp\Client;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Support\Str;
#use Illuminate\Contracts\Broadcasting\Broadcaster;

class PushStreamBroadcaster extends Broadcaster
{
    /**
     * @var Client
     */
    private $client;

    /**
     * PushStreamBroadcaster constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    /**
     * Broadcast the given event.
     *
     * @param  array $channels
     * @param  string $event
     * @param  array $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = array())
    {

        foreach ($channels as $channel) {
            $payload = [
                'text' => array_merge(['eventtype' => $event], $payload)
            ];
            $response = $this->client->request('POST', '/pub?id=' . $channel, ['json' => $payload]);
        }
    }
    
    
    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function auth($request)
    {
        if (Str::startsWith($request->channel_name, ['private-', 'presence-']) &&
            ! $request->user()) {
                throw new HttpException(403);
            }
    
            $channelName = Str::startsWith($request->channel_name, 'private-')
            ? Str::replaceFirst('private-', '', $request->channel_name)
            : Str::replaceFirst('presence-', '', $request->channel_name);
    
            return parent::verifyUserCanAccessChannel(
                $request, $channelName
                );
    }
    
    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        return true;
    }
}
