<?php

// app/Controllers/UnitKerjaController.php

namespace App\Controllers;

use App\Models\UnitKerjaEs1Model;
use App\Models\UnitKerjaEs2Model;
use App\Models\UnitKerjaEs3Model;

class UnitKerja extends BaseController
{
    protected $es1Model;
    protected $es2Model;
    protected $es3Model;

    public function __construct()
    {
        $this->es1Model = new UnitKerjaEs1Model();
        $this->es2Model = new UnitKerjaEs2Model();
        $this->es3Model = new UnitKerjaEs3Model();
    }

    public function index()
    {
        $data['eselon1'] = $this->es1Model->findAll();
        $data['eselon2'] = $this->es2Model->findAll();
        $data['eselon3'] = $this->es3Model->findAll();
        $data['title'] = 'unit kerja';
        return view('unit_kerja/treeview_index', $data);
    }
}
