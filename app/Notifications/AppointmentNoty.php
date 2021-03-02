<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\PaymentRequest;
use App\User;

class AppointmentNoty extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(AppointmentNoty $request)
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
        return [
            'noty_type' => 'appointment-scheduled',
            //'request_id' => $this->request->id,
            'reference' => $this->request->appointment_reference,
            'message' => 'Appointment is Scheduled with reference #' . $this->request->appointment_reference . '!',
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
            'noty_type' => 'appointment-scheduled',
            //'request_id' => $this->request->id,
            'reference' => $this->request->appointment_reference,
            'message' => 'Appointment is Scheduled with reference #' . $this->request->appointment_reference . '!',
            // 'open_link' => route('paymentRequestView',['id'=>$this->request->id])
        ]);
    }
}
