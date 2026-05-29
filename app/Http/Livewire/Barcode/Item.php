<?php

namespace App\Http\Livewire\Barcode;

use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class Item extends Component
{

    public $items = [];

    protected $listeners = [
        'add-barcode' => 'add'
    ];

    public function add(array $data): Void
    {
        $barcode = $data['barcode'];

        if ($this->exists($barcode)) {
            $this->increment($barcode);
        } else {
            $this->store($data);
        }
    }

    public function store(array $data): Void
    {
        $data['jumlah'] = 1;

        array_push($this->items, $data);
    }

    public function update($barcode, int $qty): Void
    {
        $index = $this->search($barcode);

        $this->items[$index]['jumlah'] = $qty;
    }

    public function increment($barcode): Void
    {
        $index = $this->search($barcode);

        $this->items[$index]['jumlah']++;
    }

    public function remove(int $index): Void
    {
        $this->items = collect($this->items)->forget($index)->values()->all();
    }

    public function clear(): Void
    {
        $this->items = [];
    }

    public function print(): Void
    {
        $items = json_encode($this->items);

        Cookie::queue(Cookie::make('barcode', $items, 2800));

        redirect()->route('barcode.print');
    }

    public function exists($barcode): Bool
    {
        return collect($this->items)->contains(function ($item) use ($barcode)
        {
            return $item['barcode'] === (string)$barcode;
        });
    }

    public function search($barcode): Int
    {
        return collect($this->items)->search(function ($item) use ($barcode)
        {
            return $item['barcode'] === (string)$barcode;
        });
    }

    public function mount()
    {
        Cookie::queue(Cookie::forget('barcode'));
    }

    public function render()
    {
        return view('livewire.barcode.item');
    }
}
