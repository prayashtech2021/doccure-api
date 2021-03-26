<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Appointment;
use App\User;
use Illuminate\Support\Carbon;


class AppointmentNoty extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Appointment $request)
    {
        $this->request = $request;
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
        if($this->request->appointment_status==1){
            $message = 'Appointment is Scheduled on '.Carbon::parse($this->request->appointment_date)->format('d/m/Y').' at '.Carbon::parse($this->request->start_time)->format('h:i A').' to '.Carbon::parse($this->request->end_time)->format('h:i A').' reference #' . $this->request->appointment_reference . '!';
        }else{
            $message = config('custom.appointment_log_message.'.$this->request->appointment_status).' with reference to #' . $this->request->appointment_reference . '!';
        }
        return [
            'noty_type' => config('custom.appointment_log_message.'.$this->request->appointment_status),
            'reference' => $this->request->appointment_reference,
            'message' => $message,
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
        if($this->request->appointment_status==1){
            $message = 'Appointment is Scheduled on '.convertToLocal(Carbon::parse($this->request->appointment_date),'','d/m/Y').' at '.Carbon::parse($this->request->start_time)->format('h:i A').' to '.Carbon::parse($this->request->start_time)->format('h:i A').' reference #' . $this->request->appointment_reference . '!';
        }else{
            $message = config('custom.appointment_log_message.'.$this->request->appointment_status).' with reference to #' . $this->request->appointment_reference . '!';
        }
        return new BroadcastMessage([
            'noty_type' => config('custom.appointment_log_message.'.$this->request->appointment_status),
            'reference' => $this->request->appointment_reference,
            'message' => $message,
        ]);
    }
}
