<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    
  public $message;
  public $lol = 'haha';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
  
    /**
      * Get the channels the event should broadcast on.
      *
      * @author Author
      *
      * @return Channel|array
      */
    public function broadcastOn()
    {
        return new Channel('chatbox');
    }
    public function broadcastAs()

    {

        return 'UserEvent';

    }
    /**
      * Get the data to broadcast.
      *
      * @author Author
      *
      * @return array
      */
    public function broadcastWith()
    {
        return ['msg'=>$this->message];
    }
  
  

    

}
