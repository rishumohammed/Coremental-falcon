<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ViewExport implements FromView
{
    protected $collection;
    protected $view;
    protected $extraData;

    public function __construct($collection, $view, $extraData = [])
    {
        $this->collection = $collection;
        $this->view = $view;
        $this->extraData = $extraData;
    }

    public function view(): View
    {
        return view($this->view, array_merge([
            'rows' => $this->collection
        ], $this->extraData));
    }
}
