<?php

namespace App\DataTables;

use App\Models\Master\Truck;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TruckDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query);
            // ->addColumn('action', 'truck.action');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Truck $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Truck $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('truck-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->parameters(
                        [
                            'dom'          => 'Blfrtip',
                            'buttons'      => ['csv', 'excel', 'pdf', 'print', 'reset', 'reload'],
                        ]
                    )
                    ->orderBy(0, 'asc');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'id',
            'kode',
            'type',
            'merk'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Truck_' . date('YmdHis');
    }
}
