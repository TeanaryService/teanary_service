<?php

namespace App\Livewire\Payment;

use App\Models\Order;
use Livewire\Component;

class Success extends Component
{
    public ?Order $order = null;

    public function mount(?int $orderId = null)
    {
        if ($orderId) {
            $this->order = Order::findOrFail($orderId);
        }
    }

    public function render()
    {
        return view('livewire.payment.success');
    }
}
