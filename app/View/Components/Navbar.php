<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Repositories\StuffRepository;

class Navbar extends Component
{

    public $limit = 0;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(StuffRepository $stuffRepo)
    {
        $this->limit = $stuffRepo->countLimit();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.navbar');
    }
}
