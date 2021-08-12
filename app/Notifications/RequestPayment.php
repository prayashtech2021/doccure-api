<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\PaymentRequest;
use App\User;

class RequestPayment extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PaymentRequest $request)
    {
        $this->request = $request;
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if($this->request->status==1){ //request
            $msg = 'Payment Request with reference #' . $this->request->reference_id . ' has been created!';
        }elseif($this->request->status==2){ // paid
            $msg = 'Your Requested payment #' . $this->request->reference_id . ' has paid successfully';
        }else{
            $msg = 'Your Requested payment #' . $this->request->reference_id . ' has been rejected';
        }
        return [
            'noty_type' => 'payment-requested',
            'request_id' => $this->request->id,
            'reference' => $this->request->reference_id,
            'message' => $msg,
            // 'open_link' => route('paymentRequestView',['id'=>$this->request->id])
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'noty_type' => 'payment-requested',
            'request_id' => $this->request->id,
            'reference' => $this->request->request_id,
            'message' => 'Payment Request with reference #' . $this->request->request_id . ' has been created!',
            // 'open_link' => route('paymentRequestView',['id'=>$this->request->id])
        ]);
    }
}
