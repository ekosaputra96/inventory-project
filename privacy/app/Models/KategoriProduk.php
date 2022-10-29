<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Produk;

class KategoriProduk extends Model
{
    //
    use AuditableTrait;
    
    protected $connection = 'mysql2';

    protected $table = 'kategori_produk';

    protected $primaryKey = 'kode_kategori';

    public $incrementing = false;

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'status',
        'coa_gut',
        'coa_emkl',
        'coa_pbm',
        'coa_infra',
        'coa_sub',
        'coa_depo',
        'coa_gutjkt',
        'coabiaya_infra',
        'coabiaya_gut',
        'coabiaya_emkl',
        'coabiaya_pbm',
        'coabiaya_sub',
        'coabiaya_depo',
        'coabiaya_gutjkt',
        'cc_gut',
        'cc_gutjkt',
        'cc_emkl',
        'cc_pbm',
        'cc_infra',
        'cc_depo',
        'cc_sub',
        'cc_gut_persediaan',
        'cc_gutjkt_persediaan',
        'cc_emkl_persediaan',
        'cc_pbm_persediaan',
        'cc_infra_persediaan',
        'cc_depo_persediaan',
        'cc_sub_persediaan',
    ];

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'coa_gut');
    }

    public function Coa1()
    {
        return $this->belongsTo(Coa::class,'coa_emkl');
    }

    public function Coa2()
    {
        return $this->belongsTo(Coa::class,'coa_pbm');
    }

    public function Coa3()
    {
        return $this->belongsTo(Coa::class,'coa_depo');
    }

    public function Coa4()
    {
        return $this->belongsTo(Coa::class,'coa_sub');
    }

    public function Coa5()
    {
        return $this->belongsTo(Coa::class,'coa_gutjkt');
    }


    public function Coa6()
    {
        return $this->belongsTo(Coa::class,'coabiaya_gut');
    }

    public function Coa7()
    {
        return $this->belongsTo(Coa::class,'coabiaya_emkl');
    }

    public function Coa8()
    {
        return $this->belongsTo(Coa::class,'coabiaya_pbm');
    }

    public function Coa9()
    {
        return $this->belongsTo(Coa::class,'coabiaya_depo');
    }

    public function Coa10()
    {
        return $this->belongsTo(Coa::class,'coabiaya_sub');
    }

    public function Coa11()
    {
        return $this->belongsTo(Coa::class,'coabiaya_gutjkt');
    }
    
    public function Coa12()
    {
        return $this->belongsTo(Coa::class,'coa_infra');
    }
    
    public function Coa13()
    {
        return $this->belongsTo(Coa::class,'coabiaya_infra');
    }
    
    public function cost_gut()
    {
        return $this->belongsTo(Costcenter::class,'cc_gut');
    }
    
    public function cost_gutjkt()
    {
        return $this->belongsTo(Costcenter::class,'cc_gutjkt');
    }
    
    public function cost_emkl()
    {
        return $this->belongsTo(Costcenter::class,'cc_emkl');
    }
    
    public function cost_pbm()
    {
        return $this->belongsTo(Costcenter::class,'cc_pbm');
    }
    
    public function cost_infra()
    {
        return $this->belongsTo(Costcenter::class,'cc_infra');
    }
    
    public function cost_sub()
    {
        return $this->belongsTo(Costcenter::class,'cc_sub');
    }
    
    public function cost_depo()
    {
        return $this->belongsTo(Costcenter::class,'cc_depo');
    }
    
    public function cost_gut_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_gut_persediaan');
    }
    
    public function cost_gutjkt_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_gutjkt_persediaan');
    }
    
    public function cost_emkl_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_emkl_persediaan');
    }
    
    public function cost_pbm_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_pbm_persediaan');
    }
    
    public function cost_infra_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_infra_persediaan');
    }
    
    public function cost_sub_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_sub_persediaan');
    }
    
    public function cost_depo_persediaan()
    {
        return $this->belongsTo(Costcenter::class,'cc_depo_persediaan');
    }

    public function getDestroyUrlAttribute()
    {
        return route('kategoriproduk.destroy', $this->kode_kategori);
    }

    public function getEditUrlAttribute()
    {
        return route('kategoriproduk.edit',$this->kode_kategori);
    }

    public function getUpdateUrlAttribute()
    {
        return route('kategoriproduk.update',$this->kode_kategori);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->kode_company = Auth()->user()->kode_company;
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }
}
