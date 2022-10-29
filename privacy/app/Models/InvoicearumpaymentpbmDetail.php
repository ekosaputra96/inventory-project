<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yajra\Auditable\AuditableTrait;
use App\Models\Cashbankin;
use App\Models\Coa;
use Carbon;
use DB;


class InvoicearumpaymentpbmDetail extends Model
{
    //
    use AuditableTrait;

    protected $table = 'invoicearumpaymentpbm_detail';

	public $incrementing = true;

	protected $fillable = [
        'no_invoice',
    	'tgl_payment',
        'tipe_payment',
        'kode_jurnal',
        'kode_cashbank',
        'no_ref',
        'due_date',
        'total_payment',
        'no_journal',
        'status',
        'id',
    ];

    protected $appends = ['destroy_url','edit_url'];

    public function Cashbank()
    {
        return $this->belongsTo(Cashbank::class,'kode_cashbank');
    }  

    public function Jurnal()
    {
        return $this->belongsTo(Jurnal::class,'kode_jurnal');
    }  

    public function Coa()
    {
        return $this->belongsTo(Coa::class,'kode_coa');
    }

    public function getDestroyUrlAttribute()
    {
        return route('invoicearumpbmdetail.destroy', $this->id);
    }

    public function getEditUrlAttribute()
    {
        return route('invoicearumpbmdetail.edit',$this->id);
    }

    public function getUpdateUrlAttribute()
    {
        return route('invoicearumpbmdetail.update',$this->id);
    }

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

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($query){
            $query->status = 'OPEN';
            $query->kode_company = Auth()->user()->kode_company;
            // $query->no_journal = static::getjurnal(request()->kode_jurnal, request()->tgl_payment);
            $query->created_by = Auth()->user()->name;
            $query->updated_by = Auth()->user()->name;
        });

        static::updating(function ($query){
           $query->updated_by = Auth()->user()->name;
        });
    }

    public static function getjurnal($kode_jurnal, $tgl_jalan2)
    {
        $konek = static::konek();
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        // $tgl_jalan2 = $tgl_jalan->periode;

        $kode_company = auth()->user()->kode_company;
        $tahun = Carbon\Carbon::parse($tgl_jalan2)->format('y');
        $bulan = Carbon\Carbon::parse($tgl_jalan2)->format('m');

        $jurnal1 = $kode_jurnal.'.'.$kode_company.$tahun.'.'.$bulan.'.';
        
        $cek_jurnal = InvoicearpaymentsubDetail::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal != null ) {
            $leng = substr($cek_jurnal->no_journal,12,4);
        }else{
            $leng = 0;
        }

        $cek_jurnal3 = Cashbankin::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal3 != null ) {
            $leng3 = substr($cek_jurnal3->no_journal,12,4);
        }else{
            $leng3 = 0;
        }

        $cek_jurnal4 = Cashbankout::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal4 != null ) {
            $leng4 = substr($cek_jurnal4->no_journal,12,4);
        }else{
            $leng4 = 0;
        }

        $cek_jurnal5 = CashbankTransfer::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal5 != null ) {
            $leng5 = substr($cek_jurnal5->no_journal,12,4);
        }else{
            $leng5 = 0;
        }

        $cek_jurnal6 = InvoiceappaymentDetail::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal6 != null ) {
            $leng6 = substr($cek_jurnal6->no_journal,12,4);
        }else{
            $leng6 = 0;
        }

        $cek_jurnal7 = Debitnote::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal7 != null ) {
            $leng7 = substr($cek_jurnal7->no_journal,12,4);
        }else{
            $leng7 = 0;
        }

        $cek_jurnal8 = Invoicearumsub::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal8 != null ) {
            $leng8 = substr($cek_jurnal8->no_journal,12,4);
        }else{
            $leng8 = 0;
        }

        $cek_jurnal9 = Invoicearsub::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal9 != null ) {
            $leng9 = substr($cek_jurnal9->no_journal,12,4);
        }else{
            $leng9 = 0;
        }

        $cek_jurnal12 = DebitNotePayment::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal12 != null ) {
            $leng12 = substr($cek_jurnal12->no_journal,12,4);
        }else{
            $leng12 = 0;
        }

        $cek_jurnal13 = Invoiceap::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal13 != null ) {
            $leng13 = substr($cek_jurnal13->no_journal,12,4);
        }else{
            $leng13 = 0;
        }

        $cek_jurnal2 = self::on($konek)->where(DB::raw('LEFT(no_journal,12)'),$jurnal1)->orderBy('created_at','desc')->first();
        if ($cek_jurnal2 != null ) {
            $leng2 = substr($cek_jurnal2->no_journal,12,4);
        }else{
            $leng2 = 0;
        }
        
        $lenger = max($leng, $leng2, $leng3, $leng4, $leng5, $leng6, $leng7, $leng8, $leng9, $leng12, $leng13);

        $hasil = $jurnal1.sprintf('%04d', intval($lenger) + 1);
          
        return $hasil;
    }
}
