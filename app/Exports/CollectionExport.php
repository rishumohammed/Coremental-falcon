<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class CollectionExport implements FromCollection
{

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->collection;   
    }
}
