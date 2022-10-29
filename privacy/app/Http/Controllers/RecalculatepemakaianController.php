<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaian;
use App\Models\PemakaianDetail;
use App\Models\Produk;
use App\Models\Konversi;
use App\Models\satuan;
use App\Models\Company;
use App\Models\KategoriProduk;
use App\Models\Merek;
use App\Models\Ukuran;
use App\Models\Mobil;
use App\Models\JenisMobil;
use App\Models\Alat;
use App\Models\Kapal;
use App\Models\tb_akhir_bulan;
use App\Models\tb_item_bulanan;
use App\Models\tb_produk_history;
use App\Models\user_history;
use App\Models\MasterLokasi;
use App\Models\Ledger;
use App\Models\Coa;
use App\Models\AccBalance;
use App\Models\Tb_acc_history;
use App\Models\Jurnal;
use App\Models\Labarugiberjalan;
use PDF;
use Excel;
use DB;
use Carbon;

class RecalculatepemakaianController extends Controller
{
    public function konek()
    {
        $compa2 = auth()->user()->kode_company;
        $compa = substr($compa2,0,2);
        if ($compa == '01'){
            $koneksi = 'mysqldepo';
        }else if ($compa == '02'){
            $koneksi = 'mysqlpbm';
        }else if ($compa == '99'){
            $koneksi = 'mysqlpbmlama';
        }else if ($compa == '03'){
            $koneksi = 'mysqlemkl';
        }else if ($compa == '22'){
            $koneksi = 'mysqlskt';
        }else if ($compa == '04'){
            $koneksi = 'mysqlgut';
        }else if ($compa == '05'){
            $koneksi = 'mysql';
        }else if ($compa == '06'){
            $koneksi = 'mysqlinfra';
        }
        return $koneksi;
    }


    public static function konek2()
    {
        $compa = auth()->user()->kode_company;
        if ($compa == '01'){
            $koneksi = 'mysql_finance_depo';
        }else if ($compa == '02'){
            $koneksi = 'mysql_finance_pbm';
        }else if ($compa == '03'){
            $koneksi = 'mysql_finance_emkl';
        }else if ($compa == '04'){
            $koneksi = 'mysql_finance_gut';
        }else if ($compa == '0401'){
            $koneksi = 'mysql_finance_gutjkt';
        }else if ($compa == '05'){
            $koneksi = 'mysql_finance_sub';
        }
        return $koneksi;
    }

    public function index()
    {
        $konek = self::konek();

        $create_url = route('recalculatepemakaian.create');
        $tanggal = tb_akhir_bulan::on($konek)->where('status_periode','Open')->pluck('periode','periode');
        $periode = tb_akhir_bulan::on($konek)->pluck('periode','periode');

        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;

        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        
        if($get_company != null){
            $nama_company = $get_company->nama_company;

            return view('admin.recalculatepemakaian.index',compact('create_url','tanggal','period', 'nama_lokasi','periode','nama_company', 'nama_lokasi'));
        }else{
            $nama_company = 'GUI GROUP';

            return view('admin.recalculatepemakaian.index',compact('create_url','tanggal','period', 'nama_lokasi','periode','nama_company', 'nama_lokasi'));
        }
    }

