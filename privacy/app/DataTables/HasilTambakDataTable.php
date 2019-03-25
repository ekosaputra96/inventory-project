<?php

namespace App\DataTables;

use App\HasilTambak;
use Yajra\DataTables\Services\DataTable;

class HasilTambakDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables($query)
            ->addColumn('action', function ($query){
                $action =
                    '<a href="'.$query->show_url.'" class="btn btn-info btn-sm"> <i class="fa fa-eye"></i> Lihat</a>'.'&nbsp'.
                    '<a href="'.$query->edit_url.'" class="btn btn-warning btn-sm"> <i class="fa fa-edit"></i> Edit</a>'.'&nbsp'.
                    '<a href="javascript:;" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" 
                    id="hapus" class="btn btn-danger btn-sm"> <i class="fa fa-times-circle"></i> Hapus</a>'.'&nbsp';

                return $action;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\HasilTambak $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(HasilTambak $model)
    {
        return $model->newQuery()->with('petambak')->select('id', 'petambak_id', 'created_at', 'updated_at');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'searchDelay' => 1400,
                "autoWidth" => false
            ])
            ->addAction(['width' => '200px']);
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
            'petambak.nama_petambak',
            'created_at',
            'updated_at'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'HasilTambak_' . date('YmdHis');
    }
}
