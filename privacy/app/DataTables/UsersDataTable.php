<?php

namespace App\DataTables;

use App\User;
use App\Models\Company;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {

            return datatables($query)->escapeColumns(['action'])->addColumn('action', function($query) {
                $action = '<a href="'.$query->edit_url.'" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Edit"> <i class="fa fa-edit"></i></a>'.'&nbsp'.
                    '<a href="javascript:;" data-toggle="tooltip" title="Hapus" onclick="del(\''.$query->id.'\',\''.$query->destroy_url.'\')" 
                    id="hapus" class="btn btn-danger btn-xs"> <i class="fa fa-times-circle"></i></a>'.'&nbsp';

                $action2 = '<a href="'.$query->edit_url.'" class="btn btn-warning btn-xs" data-toggle="tooltip" title="Edit"> <i class="fa fa-edit"></i></a>'.'&nbsp';

                $level = auth()->user()->level;
                if($level != 'superadministrator'){
                    return $action2;
                }
                else{
                    return $action;
                }
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $level = auth()->user()->level;
        $username = auth()->user()->username;
        if($level != 'superadministrator' && $level != 'user_tina'){
            return $model->newQuery()->with('roles','company')->select('id', 'name', 'username', 'email', 'level', 'kode_company', 'kode_lokasi' ,'level')->where('username',$username);
        }
        else if($level == 'user_tina'){
            return $model->newQuery()->with('roles','company')->select('id', 'name', 'username', 'email', 'level', 'kode_company', 'kode_lokasi' ,'level')->where('level','<>','superadministrator');
        }else{
            return $model->newQuery()->with('roles','company')->select('id', 'name', 'username', 'email', 'level', 'kode_company', 'kode_lokasi' ,'level');
        }
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
                    ->addAction(['width' => '150px']);
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
            'name',
            'username',
            'level',
            'kode_company',
            'kode_lokasi',
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Users_' . date('YmdHis');
    }
}
