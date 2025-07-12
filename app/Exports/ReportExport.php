<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ReportExport implements FromView
{
    protected $data;
    protected $view;

    public function __construct(array $data, string $view)
    {
        $this->data = $data;
        $this->view = $view;
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }
}