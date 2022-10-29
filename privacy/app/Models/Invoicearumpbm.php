<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use Carbon;
use DB;

class Invoicearumpbm extends Model
{
    //
    use AuditableTrait;

    protected $table = 'invoice_arumpbm';

    protected $primaryKey = 'no_invoice';

    public $incrementing = false;

    protected $fillable = [
        'no_invoice',
        'tgl_invoice',
        'type_ar',
        'no_seri',
        'kode_customer',
        'no_kode_pajak',
        'no_jo',
        'no_bl',
        'top',
        'due_date',
        'remark',
        'total_item',
        'subtotal_invoice',
        'disc_persen',
        'disc_rp',
        'total_invoice',
        'total_administrasi',
        'ppn_persen',
        'ppn_rp',
        'biaya_lain',
        'grand_total',
        'total_payment',
        'total_creditnote',
        'kode_jurnal',
        'no_journal',
        'status',
    ];

    public static function konek()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '0401'){
            $koneksi = 'mysqlgutjkt';
        }else if ($compa == '03'){
            $koneksi = 'mysql';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysqlsub';
        }
        return $koneksi;
    }

    public function Customer()
    {
        return $this->belongsTo(Customersub::class,'kode_customer');
    }

    public function Company()
    {
        return $this->belongsTo(Company::class,'kode_company');
    }

    public function Jurnal()
    {
        return $this->belongsTo(Jurnal::class,'kode_jurnal');
    }

    public function getDestroyUrlAttribute()
    {
        return route('invoicearumpbm.destroy', $this->no_invoice);
    }

    public function getEditUrlAttribute()
    {
        return route('invoicearumpbm.edit',$this->no_invoice);
    }

    public function getUpdateUrlAttribute()
    {
        return route('invoicearumpbm.update',$this->no_invoice);
    }

    public function getDetailUrlAttribute()
    {
        return route('invoicearumpbm.detail',$this->no_invoice);
    }

    public function getShowUrlAttribute()
    {
        return route('invoicearumpbm.show',$this->no_invoice);
    }

    public function getCetakUrlAttribute()
    {
        return route('invoicearumpbm.cetak',$this->no_invoice);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->kode_jurnal = '050';
            $query->kode_company = Auth()->user()->kode_company;
            $query->kode_lokasi = Auth()->user()->kode_lokasi;
            $query->no_invoice = static::generateKode(request()->tgl_invoice);
            $query->no_journal = static::getjurnal(request()->tgl_invoice);
            // if(request()->ppn_persen > 0 || request()->ppn_rp > 0){
            //     $query->no_seri = static::getseri(request()->kode_customer, request()->no_kode_pajak);
            // }else{
            //     $query->no_seri = null;
            // }
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function getjurnal($data)
    {
        $konek = static::konek();
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $data;

        $kode_company = auth()->user()->kode_company;
        $tahun = Carbon\Carbon::parse($tgl_jalan2)->format('y');
        $bulan = Carbon\Carbon::parse($tgl_jalan2)->format('m');

        $jurnal1 = '050'.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
        $cek_jurnal = Invoicearpbm::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal != null ) {
            $leng = substr($cek_jurnal->no_journal,12,4);
        }else{
           $leng = 0;
        }

        $cek_jurnal2 = self::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal2 != null ) {
            $leng2 = substr($cek_jurnal2->no_journal,12,4);
        }else{
           $leng2 = 0;
        }
        
        $cek_jurnal3 = InvoicearpaymentpbmDetail::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal3 != null ) {
            $leng3 = substr($cek_jurnal3->no_journal,12,4);
        }else{
           $leng3 = 0;
        }

        $cek_jurnal4 = InvoicearumpaymentpbmDetail::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal4 != null ) {
            $leng4 = substr($cek_jurnal4->no_journal,12,4);
        }else{
           $leng4 = 0;
        }

        $cek_jurnal7 = DebitNotePayment::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal7 != null ) {
            $leng7 = substr($cek_jurnal7->no_journal,12,4);
        }else{
            $leng7 = 0;
        }
        
        $lenger = max($leng, $leng2, $leng3, $leng4, $leng7);
        $hasil = $jurnal1.sprintf('%04d', intval($lenger) + 1);
        return $hasil;
    }

    public static function getseri($kode_customer, $no_kode_pajak)
    {
        $konek = static::konek();
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;

        $kode_company = auth()->user()->kode_company;
        $tahun = Carbon\Carbon::parse($tgl_jalan2)->format('Y');
        $bulan = Carbon\Carbon::parse($tgl_jalan2)->format('m');

        $get_nopajak = Customerpbm::find($kode_customer);
        $no_pajak = $no_kode_pajak;
        
        $get_seri_awal = Nomorserifaktur::on($konek)->whereYear('tgl_dapat',$tahun)->where('sisa','>',0)->first();
        $seri = substr($get_seri_awal->no_seri_faktur_dari,0,7);

        $cek_seri = self::on($konek)->where('no_seri','<>',null)->where(DB::raw('MID(no_seri,5,7)'),$seri)->orderBy('created_at','desc')->first();
        if ($cek_seri != null ) {
            $cek = self::on($konek)->where('no_seri','<>',null)->orderBy('created_at','desc')->first();
            $nsf = substr($cek->no_seri,11,9);
        }else{
            $nsf = 0;
        }

        $cek_seri1 = Invoicearpbm::on($konek)->where('no_seri','<>',null)->where(DB::raw('MID(no_seri,5,7)'),$seri)->orderBy('created_at','desc')->first();
        if ($cek_seri1 != null ) {
            $cek = Invoicearpbm::on($konek)->where('no_seri','<>',null)->orderBy('created_at','desc')->first();
            $nsf1 = substr($cek->no_seri,11,9);
        }else{
            $nsf1 = 0;
        }

        $lenger = max($nsf, $nsf1);
        if($lenger == 0){
            $hasil = $no_pajak.'.'.$get_seri_awal->no_seri_faktur_dari;
        }else{
            $hasil = $no_pajak.'.'.$seri.($lenger + 1);
        }

        return $hasil;
    }

    public static function generateKode($data)
    {
        $konek = static::konek();
        $user = Auth()->user()->name;
        $getkode = TransaksiSetup::where('kode_setup','008')->first();
        $kode = $getkode->kode_transaksi;

        $primary_key = (new self)->getKeyName();
        $get_prefix_1 = Auth()->user()->kode_company;
        $get_prefix_2 = strtoupper($kode);

        $period = Carbon\Carbon::parse($data)->format('ym');

        $get_prefix_3 = $period;
        $prefix_result = $get_prefix_1.$get_prefix_2.$get_prefix_3;
        $prefix_result_length = strlen($get_prefix_1.$get_prefix_2.$get_prefix_3);

        $lastRecort = self::on($konek)->where($primary_key,'like',$prefix_result.'%')->orderBy('created_at', 'desc')->first();

        if ( ! $lastRecort )
            $number = 0;
        else {
            $get_record_prefix = strtoupper(substr($lastRecort->{$primary_key}, 0,$prefix_result_length));
            if ($get_record_prefix == $prefix_result){
                $number = substr($lastRecort->{$primary_key},$prefix_result_length);
            }else {
                $number = 0;
            }

        }

        $result_number = $prefix_result . sprintf('%05d', intval($number) + 1);
        return $result_number ;
    }
}
