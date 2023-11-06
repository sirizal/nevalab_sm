<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPurchaseRequestNotification extends Notification
{
    use Queueable;

    private PurchaseRequest $pr;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $pr)
    {
        $this->pr = $pr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        switch ($this->pr->status) {
            case '1':
                return (new MailMessage)
                    ->subject('Request manager approve for ' . $this->pr->code)
                    ->line('Purchase Request baru telah di buat.')
                    ->line('Oleh : ' . $this->pr->createUser->name)
                    ->line('No PR : ' . $this->pr->code)
                    ->line('Request Delivery : ' . $this->pr->request_delivery_date)
                    ->line('Request Receive : ' . $this->pr->request_receive_date)
                    ->line('Request Amount : ' . number_format($this->pr->total_request_amount, 0, '.', ','))
                    ->line('Remark  : ' . $this->pr->remarks)
                    ->line('Silahkan klik link di bawah untuk melakukan approval')
                    ->action('Approval', route('filament.admin.resources.purchase-requests.view', $this->pr->id))
                    ->line('Terima kasih');
            case '2':
                return (new MailMessage)
                    ->subject('Request Cost Controller approve for ' . $this->pr->code)
                    ->line('Purchase Request telah di buat :')
                    ->line('Oleh : ' . $this->pr->createUser->name)
                    ->line('No PR : ' . $this->pr->code)
                    ->line('Request Delivery : ' . $this->pr->request_delivery_date)
                    ->line('Request Receive : ' . $this->pr->request_receive_date)
                    ->line('Request Amount : ' . number_format($this->pr->total_request_amount, 0, '.', ','))
                    ->line('Remark  : ' . $this->pr->remarks)
                    ->line('Manager approval : ' . $this->pr->managerUser->name)
                    ->line('Manager komen : ' . $this->pr->manager_comment)
                    ->line('Silahkan klik link di bawah untuk melakukan approval')
                    ->action('Approval', route('filament.admin.resources.purchase-requests.view', $this->pr->id))
                    ->line('Terima kasih');
            case '3':
                return (new MailMessage)
                    ->subject('Purchase request rejected for ' . $this->pr->code)
                    ->line('Purchase Request telah reject oleh :')
                    ->line('Oleh : ' . $this->pr->managerUser->name)
                    ->line('Manager komen : ' . $this->pr->manager_comment)
                    ->line('Terima kasih');
            case '4':
                return (new MailMessage)
                    ->subject('Request KAM approve for ' . $this->pr->code)
                    ->line('Purchase Request telah di buat :')
                    ->line('Oleh : ' . $this->pr->createUser->name)
                    ->line('No PR : ' . $this->pr->code)
                    ->line('Request Delivery : ' . $this->pr->request_delivery_date)
                    ->line('Request Receive : ' . $this->pr->request_receive_date)
                    ->line('Request Amount : ' . number_format($this->pr->total_request_amount, 0, '.', ','))
                    ->line('Remark  : ' . $this->pr->remarks)
                    ->line('Cost Controller approval : ' . $this->pr->costControllerUser->name)
                    ->line('Cost Controller komen : ' . $this->pr->cost_controller_comment)
                    ->line('Silahkan klik link di bawah untuk melakukan approval')
                    ->action('Approval', route('filament.admin.resources.purchase-requests.view', $this->pr->id))
                    ->line('Terima kasih');
            case '5':
                return (new MailMessage)
                    ->subject('Purchase request rejected for ' . $this->pr->code)
                    ->line('Purchase Request telah reject oleh :')
                    ->line('Oleh : ' . $this->pr->costControllerUser->name)
                    ->line('Manager komen : ' . $this->pr->cost_controller_comment)
                    ->line('Terima kasih');
            case '6':
                return (new MailMessage)
                    ->subject('Full approval for ' . $this->pr->code)
                    ->line('Request Delivery : ' . $this->pr->request_delivery_date)
                    ->line('Request Receive : ' . $this->pr->request_receive_date)
                    ->line('Request Amount : ' . number_format($this->pr->total_request_amount, 0, '.', ','))
                    ->line('Remark  : ' . $this->pr->remarks)
                    ->line('Silahkan klik link di bawah navigasi ke data nya')
                    ->action('Ready for PO', route('filament.admin.resources.purchase-requests.view', $this->pr->id))
                    ->line('Terima kasih');
            case '7':
                return (new MailMessage)
                    ->subject('Purchase request rejected for ' . $this->pr->code)
                    ->line('Purchase Request telah reject oleh :')
                    ->line('Oleh : ' . $this->pr->kamUser->name)
                    ->line('KAM komen : ' . $this->pr->kam_comment)
                    ->line('Terima kasih');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