    public function lrb_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        //UPDATE LABA RUGI BERJALAN
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
            if($cek_lrb != null){
                $begin_awal = $cek_lrb->beginning_balance;
                $debit_awal = $cek_lrb->debit;
                $kredit_awal = $cek_lrb->kredit;

                if($dbkr == 'D'){
                    $debit_akhir = $debit_awal + $harga;
                    $kredit_akhir = $kredit_awal;
                    $end = $begin_awal - $debit_akhir + $kredit_awal;
                }else{
                    $debit_akhir = $debit_awal;
                    $kredit_akhir = $kredit_awal + $harga;
                    $end = $begin_awal - $debit_awal + $kredit_akhir;
                }
                       
                $update_saldo = [
                    'debit'=>$debit_akhir,
                    'kredit'=>$kredit_akhir,
                    'ending_balance'=>$end,
                ];

                $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_saldo);

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($dbkr == 'D'){
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }
                            $debit = $cek_setelah->debit;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }


                $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
                if($coa->normal_balance == 'D'){
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
                    $begin = $cek_balance->beginning_balance;
                    $debit_awal = $cek_balance->debet;
                    $kredit = $cek_balance->kredit;

                    $debit_akhir = $debit_awal + $harga;
                    $ending_balance = $begin - $debit_akhir + $kredit;

                    $update_acc = [
                        'debet'=>$debit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);


                    $tabel_historyacc = [
                        'transaction_type'=>$type,
                        'account'=>$cek_coa->account,
                        'no_transaksi'=>$transaksi->no_pemakaian,
                        'tanggal_transaksi'=>$tgl_trans,
                        'dbkr_type'=>'D',
                        'bulan'=>$bulan,
                        'tahun'=>$tahun,
                        'total'=>$harga,
                        'no_journal'=>$transaksi->no_journal,
                        'jam_transaksi'=>$transaksi->created_at,
                        'kode_lokasi'=>$lokasi,
                    ];

                    $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                if($coa->normal_balance == 'D'){
                                    $begin = $cek_setelah->beginning_balance - $harga;
                                }else{
                                    $begin = $cek_setelah->beginning_balance + $harga;
                                }
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                $ending_balance = $begin - $debit + $kredit;

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }else{
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
                    $begin = $cek_balance->beginning_balance;
                    $debit = $cek_balance->debet;
                    $kredit_awal = $cek_balance->kredit;

                    $kredit_akhir = $kredit_awal + $harga;
                    $ending_balance = $begin - $debit + $kredit_akhir;

                    $update_acc = [
                        'kredit'=>$kredit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);


                    $tabel_historyacc = [
                        'transaction_type'=>$type,
                        'account'=>$cek_coa->account,
                        'no_transaksi'=>$transaksi->no_pemakaian,
                        'tanggal_transaksi'=>$tgl_trans,
                        'dbkr_type'=>'K',
                        'bulan'=>$bulan,
                        'tahun'=>$tahun,
                        'total'=>$harga,
                        'no_journal'=>$transaksi->no_journal,
                        'jam_transaksi'=>$transaksi->created_at,
                        'kode_lokasi'=>$lokasi,
                    ];

                    $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                if($coa->normal_balance == 'D'){
                                    $begin = $cek_setelah->beginning_balance - $harga;
                                }else{
                                    $begin = $cek_setelah->beginning_balance + $harga;
                                }
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                $ending_balance = $begin - $debit + $kredit;

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }
                
            }else{
                //CEK SEBELUM
                $cek_sebelum = Labarugiberjalan::on($konek)->whereMonth('periode', ($bulan-1))->whereYear('periode', $tahun)->first();
                if($cek_sebelum != null){
                    $ending_sebelum = $cek_sebelum->ending_balance;
                }else{
                    $ending_sebelum = 0;
                }

                $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
                $tgl_jalan2 = $tgl_jalan->periode;

                if($dbkr == 'D'){
                    $debit_akhir = $harga;
                    $kredit_akhir = 0;
                    $end = $ending_sebelum - $debit_akhir;
                }else{
                    $debit_akhir = 0;
                    $kredit_akhir = $harga;
                    $end = $ending_sebelum + $kredit_akhir;
                }

                $update_saldo = [
                    'periode'=>$tanggal_baru,
                    'beginning_balance'=>$ending_sebelum,
                    'debit'=>$debit_akhir,
                    'kredit'=>$kredit_akhir,
                    'ending_balance'=>$end,
                    'kode_lokasi'=>$lokasi,
                ];

                $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_saldo);


                $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
                if($coa->normal_balance == 'D'){
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance == null) {
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$cek_coa->account,
                            'beginning_balance'=>0,
                            'debet'=>0 - $harga,
                            'kredit'=>0,
                            'ending_balance'=>$harga,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $create_balance = AccBalance::on($konek)->create($update_acc);
                    }

                    $tabel_historyacc = [
                        'transaction_type'=>$type,
                        'account'=>$cek_coa->account,
                        'no_transaksi'=>$transaksi->no_pemakaian,
                        'tanggal_transaksi'=>$tgl_trans,
                        'dbkr_type'=>'D',
                        'bulan'=>$bulan,
                        'tahun'=>$tahun,
                        'total'=>$harga,
                        'no_journal'=>$transaksi->no_journal,
                        'jam_transaksi'=>$transaksi->created_at,
                        'kode_lokasi'=>$lokasi,
                    ];

                    $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);
                }else{
                    $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    if ($cek_balance == null) {
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$cek_coa->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>$harga,
                            'ending_balance'=>$harga,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $create_balance = AccBalance::on($konek)->create($update_acc);


                        $tabel_historyacc = [
                            'transaction_type'=>$type,
                            'account'=>$cek_coa->account,
                            'no_transaksi'=>$transaksi->no_pemakaian,
                            'tanggal_transaksi'=>$tgl_trans,
                            'dbkr_type'=>'K',
                            'bulan'=>$bulan,
                            'tahun'=>$tahun,
                            'total'=>$harga,
                            'no_journal'=>$transaksi->no_journal,
                            'jam_transaksi'=>$transaksi->created_at,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);
                    }
                }
            }
        }
    }

    public function lrb_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $dbkr)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        //UPDATE LABA RUGI BERJALAN
        if($coa->account_type == '5' || $coa->account_type == '4' || $coa->account_type == '6'){
            $cek_lrb = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        
            $begin_awal = $cek_lrb->beginning_balance;
            $debit_awal = $cek_lrb->debit;
            $kredit_awal = $cek_lrb->kredit;

            if($dbkr == 'D'){
                $debit_akhir = $debit_awal - $harga;
                $kredit_akhir = $kredit_awal;
                $end = $begin_awal - $debit_akhir + $kredit_awal;
            }else{
                $debit_akhir = $debit_awal;
                $kredit_akhir = $kredit_awal - $harga;
                $end = $begin_awal - $debit_awal + $kredit_akhir;
            }
                            
            $update_saldo = [
                'debit'=>$debit_akhir,
                'kredit'=>$kredit_akhir,
                'ending_balance'=>$end,
            ];

            $update = Labarugiberjalan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_saldo);

            //CEK SETELAH
            $i = $bulan;
            $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
            if ($cek_setelah != null) {
                for($i = $bulan; $i <= 12; $i++){
                    $cek_setelah = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        if($dbkr == 'D'){
                            $begin = $cek_setelah->beginning_balance + $harga;
                        }else{
                            $begin = $cek_setelah->beginning_balance - $harga;
                        }
                        $debit = $cek_setelah->debit;
                        $kredit = $cek_setelah->kredit;
                        $ending_balance = $begin - $debit + $kredit;

                        $tabel_baru = [
                            'beginning_balance'=>$begin,
                            'ending_balance'=>$ending_balance,
                        ];

                        $update_balance = Labarugiberjalan::on($konek)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                    }
                }
            }


            $cek_coa = Coa::on('mysql4')->where('account','3.2.00.000.00.002')->first();
            if($coa->normal_balance == 'D'){
                $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if($cek_balance != null){
                    $begin = $cek_balance->beginning_balance;
                    $debit_awal = $cek_balance->debet;
                    $kredit = $cek_balance->kredit;

                    $debit_akhir = $debit_awal - $harga;
                    $ending_balance = $begin - $debit_akhir + $kredit;

                    $update_acc = [
                        'debet'=>$debit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
                }

                $update_acc_history = Tb_acc_history::on($konek)->where('no_transaksi',$transaksi->no_pemakaian)->where('no_journal',$transaksi->no_journal)->where('transaction_type',$type)->delete();

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($coa->normal_balance == 'D'){
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }
                            $debit = $cek_setelah->debet;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }
            }else{
                $cek_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if($cek_balance != null){
                    $begin = $cek_balance->beginning_balance;
                    $debit = $cek_balance->debet;
                    $kredit_awal = $cek_balance->kredit;

                    $kredit_akhir = $kredit_awal - $harga;
                    $ending_balance = $begin - $debit + $kredit_akhir;

                    $update_acc = [
                        'kredit'=>$kredit_akhir,
                        'ending_balance'=>$ending_balance,
                    ];

                    $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
                }

                $update_acc_history = Tb_acc_history::on($konek)->where('no_transaksi',$transaksi->no_pemakaian)->where('no_journal',$transaksi->no_journal)->where('transaction_type',$type)->delete();

                //CEK SETELAH
                $i = $bulan;
                $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                if ($cek_setelah != null) {
                    for($i = $bulan; $i <= 12; $i++){
                        $cek_setelah = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                        if ($cek_setelah != null) {
                            if($coa->normal_balance == 'D'){
                                $begin = $cek_setelah->beginning_balance + $harga;
                            }else{
                                $begin = $cek_setelah->beginning_balance - $harga;
                            }
                            $debit = $cek_setelah->debet;
                            $kredit = $cek_setelah->kredit;
                            $ending_balance = $begin - $debit + $kredit;

                            $tabel_baru = [
                                'beginning_balance'=>$begin,
                                'ending_balance'=>$ending_balance,
                            ];

                            $update_balance = AccBalance::on($konek)->where('account',$cek_coa->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                        }
                    }
                }
            }
        }
    }


    public function accbalance_kredit_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        $cek_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        
        $begin = $cek_balance->beginning_balance;
        $debit = $cek_balance->debet;
        $kredit_awal = $cek_balance->kredit;

        $kredit_akhir = $kredit_awal + $harga;
        if($coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit - $kredit_akhir;
        }else{
            $ending_balance = $begin - $debit + $kredit_akhir;
        }

        $update_acc = [
            'kredit'=>$kredit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);


        $tabel_historyacc = [
            'transaction_type'=>$type,
            'account'=>$coa->account,
            'no_transaksi'=>$transaksi->no_pemakaian,
            'tanggal_transaksi'=>$tgl_trans,
            'dbkr_type'=>'K',
            'bulan'=>$bulan,
            'tahun'=>$tahun,
            'total'=>$harga,
            'no_journal'=>$transaksi->no_journal,
            'jam_transaksi'=>$transaksi->created_at,
            'kode_lokasi'=>$lokasi,
        ];

        $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);
    }

    public function accbalance_debit_post($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }

        $cek_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit_awal = $cek_balance->debet;
        $kredit = $cek_balance->kredit;

        $debit_akhir = $debit_awal + $harga;
        if($coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit_akhir - $kredit;
        }else{
            $ending_balance = $begin - $debit_akhir + $kredit;
        }

        $update_acc = [
            'debet'=>$debit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);


        $tabel_historyacc = [
            'transaction_type'=>$type,
            'account'=>$coa->account,
            'no_transaksi'=>$transaksi->no_pemakaian,
            'tanggal_transaksi'=>$tgl_trans,
            'dbkr_type'=>'D',
            'bulan'=>$bulan,
            'tahun'=>$tahun,
            'total'=>$harga,
            'no_journal'=>$transaksi->no_journal,
            'jam_transaksi'=>$transaksi->created_at,
            'kode_lokasi'=>$lokasi,
        ];

        $update_acc_history = Tb_acc_history::on($konek)->create($tabel_historyacc);
    }

    public function change(Request $request)
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_company = Auth()->user()->kode_company;

        $bulan = $request->month;
        $tahun = $request->year;

        $get_pakai = Pemakaian::on($konek)->whereMonth('tanggal_pemakaian',$bulan)->whereYear('tanggal_pemakaian',$tahun)->where('kode_lokasi','<>','HO')->where('status','POSTED')->get();
        $leng = count($get_pakai);
        foreach ($get_pakai as $rowdata){
          $data[] = array(
            'no_pemakaian'=>$rowdata->no_pemakaian,
          );
        }

        for($j = 0; $j < $leng; $j++){
          $pemakaian = Pemakaian::on($konek)->find($data[$j]['no_pemakaian']);
          $pemakaian_total = PemakaianDetail::on($konek)->where('no_pemakaian', $data[$j]['no_pemakaian'])->sum(\DB::raw('qty * harga'));

          $ledger_total = Ledger::on($konek2)->where('no_journal',$pemakaian->no_journal)->sum('debit');
          
          if($pemakaian_total != $ledger_total){
            $validate = $this->postingulang($data[$j]['no_pemakaian']);
          }
        }

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data berhasil di POST ULANG.'
        ];

        return response()->json($message);
    }

    public function postingulang($nomor)
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_company = Auth()->user()->kode_company;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        $pemakaian = Pemakaian::on($konek)->find($nomor);

        $tgl = $pemakaian->tanggal_pemakaian;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        $tanggal = '01';

        $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

        //UPDATE JURNAL
        if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03'){
            $konek2 = self::konek2();

            $update_ledger = Ledger::on($konek2)->where('no_journal',$pemakaian->no_journal)->delete();

            $cek_company = Auth()->user()->kode_company;

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;
            $detail = PemakaianDetail::on($konek)->where('no_pemakaian',$pemakaian->no_pemakaian)->get();
            $leng = count($detail);
            $data = array();

            if(!empty($detail)){
                foreach ($detail as $rowdata){

                    $kodeP = $rowdata->kode_produk;
                    $qtyS = $rowdata->qty;
                    $hargaS = $rowdata->harga;

                    $data[] = array(
                       'kode_produk'=>$kodeP,
                       'qty'=>$qtyS,
                       'harga'=>$hargaS,
                   );         
                }

            }

            foreach ($detail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = $total_harga;
            }

            $gt_apd = 0;
            $gt_ban = 0;
            $gt_bbm = 0;
            $gt_oli = 0;
            $gt_sprt = 0;
            $gt_unit = 0;

            for ($i = 0; $i < $leng; $i++) { 
                $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                $bulan = Carbon\Carbon::parse($pemakaian->tanggal_pemakaian)->format('m');
                $tahun = Carbon\Carbon::parse($pemakaian->tanggal_pemakaian)->format('Y');

                if($cek_produk->kode_kategori == 'APD'){
                    $gt_apd += $data[$i]['qty'] * $data[$i]['harga'];
                }

                if($cek_produk->kode_kategori == 'BAN'){
                    $gt_ban += $data[$i]['qty'] * $data[$i]['harga'];
                }

                if($cek_produk->kode_kategori == 'BBM'){
                    $gt_bbm += $data[$i]['qty'] * $data[$i]['harga'];
                }

                if($cek_produk->kode_kategori == 'OLI'){
                    $gt_oli += $data[$i]['qty'] * $data[$i]['harga'];
                }

                if($cek_produk->kode_kategori == 'SPRT'){
                    $gt_sprt += $data[$i]['qty'] * $data[$i]['harga'];
                }

                if($cek_produk->kode_kategori == 'UNIT'){
                    $gt_unit += $data[$i]['qty'] * $data[$i]['harga'];
                }
            }

            if($gt_apd > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_apd;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_apd,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_apd;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_apd;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_apd,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_apd;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

            if($gt_ban > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_ban;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_ban,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_ban;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_ban;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_ban,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_ban;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

            if($gt_bbm > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_bbm;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_bbm,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_bbm;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_bbm;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_bbm,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_bbm;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

            if($gt_oli > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_oli;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_oli,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_oli;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_oli;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_oli,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_oli;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

            if($gt_sprt > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_sprt;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_sprt,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_sprt;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_sprt;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_sprt,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_sprt;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }

            if($gt_unit > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '0501'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }

                $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_inventory->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_unit;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_inventory->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$pemakaian->no_pemakaian,
                    'kredit'=>$gt_unit,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_unit;
                $dbkr = 'K';
                $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                if ($cek_balance == null) {
                                //CEK SEBELUM
                    $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                    if($cek_sebelum != null){
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>$cek_sebelum->ending_balance,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>$cek_sebelum->ending_balance,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }else{
                        $update_acc = [
                            'periode'=>$tanggal_baru,
                            'fiscalyear'=>$tahun,
                            'account'=>$coa_biaya->account,
                            'beginning_balance'=>0,
                            'debet'=>0,
                            'kredit'=>0,
                            'ending_balance'=>0,
                            'kode_lokasi'=>$lokasi,
                        ];

                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                    }
                }else{
                                //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_unit;
                                $debit = $cek_setelah->debet;
                                $kredit = $cek_setelah->kredit;
                                if($coa_biaya->normal_balance == 'D'){
                                    $ending_balance = $begin + $debit - $kredit;
                                }else{
                                    $ending_balance = $begin - $debit + $kredit;
                                }

                                $tabel_baru = [
                                    'beginning_balance'=>$begin,
                                    'ending_balance'=>$ending_balance,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                            }
                        }
                    }
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'no_journal'=>$pemakaian->no_journal,
                    'journal_date'=>$pemakaian->tanggal_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$pemakaian->no_pemakaian,
                    'debit'=>$gt_unit,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_unit;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }
        }
    }
}
