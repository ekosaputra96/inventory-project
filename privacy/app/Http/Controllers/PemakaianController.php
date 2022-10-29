<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Pemakaian;
use App\Models\Pemakaianban;
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
use App\Models\Joborder;
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
use App\Models\SetupAkses;
use App\Models\Opname;
use App\Models\Costcenter;  
use App\Models\Workorder;
use App\Models\WorkorderDetail;
use App\Models\SetupFolder;
use App\Models\ReturPemakaian;
use App\Models\ReturpemakaianDetail;
use Illuminate\Support\Facades\Storage;
use PDF;
use Excel;
use DB;
use Alert;
use Carbon;
use DateTime;

class PemakaianController extends Controller
{
    public function index()
    {
        $konek = self::konek();
        $create_url = route('pemakaian.create');
        $JenisMobil= JenisMobil::on($konek)->pluck('nama_jenis_mobil','id');
        $Joborder='';
        
        if (auth()->user()->kode_company == '03') {
            $Asalat = Alat::on($konek)->whereNotNull('no_asset_alat')->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status', 'Aktif')->pluck('no_asset_alat','no_asset_alat');
            $Askapal = Kapal::on($konek)->whereNotNull('no_asset_kapal')->where('kode_lokasi', auth()->user()->kode_lokasi)->pluck('no_asset_kapal','no_asset_kapal');
            $Asmobil = Mobil::on($konek)->whereNotNull('no_asset_mobil')->where('kode_lokasi', auth()->user()->kode_lokasi)->pluck('no_asset_mobil','no_asset_mobil');
    
            $Alat = Alat::on($konek)->select('kode_alat', DB::raw("concat(nama_alat,' - ',no_asset_alat) as alats"))->where('status', 'Aktif')->where('kode_lokasi', auth()->user()->kode_lokasi)->pluck('alats','kode_alat');
            $Mobil = Mobil::on($konek)->select('kode_mobil', DB::raw("concat(nopol,' - ',no_asset_mobil) as mobils"))->where('kode_lokasi', auth()->user()->kode_lokasi)->pluck('mobils','kode_mobil');
            $Kapal = Kapal::on($konek)->select('kode_kapal', DB::raw("concat(nama_kapal,' - ',no_asset_kapal) as kapals"))->where('kode_lokasi', auth()->user()->kode_lokasi)->pluck('kapals','kode_kapal');
        }else if (auth()->user()->kode_company == '05') {
            $Asalat = Alat::on($konek)->whereNotNull('no_asset_alat')->where('status', 'Aktif')->pluck('no_asset_alat','no_asset_alat');
            $Askapal = Kapal::on($konek)->whereNotNull('no_asset_kapal')->pluck('no_asset_kapal','no_asset_kapal');
            $Asmobil = Mobil::on($konek)->whereNotNull('no_asset_mobil')->pluck('no_asset_mobil','no_asset_mobil');
    
            $Alat = Alat::on($konek)->select('kode_alat', DB::raw("concat(nama_alat,' - ',no_asset_alat) as alats"))->where('status', 'Aktif')->pluck('alats','kode_alat');
            $Mobil = Mobil::on($konek)->select('kode_mobil', DB::raw("concat(nopol,' - ',no_asset_mobil) as mobils"))->pluck('mobils','kode_mobil');
            $Kapal = Kapal::on($konek)->select('kode_kapal', DB::raw("concat(nama_kapal,' - ',no_asset_kapal) as kapals"))->pluck('kapals','kode_kapal');
            
            $Joborder = Joborder::on('mysql_front_sub')->pluck('no_joborder','no_joborder');
        }else {
            $Asalat = Alat::on($konek)->whereNotNull('no_asset_alat')->where('status', 'Aktif')->pluck('no_asset_alat','no_asset_alat');
            $Askapal = Kapal::on($konek)->whereNotNull('no_asset_kapal')->pluck('no_asset_kapal','no_asset_kapal');
            $Asmobil = Mobil::on($konek)->whereNotNull('no_asset_mobil')->pluck('no_asset_mobil','no_asset_mobil');
    
            $Alat = Alat::on($konek)->select('kode_alat', DB::raw("concat(nama_alat,' - ',no_asset_alat) as alats"))->where('status', 'Aktif')->pluck('alats','kode_alat');
            $Mobil = Mobil::on($konek)->select('kode_mobil', DB::raw("concat(nopol,' - ',no_asset_mobil) as mobils"))->pluck('mobils','kode_mobil');
            $Kapal = Kapal::on($konek)->select('kode_kapal', DB::raw("concat(nama_kapal,' - ',no_asset_kapal) as kapals"))->pluck('kapals','kode_kapal');
        }
        
        $Costcenter = Costcenter::where('kode_company', auth()->user()->kode_company)->pluck('desc','cost_center');
        
        if (auth()->user()->kode_company == '02'){
            $Workorder = Workorder::on($konek)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('no_wo','no_wo');
        }else {
            $Workorder = '';
        }
        
        $Company= Company::pluck('nama_company','kode_company');
        $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
        $tgl_jalan2 = $tgl_jalan->periode;
        $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
        $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
        $nama_lokasi = $get_lokasi->nama_lokasi;
        
        $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
        $nama_company = $get_company->nama_company;
        
        return view('admin.pemakaian.index',compact('Workorder','Costcenter','Joborder','create_url','Company','Mobil','JenisMobil','Alat','Asmobil','Asalat','period','Kapal','Askapal', 'nama_lokasi','nama_company'));
        
    }

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
        }else if ($compa == '06'){
            $koneksi = 'mysql_finance_inf';
        }
        return $koneksi;
    }

    public function anyData()
    {
        $konek = self::konek();
        $lokasi = auth()->user()->kode_lokasi;
        if($lokasi == 'HO'){
            return Datatables::of(Pemakaian::on($konek)->with('mobil','jenismobil','alat','kapal','company','Lokasi')->orderBy('created_at','desc')->withCount('pemakaiandetail'))->make(true);
        }
        else{
            return Datatables::of(Pemakaian::on($konek)->with('mobil','jenismobil','alat','kapal','company','Lokasi')->orderBy('created_at','desc')->withCount('pemakaiandetail')->where('kode_lokasi', $lokasi))->make(true);
        }
    }
    
    public function getDatapreview(){
        $konek = self::konek();
        $data = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian',request()->id)->orderBy('created_at','desc')->get();
        return response()->json($data);
    }
    
    public function kalkulasi_jurnalhonte(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $bulan = request()->bulan;
        $tahun = request()->tahun;
        $lokasijurnal = 'HO';
        Ledger::on($konek2)->where('tahun', $tahun)->where('periode', $bulan)->where('reference', 'regexp', '02NRK')->delete();

        $pemakaian = ReturPemakaian::on($konek)->whereMonth('tgl_retur_pemakaian', $bulan)->whereYear('tgl_retur_pemakaian', $tahun)->where('status', 'POSTED')->get();
        foreach ($pemakaian as $rows) {
            $cek_company = Auth()->user()->kode_company;
            $total_qty = 0;
            $total_harga = 0;
            $subtotal = 0;
            $grand_total = 0;
            
            if ($cek_company == '04' || $cek_company == '0401'){
                $compan = 'u5611458_gui_inventory_gut_laravel';
            }else if ($cek_company == '03'){
                $compan = 'u5611458_gui_inventory_emkl_laravel';
            }else if ($cek_company == '02'){
                $compan = 'u5611458_gui_inventory_pbm_laravel';
            }else if ($cek_company == '05'){
                $compan = 'u5611458_gui_inventory_sub_laravel';
            }else if ($cek_company == '01'){
                $compan = 'u5611458_gui_inventory_depo_laravel';
            }else if ($cek_company == '06'){
                $compan = 'u5611458_gui_inventory_pbminfra_laravel';
            }

            $no_pemakaian = $rows->no_retur_pemakaian;
            $no_journal = $rows->no_journal;
            $tgl_pemakaian = $rows->tgl_retur_pemakaian;
            $cost_center = $rows->cost_center;

            $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.retur_pemakaian_detail',$compan.'.produk.id','=',$compan.'.retur_pemakaian_detail.kode_produk')->where($compan.'.retur_pemakaian_detail.no_retur_pemakaian', $rows->no_retur_pemakaian)->groupBy('kategori_produk.kode_kategori')->get();
            foreach ($detail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;

                $totalhpp = ReturpemakaianDetail::on($konek)->select(DB::raw('SUM('.$compan.'.retur_pemakaian_detail.qty *'.$compan.'.retur_pemakaian_detail.harga) as total'))->join($compan.'.produk',$compan.'.retur_pemakaian_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.retur_pemakaian_detail.no_retur_pemakaian', $no_pemakaian)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                $totalhpp = $totalhpp->total;
                $grand_total += $totalhpp;

                $kategori = KategoriProduk::where('kode_kategori', $row->kode_kategori)->first();

                if ($cek_company == '04'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                    $cc_inv = $kategori->cc_gut_persediaan;
                    $cc_biaya = $kategori->cc_gut;
                }else if ($cek_company == '0401'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                    $cc_inv = $kategori->cc_gutjkt_persediaan;
                    $cc_biaya = $kategori->cc_gutjkt;
                }else if ($cek_company == '03'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                    $cc_inv = $kategori->cc_emkl_persediaan;
                    $cc_biaya = $kategori->cc_emkl;
                }else if ($cek_company == '02'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                    $cc_inv = $kategori->cc_pbm_persediaan;
                    $cc_biaya = $kategori->cc_pbm;
                }else if ($cek_company == '01'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                    $cc_inv = $kategori->cc_depo_persediaan;
                    $cc_biaya = $kategori->cc_depo;
                }else if ($cek_company == '05'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                    $cc_inv = $kategori->cc_sub_persediaan;
                    $cc_biaya = $kategori->cc_sub;
                }else if ($cek_company == '06'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                    $cc_inv = $kategori->cc_infra_persediaan;
                    $cc_biaya = $kategori->cc_infra;
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'cost_center'=>$cc_inv,
                    'no_journal'=>$no_journal,
                    'journal_date'=>$tgl_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$no_pemakaian,
                    'debit'=>$totalhpp,
                    'kode_lokasi'=>$lokasijurnal,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'cost_center'=>$cost_center,
                    'no_journal'=>$no_journal,
                    'journal_date'=>$tgl_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$no_pemakaian,
                    'kredit'=>$totalhpp,
                    'kode_lokasi'=>$lokasijurnal,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);
            }
        }
        
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Selesai.'
        ];
        return response()->json($message);
    }
    
    
    public function kalkulasi_jurnal(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $bulan = request()->bulan;
        $tahun = request()->tahun;
        $lokasijurnal = 'HO';
        Ledger::on($konek2)->where('tahun', $tahun)->where('periode', $bulan)->where('reference', 'regexp', '02NPK')->delete();

        $pemakaian = Pemakaian::on($konek)->whereMonth('tanggal_pemakaian', $bulan)->whereYear('tanggal_pemakaian', $tahun)->where('status', 'POSTED')->get();
        foreach ($pemakaian as $rows) {
            $cek_company = Auth()->user()->kode_company;
            $total_qty = 0;
            $total_harga = 0;
            $subtotal = 0;
            $grand_total = 0;
            
            if ($cek_company == '04' || $cek_company == '0401'){
                $compan = 'u5611458_gui_inventory_gut_laravel';
            }else if ($cek_company == '03'){
                $compan = 'u5611458_gui_inventory_emkl_laravel';
            }else if ($cek_company == '02'){
                $compan = 'u5611458_gui_inventory_pbm_laravel';
            }else if ($cek_company == '05'){
                $compan = 'u5611458_gui_inventory_sub_laravel';
            }else if ($cek_company == '01'){
                $compan = 'u5611458_gui_inventory_depo_laravel';
            }else if ($cek_company == '06'){
                $compan = 'u5611458_gui_inventory_pbminfra_laravel';
            }

            $no_pemakaian = $rows->no_pemakaian;
            $no_journal = $rows->no_journal;
            $tgl_pemakaian = $rows->tanggal_pemakaian;
            $cost_center = $rows->cost_center;

            $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.pemakaian_detail',$compan.'.produk.id','=',$compan.'.pemakaian_detail.kode_produk')->where($compan.'.pemakaian_detail.no_pemakaian', $rows->no_pemakaian)->groupBy('kategori_produk.kode_kategori')->get();
            foreach ($detail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;

                $totalhpp = PemakaianDetail::on($konek)->select(DB::raw('SUM('.$compan.'.pemakaian_detail.qty *'.$compan.'.pemakaian_detail.harga) as total'))->join($compan.'.produk',$compan.'.pemakaian_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.pemakaian_detail.no_pemakaian', $no_pemakaian)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                $totalhpp = $totalhpp->total;
                $grand_total += $totalhpp;

                $kategori = KategoriProduk::where('kode_kategori', $row->kode_kategori)->first();

                if ($cek_company == '04'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                    $cc_inv = $kategori->cc_gut_persediaan;
                    $cc_biaya = $kategori->cc_gut;
                }else if ($cek_company == '0401'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                    $cc_inv = $kategori->cc_gutjkt_persediaan;
                    $cc_biaya = $kategori->cc_gutjkt;
                }else if ($cek_company == '03'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                    $cc_inv = $kategori->cc_emkl_persediaan;
                    $cc_biaya = $kategori->cc_emkl;
                }else if ($cek_company == '02'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                    $cc_inv = $kategori->cc_pbm_persediaan;
                    $cc_biaya = $kategori->cc_pbm;
                }else if ($cek_company == '01'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                    $cc_inv = $kategori->cc_depo_persediaan;
                    $cc_biaya = $kategori->cc_depo;
                }else if ($cek_company == '05'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                    $cc_inv = $kategori->cc_sub_persediaan;
                    $cc_biaya = $kategori->cc_sub;
                }else if ($cek_company == '06'){
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                    $cc_inv = $kategori->cc_infra_persediaan;
                    $cc_biaya = $kategori->cc_infra;
                }

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_inventory->account,
                    'cost_center'=>$cc_inv,
                    'no_journal'=>$no_journal,
                    'journal_date'=>$tgl_pemakaian,
                    'db_cr'=>'K',
                    'reference'=>$no_pemakaian,
                    'kredit'=>$totalhpp,
                    'kode_lokasi'=>$lokasijurnal,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $update_ledger = [
                    'tahun'=>$tahun,
                    'periode'=>$bulan,
                    'account'=>$coa_biaya->account,
                    'cost_center'=>$cost_center,
                    'no_journal'=>$no_journal,
                    'journal_date'=>$tgl_pemakaian,
                    'db_cr'=>'D',
                    'reference'=>$no_pemakaian,
                    'debit'=>$totalhpp,
                    'kode_lokasi'=>$lokasijurnal,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);
            }
        }
        
        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Selesai.'
        ];
        return response()->json($message);
    }

    public function previewpo()
    {
        $konek = self::konek();
        $pemakaian = Pemakaian::on($konek)->find(request()->id);

        if ($pemakaian->type == 'Alat'){
            $alats = Alat::on($konek)->find($pemakaian->kode_alat);
            $alat = $alats->nama_alat;
            $aset = $alats->no_asset_alat;
        }else if ($pemakaian->type == 'Mobil'){
            $alats = Mobil::on($konek)->find($pemakaian->kode_mobil);
            $alat = $alats->nopol;
            $aset = $alats->no_asset_mobil;
        }else if ($pemakaian->type == 'Kapal'){
            $alats = Kapal::on($konek)->find($pemakaian->kode_kapal);
            $alat = $alats->nama_kapal;
            $aset = $alats->no_asset_kapal;
        }else {
            $alat = '-';
            $aset = '-';
        }

        $nojo = $pemakaian->no_jo;
        $nowo = $pemakaian->no_wo;
        $note = $pemakaian->deskripsi;
        
        $output = array(
            'no_pemakaian'=>$pemakaian->no_pemakaian,
            'type'=>$pemakaian->type,
            'alat'=>$alat,
            'no_asset'=>$aset,
            'nojo'=>$nojo,
            'nowo'=>$nowo,
            'note'=>$note,
            'pemakai'=>$pemakaian->pemakai,
            'tanggal_pemakaian'=>$pemakaian->tanggal_pemakaian,
            'kode_company'=>Auth()->user()->kode_company,
        );
        return response()->json($output);
    }
    
    public function ttd_buat()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-dibuat'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pemakaian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pemakaian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Dibuat Oleh) telah disimpan.'
        ];
        return response()->json($message);
    }

    public function ttd_terima()
    {
        $konek = self::konek();
        $signature = request()->img;
        $signatureFileName = request()->no.'-diterima'.'.png';
        $signature = str_replace('data:image/png;base64,', '', $signature);
        $signature = str_replace(' ', '+', $signature);
        $data = base64_decode($signature);

        $cekfile = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pemakaian/'.$signatureFileName;
        if (file_exists($cekfile)) {
            unlink($cekfile);
        }

        $folder = realpath(dirname(getcwd())).'/gui_inventory_laravel/digital/pemakaian/';
        $file = $folder.$signatureFileName;
        file_put_contents($file, $data);

        $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'TTD (Pemakai) telah disimpan.'
        ];
        return response()->json($message);
    }
    
    public function grandios()
    {
        $konek = self::konek();
        $detail = PemakaianDetail::on($konek)->where('no_pemakaian', request()->no_pemakaian)->sum(\DB::raw('qty * harga'));
        $output = array(
            'grand_total'=>$detail,
        );
        return response()->json($output);
    }
    
    public function limitos()
    {
        $konek = self::konek();
        $limit = SetupAkses::on($konek)->where('limit_dari', 0)->where('limit_total', 50000000)->first();
        $limit2 = SetupAkses::on($konek)->where('limit_dari', 50000000)->where('limit_total', 500000000)->first();
        $limit3 = SetupAkses::on($konek)->where('limit_dari', 500000000)->first();
        if ($limit != null) {
            $output = array(
                'nama'=>$limit->nama_user,
                'nama2'=>$limit->nama_user2,
                'nama3'=>$limit->nama_user3,
                'grand1'=>$limit->limit_dari,
                'grand2'=>$limit->limit_total,
                'namara'=>$limit2->nama_user,
                'namara2'=>$limit2->nama_user2,
                'namara3'=>$limit2->nama_user3,
                'grandara1'=>$limit2->limit_dari,
                'grandara2'=>$limit2->limit_total,
                'namaga'=>$limit3->nama_user,
                'namaga2'=>$limit3->nama_user2,
                'namaga3'=>$limit3->nama_user3,
                'grandaga1'=>$limit3->limit_dari,
                'grandaga2'=>$limit3->limit_total,
            );
            return response()->json($output);
        }
    }
    
    public function historia()
    {
        $konek = self::konek();
        $post = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Post No.%')->orderBy('created_at','desc')->first();
        if ($post != null) {
            $nama1 = $post->nama;
        }else {
            $nama1 = 'None';
        }

        $unpost = user_history::on($konek)->where('aksi', 'like', '%'.request()->id.'%')->where('aksi','like','Unpost No.%')->orderBy('created_at','desc')->first();
        if ($unpost != null) {
            $nama2 = $unpost->nama;
        }else {
            $nama2 = 'None';
        }

        $output = array(
            'post'=>$nama1,
            'unpost'=>$nama2,
        );
        return response()->json($output);
    }

    public function detail($pemakaian)
    {   
        $konek = self::konek();
        $pemakaian = Pemakaian::on($konek)->find($pemakaian);
        $tanggal = $pemakaian->tanggal_pemakaian;
        $no_pemakaian = $pemakaian->no_pemakaian;

        $validate = $this->periodeChecker($tanggal);
             
        if($validate == true){
            $data = Pemakaian::on($konek)->find($no_pemakaian);

            $total_qty = 0;
            $total_harga = 0;
            $grand_total = 0;

            $Pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', $pemakaian->no_pemakaian)
            ->orderBy('created_at','desc')->get();

            foreach ($Pemakaiandetail as $row){
                $total_qty += $row->qty;
                $subtotal = $row->harga * $row->qty;
                $total_harga += $subtotal;
                $grand_total = number_format($total_harga,2,",",".");
            }

            $list_url= route('pemakaian.index');

            $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode', 'Open')->orwhere('reopen_status', 'true')->first();

            if ($pemakaian->no_wo != null){
                $Produk = Produk::on($konek)->Join('workorder_detail', 'produk.id', '=', 'workorder_detail.kode_produk')->where('workorder_detail.type','Stock')->where('workorder_detail.status_produk','OFF')->where('workorder_detail.no_wo',$pemakaian->no_wo)->pluck('produk.nama_produk','produk.id');
            }else {
                $Produk = Produk::on($konek)->Join('tb_item_bulanan', 'produk.id', '=', 'tb_item_bulanan.kode_produk')->where('ending_stock','>',0)->where('periode',$cek_bulan->periode)->where('kode_lokasi',auth()->user()->kode_lokasi)->pluck('produk.nama_produk','produk.id');
            }

            $Satuan = satuan::pluck('nama_satuan', 'kode_satuan');
            $Kategori = KategoriProduk::pluck('nama_kategori', 'kode_kategori');
            $Merek = Merek::on($konek)->pluck('nama_merek', 'kode_merek');
            $Ukuran= Ukuran::on($konek)->pluck('nama_ukuran', 'kode_ukuran');
            $Satuan= satuan::pluck('nama_satuan', 'kode_satuan');
            $Company= Company::pluck('nama_company', 'kode_company');

            $tgl_jalan = tb_akhir_bulan::on($konek)->where('reopen_status','true')->orwhere('status_periode','Open')->first();
            $tgl_jalan2 = $tgl_jalan->periode;
            $period = Carbon\Carbon::parse($tgl_jalan2)->format('F Y');
            $get_lokasi = MasterLokasi::where('kode_lokasi',auth()->user()->kode_lokasi)->first();
            $nama_lokasi = $get_lokasi->nama_lokasi;

            $get_company = Company::where('kode_company',auth()->user()->kode_company)->first();
            $nama_company = $get_company->nama_company;

            return view('admin.pemakaiandetail.index', compact('pemakaian','Pemakaiandetail','list_url','Produk','Satuan','total_qty','grand_total','Kategori','Merek','Ukuran','Satuan','Company','period', 'nama_lokasi','nama_company'));
        }
        else{
            alert()->success('Status POSTED / Periode Telah CLOSED: '.$tanggal,'GAGAL!')->persistent('Close');
            return redirect()->back();
        }
    }

    public function export2(){
        $request = $_GET['no_pemakaian'];
        $konek = self::konek();
        $pemakaian = Pemakaian::on($konek)->where('no_pemakaian',$request)->first();
        $user = $pemakaian->created_by;

        $no_pemakaian = $pemakaian->no_pemakaian;
        $type = $pemakaian->type;
        $pemakai = $pemakaian->pemakai;

        $kode_company = $pemakaian->kode_company;
        // dd($tipe);
        $pemakaiandetail = PemakaianDetail::on($konek)->where('no_pemakaian',$request)->get();

        $company = Company::where('kode_company',$kode_company)->first();
        $nama_company = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');

        $tgl = $pemakaian->tanggal_pemakaian;
        $date=date_create($tgl);
        // dd($tgl);

        if($type == 'Alat'){
            $alat = $pemakaian->kode_alat;
            $get_alat = Alat::on($konek)->find($alat);
            $nama = $get_alat->nama_alat;
            
            $aset = $pemakaian->no_asset_alat;
        }
        else if($type == 'Mobil')
        {
            $mobil = $pemakaian->kode_mobil;
            $get_mobil = Mobil::on($konek)->find($mobil);
            $nama = $get_mobil->nopol;
            
            $aset = $pemakaian->no_asset_mobil;
        }
        else if($type == 'Kapal'){
            $kapal = $pemakaian->kode_kapal;
            $get_kapal = Kapal::on($konek)->find($kapal);
            $nama = $get_kapal->nama_kapal;
            
            $aset = $pemakaian->no_asset_kapal;
        }
        else{
            $nama = '';
            $aset = '';
        }
        
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        
        $setupfolder = SetupFolder::find(11);
        $tes_save = $company->kode_company.". ".$company->nama_company."/".$setupfolder->folder."/".$setupfolder->subfolder."/".$tahun."/".$bulan."/".$no_pemakaian.".pdf";
        
        $pdf = PDF::loadView('/admin/pemakaian/cetak', compact('pemakaiandetail','request','no_pemakaian','tgl','nama_company','date_now','type','pemakai','nama','aset','pemakaian','user'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print No. Pemakaian : '.$no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        Storage::disk('ftp')->put($tes_save, $pdf->output());
        return $pdf->stream($no_pemakaian.'.pdf');
    }


    public function exportPDF3(){
        $konek = self::konek();
        $konek2 = self::konek2();
        $request = $_GET['no_pemakaian'];
        $no_journal = $_GET['no_journal'];

        $pemakaian = Pemakaian::on($konek)->find($request);
        $jur = $pemakaian->kode_jurnal;
        $jurnal = Jurnal::find($jur);

        $total_qty = 0;
        $total_harga = 0;
        $grand_total = 0;
        $detail = PemakaianDetail::on($konek)->where('no_pemakaian',$request)->get();
        foreach ($detail as $row){
            $total_qty += $row->qty;
            $subtotal = $row->harga * $row->qty;
            $total_harga += $subtotal;
            $grand_total = $total_harga;
        }

        $ledger2 = Ledger::on($konek2)->with('coa')->where('no_journal',$no_journal)->first();

        $ledger = Ledger::on($konek2)->select('ledger.*','coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('no_journal', $no_journal)->get();

        $user = $pemakaian->created_by;
        $tgl = $pemakaian->tanggal_pemakaian;
        $date=date_create($tgl);

        $ttd = $user;

        $get_lokasi = auth()->user()->kode_lokasi;
        $get_company = auth()->user()->kode_company;

        $nama_lokasi = MasterLokasi::find($get_lokasi);
        $nama = $nama_lokasi->nama_lokasi;

        $company = Company::find($get_company);
        $nama2 = $company->nama_company;

        $dt = Carbon\Carbon::now();
        $date_now = Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');
        $journal_date = Carbon\Carbon::parse($ledger2->journal_date)->format('d/m/Y');

        $pdf = PDF::loadView('/admin/pemakaian/pdf2', compact('pemakaian','request', 'jurnal','tgl','date', 'ttd','date_now','ledger','ledger2','dt','user','nama','nama2','journal_date','grand_total'));
        $pdf->setPaper([0, 0, 684, 792], 'potrait');
        
        $nama = auth()->user()->name;
        $tmp = ['nama' => $nama,'aksi' => 'Print Zoom Jurnal : '.$request.'.','created_by'=>$nama,'updated_by'=>$nama];
        user_history::on($konek)->create($tmp);
        
        return $pdf->stream('Cetak Zoom Jurnal '.$request.'.pdf');
    }
    
    public function getwoalat()
    {
        $konek = self::konek();

        $wo = Workorder::on($konek)->where('no_wo',request()->no_wo)->first();
        $kodealat = Alat::on($konek)->where('no_asset_alat',$wo->no_asset_alat)->first();

        $output = array(
            'kode_alat'=>$kodealat->kode_alat,
            'no_asset_alat'=>$wo->no_asset_alat,
        );

        return response()->json($output);
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
    }

    public function accbalance_kredit_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
        $cek_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit = $cek_balance->debet;
        $kredit_awal = $cek_balance->kredit;

        $get_coa = Coa::on('mysql4')->where('account',$coa)->first();
        $kredit_akhir = $kredit_awal - $harga;
        if($get_coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit - $kredit_akhir;
        }else{
            $ending_balance = $begin - $debit + $kredit_akhir;
        }

        $update_acc = [
            'kredit'=>$kredit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }

    public function accbalance_debit_unpost($coa, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans)
    {
        $konek = self::konek2();
        if(auth()->user()->kode_company != '04' || auth()->user()->kode_company != '0401'){
            $lokasi = 'HO';
        }
        $cek_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

        $begin = $cek_balance->beginning_balance;
        $debit_awal = $cek_balance->debet;
        $kredit = $cek_balance->kredit;

        $get_coa = Coa::on('mysql4')->where('account',$coa)->first();
        $debit_akhir = $debit_awal - $harga;
        if($get_coa->normal_balance == 'D'){
            $ending_balance = $begin + $debit_akhir - $kredit;
        }else{
            $ending_balance = $begin - $debit_akhir + $kredit;
        }

        $update_acc = [
            'debet'=>$debit_akhir,
            'ending_balance'=>$ending_balance,
        ];

        $update_balance = AccBalance::on($konek)->where('account',$coa)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($update_acc);
    }



    function periodeChecker($tgl)
    {   
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        $konek = self::konek();
        $tabel = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
        // dd($tabel);
        
        if($tabel != null)
        {
            $stat = $tabel->status_periode;
            $re_stat = $tabel->reopen_status;
            if($stat =='Open' || $re_stat == 'true')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    //UNTUK CEK APAKAH DETAIL PEMAKAIAN ADA YANG HPP PRODUKNYA BERUBAH
    function produkChecker2($no_pemakaian, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaian, $koneksi)
    {   
        $konek = self::konek();
        $pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', request()->id)->get();
        $no_pemakaian = request()->id;
             
        $data = array();

        if(!empty($pemakaiandetail)){
            foreach ($pemakaiandetail as $rowdata){
                $data[] = array(
                    'kode_produk'=>$rowdata->kode_produk,
                    'partnumber'=>$rowdata->partnumber,
                );         
            }
        }

        if(!empty($pemakaiandetail)){
            $leng = count($pemakaiandetail);

            $i = 0;
            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();

                if($tb_item_bulanan != null){
                    $hpp_akhir = $tb_item_bulanan->hpp;

                    $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                    $hpp_transaksi = $pemakaiandetail2->harga;
                    $produk = $data[$i]['kode_produk'];
                    $cek_produk = Produk::on($konek)->find($produk);
                    $nama_produk = $cek_produk->nama_produk;

                    if($tb_item_bulanan->ending_stock > 0){
                        if(round($hpp_akhir) != round($hpp_transaksi)){
                            exit("UNPOST No. Pemakaian: $pemakaian->no_pemakaian gagal, karena $nama_produk mengalami perubahan HPP.");
                        }  
                    }
                } 
            }
        }

        return true;
    }

    //UNTUK CEK APAKAH STOK BARANG JADI MINUS
    function produkChecker($no_pemakaian, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaian, $koneksi)
    {   
        $konek = self::konek();
        $pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', request()->id)->get();
        $no_pemakaian = request()->id;
             
        $data = array();

        if(!empty($pemakaiandetail)){
            foreach ($pemakaiandetail as $rowdata){
                $data[] = array(
                    'no_pemakaian'=>$no_pemakaian,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                );          
            }
        }

        
        if(!empty($pemakaiandetail)){
            $leng = count($pemakaiandetail);

            $i = 0;
            for($i = 0; $i < $leng; $i++){
                $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    // dd($data[0]['kode_produk']);
                if($tb_item_bulanan != null){
                    $produk_awal = $tb_item_bulanan->kode_produk;

                    $stock_begin = $tb_item_bulanan->begin_stock;
                    $amount_begin = $tb_item_bulanan->begin_amount;
                    $stok_in = $tb_item_bulanan->in_stock;
                    $stok_ending = $tb_item_bulanan->ending_stock;
                    $amount_masuk = $tb_item_bulanan->in_amount;
                    $amount = $tb_item_bulanan->ending_amount;
                    $stock_out = $tb_item_bulanan->out_stock;
                    $outamount_awal_1 = $tb_item_bulanan->out_amount;
                    $amount_sale = $tb_item_bulanan->sale_amount;
                    $amount_trfin = $tb_item_bulanan->trf_in_amount;
                    $amount_trfout = $tb_item_bulanan->trf_out_amount;
                    $amount_adj = $tb_item_bulanan->adjustment_amount;
                    $amount_op = $tb_item_bulanan->amount_opname;
                    $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                    $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                    $disassembling_amount = $tb_item_bulanan->disassembling_amount;
                    $assembling_amount = $tb_item_bulanan->assembling_amount;

                    $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                    $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                        // $hpp = $pemakaiandetail2->harga;
                    if($stok_ending != 0){
                        $hpp_real = $amount/$stok_ending;
                    }else{
                        exit();
                    }

                    $konversi = Konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                    $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                    $waktu = $tgl;
                    $barang = $data[$i]['kode_produk'];
                    $stok_masuk = $stok_in;
                    $stok_keluar = $stock_out + $qty_baru;
                    $amount_keluar = $outamount_awal_1 + ($hpp_real*$qty_baru);
                    $end_stok = $stok_ending - $qty_baru;
                    $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount - $disassembling_amount + $assembling_amount;

                    if($end_stok < 0){
                        exit();
                    }

                    $tgl_pakai1 = $pemakaian->tanggal_pemakaian;
                    $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                    $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                    $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                    $status_reopen = $reopen->reopen_status;

                    if($status_reopen == 'true'){
                        $tgl_pakai = $pemakaian->tanggal_pemakaian;
                        $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                        $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

                        $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                        $periode_berjalan = $tb_akhir_bulan2->periode;

                        $datetime1 = new DateTime($periode_berjalan);
                        $datetime2 = new DateTime($tanggal_baru);
                        $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                        $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');

                        $diff = $datetime1->diff($datetime2);
                        $final_date = $diff->d;
                        $final_year = $diff->y;
                        $f_month = $diff->m;

                            //convert
                        $timeStart = strtotime($tanggal_baru);
                        $timeEnd = strtotime($periode_berjalan);

                            // Menambah bulan ini + semua bulan pada tahun sebelumnya
                        $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                            // hitung selisih bulan
                        $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                        $final_month = $numBulan;

                        $bulan3 = 0;
                        $j = 1;
                        while($j <= $final_month){
                            $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $hpp = $pemakaiandetail2->harga;
                            $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                            $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                            $amount_o = $hpp*$stock_o;

                            $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                            $tahun_kemarin = $tahun_berjalan - 1;

                            $bulancek = $bulan + $j;
                            if($bulancek >= 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                $bulan3 += 1;
                                $bulan2 = strval($bulan3);
                                $tahun2 = strval($tahun_berjalan);
                            }else if($bulancek < 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                $bulan2 = strval($bulancek);
                                $tahun2 = strval($tahun_kemarin);
                            }else{
                                $bulan2 = strval($bulancek);
                                $tahun2 = strval($tahun_berjalan);
                            }

                            $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                            if($tb_item_bulanan2 != null){
                                $bs = $tb_item_bulanan2->begin_stock;
                                $ba = $tb_item_bulanan2->begin_amount;
                                $es = $tb_item_bulanan2->ending_stock;
                                $ea = $tb_item_bulanan2->ending_amount;

                                $begin_stock1 = $bs - $stock_o;
                                $begin_amount1 = $ba - $amount_o;

                                $end_stok1 = $es - $stock_o;
                                $end_amount1 = $ea - $amount_o;

                                $tabel_baru2 = [
                                    'begin_stock'=>$begin_stock1,
                                    'begin_amount'=>$begin_amount1,
                                    'ending_stock'=>$end_stok1,
                                    'ending_amount'=>$end_amount1,
                                ];

                                if($end_stok1 < 0){
                                    exit();
                                }
                            }
                            $j++;
                        }
                    }

                }

            }
        }
        
        return true;
    }


    public function getDatajurnal2(){
        $konek2 = self::konek2();
        $data = Ledger::on($konek2)->with('costcenter')->select('ledger.*','u5611458_gui_general_ledger_laravel.coa.ac_description')->join('u5611458_gui_general_ledger_laravel.coa','u5611458_gui_general_ledger_laravel.coa.account','=','ledger.account')->where('ledger.no_journal',request()->id)->orderBy('ledger.created_at','desc')->get();
        return response()->json($data);
    }

    public function cekjurnal2()
    {
        $konek = self::konek();
        $konek2 = self::konek2();
        $cek = Ledger::on($konek2)->where('no_journal', request()->no_journal)->first();
        $cek_ar = Pemakaian::on($konek)->where('no_journal', request()->no_journal)->first();

        $output = array(
            'journal_date'=>Carbon\Carbon::parse($cek->journal_date)->format('d/m/Y'),
            'reference'=>$cek->reference,
            'created_at'=>($cek_ar->created_at)->format('d/m/Y H:i:s'),
            'updated_by'=>$cek->updated_by,
            'status'=>$cek_ar->status,
        );
        return response()->json($output);
    }

    public function hitungjurnal()
    {
        $konek = self::konek();
        $cek_company = Auth()->user()->kode_company;
        $lokasi = auth()->user()->kode_lokasi;

        $pemakaian_header = Pemakaian::on($konek)->where('tanggal_pemakaian','>=','2022-01-01')->where('tanggal_pemakaian','<','2022-07-01')->get();

        foreach ($pemakaian_header as $row) {
            $pemakaian = Pemakaian::on($konek)->find($row->no_pemakaian);
            $pemakaiandetail = PemakaianDetail::on($konek)->where('no_pemakaian', $row->no_pemakaian)->get();
            $leng = count($pemakaiandetail);
            $data = array();

            //CEK COA KATEGORI
            foreach ($pemakaiandetail as $rowdata){
                $kodeP = $rowdata->kode_produk;

                $data[] = array(
                    'kode_produk'=>$kodeP,
                );
            }

            for ($i = 0; $i < $leng; $i++) { 
                    $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                    if($cek_produk->kode_kategori == 'APD'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','APD')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','APD')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','APD')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','APD')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','APD')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','APD')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_produk->kode_kategori == 'BAN'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BAN')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BAN')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BAN')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BAN')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BAN')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BAN')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_produk->kode_kategori == 'BBM'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BBM')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BBM')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BBM')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BBM')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','BBM')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','BBM')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_produk->kode_kategori == 'OLI'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','OLI')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','OLI')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','OLI')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','OLI')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','OLI')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','OLI')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_produk->kode_kategori == 'SPRT'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SPRT')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SPRT')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SPRT')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SPRT')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SPRT')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SPRT')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_produk->kode_kategori == 'UNIT'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','UNIT')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','UNIT')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','UNIT')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','UNIT')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','UNIT')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','UNIT')->where('coa_pbm',null)->first();
                        }
                    }
                    
                    if($cek_produk->kode_kategori == 'SLDG'){
                        if($cek_company == '04'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coabiaya_gut',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SLDG')->where('coa_gut',null)->first();
                        }else if($cek_company == '0401'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coabiaya_gutjkt',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SLDG')->where('coa_gutjkt',null)->first();
                        }else if($cek_company == '03'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coabiaya_emkl',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SLDG')->where('coa_emkl',null)->first();
                        }else if($cek_company == '05'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coabiaya_sub',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SLDG')->where('coa_sub',null)->first();
                        }else if($cek_company == '02'){
                            $cek_kategori = KategoriProduk::where('kode_kategori','SLDG')->where('coabiaya_pbm',null)->first();
                            $cek_kategori2 = KategoriProduk::where('kode_kategori','SLDG')->where('coa_pbm',null)->first();
                        }
                    }

                    if($cek_kategori != null){
                        $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Kategori: '.$cek_kategori->kode_kategori.' belum memiliki COA Biaya, silahkan lengkapi terlebih dahulu.',
                        ];
                        return response()->json($message);
                    }else if($cek_kategori2 != null){
                       $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Kategori: '.$cek_kategori2->kode_kategori.' belum memiliki COA Persediaan, silahkan lengkapi terlebih dahulu.',
                        ];
                        return response()->json($message); 
                    }
                }   

            $tgl = $pemakaian->tanggal_pemakaian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            //UPDATE JURNAL
            $pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', $row->no_pemakaian)->get();
            $no_pemakaian = $row->no_pemakaian;
            $data = array();
            foreach ($pemakaiandetail as $rowdata){
                $data[] = array(
                    'no_pemakaian'=>$no_pemakaian,
                    'kode_produk'=>$rowdata->kode_produk,
                    'kode_satuan'=>$rowdata->kode_satuan,
                    'qty'=>$rowdata->qty,
                    'partnumber'=>$rowdata->partnumber,
                    'harga'=>$rowdata->harga,
                );        
            }
            $leng = count($pemakaiandetail);

            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    $detail = PemakaianDetail::on($konek)->where('no_pemakaian',$pemakaian->no_pemakaian)->get();
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
                    $gt_sldg = 0;

                    for ($i = 0; $i < $leng; $i++) { 
                        $cek_produk = Produk::on($konek)->where('id', $data[$i]['kode_produk'])->first();

                        $bulan = Carbon\Carbon::parse($pemakaian->tanggal_pemakaian)->format('m');
                        $tahun = Carbon\Carbon::parse($pemakaian->tanggal_pemakaian)->format('Y');

                                //MENGHITUNG NILAI DETAIL PER KATEGORI
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
                        
                        if($cek_produk->kode_kategori == 'SLDG'){
                            $gt_sldg += $data[$i]['qty'] * $data[$i]['harga'];
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
                        }else if($cek_company == '05'){
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
                        }else if($cek_company == '05'){
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
                        }else if($cek_company == '05'){
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
                        }else if($cek_company == '05'){
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
                        }else if($cek_company == '05'){
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
                        }else if($cek_company == '05'){
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
                    
                    if($gt_sldg > 0){
                        if ($cek_company == '04') {
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                        }else if($cek_company == '0401'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                        }else if($cek_company == '03'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                        }else if($cek_company == '02'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                        }else if($cek_company == '01'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                        }else if($cek_company == '05'){
                            $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
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

                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance - $gt_sldg;
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
                        }else{
                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance - $gt_sldg;
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
                            'kredit'=>$gt_sldg,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $pemakaian;
                        $tgl_trans = $pemakaian->tanggal_pemakaian;
                        $harga_acc = $gt_sldg;
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

                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                        }else{
                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                            'debit'=>$gt_sldg,
                            'kode_lokasi'=>$lokasi,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $pemakaian;
                        $tgl_trans = $pemakaian->tanggal_pemakaian;
                        $harga_acc = $gt_sldg;
                        $dbkr = 'D';
                        $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                    }
                }
        }
    }

    public function posting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $lokasijurnal = "HO";
        $cek_company = Auth()->user()->kode_company;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $pemakaian = Pemakaian::on($konek)->find(request()->id);
        if ($pemakaian->tanggal_pemakaian != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Pemakaian berbeda, Posting pemakaian hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }
        
        if($cek_bulan == null || $level == 'superadministrator' || $level == 'rince_pbm' || $level == 'user_thomas' || $level == 'merisa_pbm' || $level == 'merisa_cabang'){
            if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                $pemakaiandetail = PemakaianDetail::on($konek)->where('no_pemakaian', request()->id)->get();
                $leng = count($pemakaiandetail);
                $data = array();

                $kat1 = 0;
                foreach ($pemakaiandetail as $rowdata){
                    $cek_produk = Produk::on($konek)->where('id', $rowdata->kode_produk)->first();
                    $cek_kategori = KategoriProduk::where('kode_kategori',$cek_produk->kode_kategori)->first();

                    if ($cek_company == '04') {
                        if ($cek_kategori->coa_gut == null || $cek_kategori->coabiaya_gut == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '0401') {
                        if ($cek_kategori->coa_gutjkt == null || $cek_kategori->coabiaya_gutjkt == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '03') {
                        if ($cek_kategori->coa_emkl == null || $cek_kategori->coabiaya_emkl == null) {
                            $kat1 = 1;
                        }else {
                            if ($pemakaian->cost_center != null){
                                $cekcoa = Coa::find($cek_kategori->coabiaya_emkl);
                                if ($cekcoa->cost_center != 'Y'){
                                    $message = [
                                        'success' => false,
                                        'title' => 'Simpan',
                                        'message' => 'Status CC = [FALSE].',
                                    ];
                                    return response()->json($message);
                                }
                            }
                        }
                    }else if ($cek_company == '05') {
                        if ($cek_kategori->coa_sub == null || $cek_kategori->coabiaya_sub == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '02') {
                        if ($cek_kategori->coa_pbm == null || $cek_kategori->coabiaya_pbm == null) {
                            $kat1 = 1;
                        }
                    }else if ($cek_company == '06') {
                        if ($cek_kategori->coa_infra == null || $cek_kategori->coabiaya_infra == null) {
                            $kat1 = 1;
                        }
                    }

                    if ($kat1 == 1) {
                        $message = [
                            'success' => false,
                            'title' => 'Simpan',
                            'message' => 'Kategori: '.$cek_kategori->kode_kategori.' belum memiliki COA Persediaan / Biaya, silahkan lengkapi terlebih dahulu.',
                        ];
                        return response()->json($message);
                    }
                }
            }
            
            $pemakaian = Pemakaian::on($konek)->find(request()->id);
            $cek_status = $pemakaian->status;
            if($cek_status != 'OPEN'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'POST No. Pemakaian: '.$pemakaian->no_pemakaian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
                ];
                return response()->json($message);
            }
            
            $pemakaian->status = 'ONGOING';
            $pemakaian->save();

            $no_pemakaian = $pemakaian->no_pemakaian;
            $crate_pemakaian = $pemakaian->created_at;
            $koneksi = $pemakaian->kode_lokasi;

            $tgl = $pemakaian->tanggal_pemakaian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

            $validate = $this->periodeChecker($tgl);
            if($validate != true){  
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Data gagal di POSTING, re-open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];
                return response()->json($message);
            }

            // $validate_produk = $this->produkChecker($no_pemakaian, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaian, $koneksi);
            // dd($validate_produk);
            
            $validate_produk = 'true';
            if($validate_produk == true){
                $pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', request()->id)->get();
                $no_pemakaian = request()->id;
                $data = array();
                
                $pemakaian->status = 'ONGOING1';
                $pemakaian->save();

                foreach ($pemakaiandetail as $rowdata){
                    $data[] = array(
                          'no_pemakaian'=>$no_pemakaian,
                          'kode_produk'=>$rowdata->kode_produk,
                          'kode_satuan'=>$rowdata->kode_satuan,
                          'qty'=>$rowdata->qty,
                          'partnumber'=>$rowdata->partnumber,
                          'harga'=>$rowdata->harga,
                    );
                    
                    if($pemakaian->no_wo != null){
                        $woheader = Workorder::on($konek)->find($pemakaian->no_wo);
                        $wodetail = WorkorderDetail::on($konek)->where('no_wo',$pemakaian->no_wo)->where('kode_produk',$rowdata->kode_produk)->first();
                        
                        if($wodetail != null){
                            $wodetail->qty_pakai = $wodetail->qty_pakai + $rowdata->qty;
                            if ($wodetail->qty_pakai >= $wodetail->qty){
                                $wodetail->status_produk = "ON";
                            }
                            
                            $wodetail->save();
                        }
                        
                        
                        // $getstatus = WorkorderDetail::on($konek)->where('no_wo',$pemakaian->no_wo)->where('status_produk','OFF')->first();
                        // if($getstatus == null){
                        //     $woheader->status = "CLOSED";
                        //     $woheader->save();
                        // }
                    }
                    
                    $konversi = Konversi::on($konek)->where('kode_produk', $rowdata->kode_produk)->where('kode_satuan', $rowdata->kode_satuan)->first();
                    $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$rowdata->kode_produk)->where('kode_lokasi',$koneksi)->where('partnumber',$rowdata->partnumber)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                    $ended = $tb_item_bulanan->ending_stock - ($rowdata->qty * $konversi->nilai_konversi);
                    if ($ended < 0){
                        $produk = Produk::on($konek)->find($rowdata->kode_produk);
                        $message = [
                            'success' => false,
                            'title' => 'Update',
                            'message' => 'Gagal Post!! Produk ['.$rowdata->kode_produk.'] '.$produk->nama_produk.' Stok tidak cukup, perbarui detail pemakaian.'
                        ];
                        return response()->json($message);
                    }
                }

                if(!empty($pemakaiandetail)){
                    $leng = count($pemakaiandetail);

                    $i = 0;
                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                            // dd($data[0]['kode_produk']);
                        if($tb_item_bulanan != null){
                            $produk_awal = $tb_item_bulanan->kode_produk;

                            $stock_begin = $tb_item_bulanan->begin_stock;
                            $amount_begin = $tb_item_bulanan->begin_amount;
                            $stok_in = $tb_item_bulanan->in_stock;
                            $stok_ending = $tb_item_bulanan->ending_stock;
                            $amount_masuk = $tb_item_bulanan->in_amount;
                            $amount = $tb_item_bulanan->ending_amount;
                            $stock_out = $tb_item_bulanan->out_stock;
                            $outamount_awal_1 = $tb_item_bulanan->out_amount;
                            $amount_sale = $tb_item_bulanan->sale_amount;              
                            $amount_trfin = $tb_item_bulanan->trf_in_amount;
                            $amount_trfout = $tb_item_bulanan->trf_out_amount;
                            $amount_adj = $tb_item_bulanan->adjustment_amount;
                            $amount_op = $tb_item_bulanan->amount_opname;
                            $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                            $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                            $amount_dis = $tb_item_bulanan->disassembling_amount;
                            $amount_ass = $tb_item_bulanan->assembling_amount;
                            $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

                                // $hpp = $tb_item_bulanan->hpp;
                            $hpp_real = $amount/$stok_ending;

                            $produk = Produk::on($konek)->find($data[$i]['kode_produk']);
                            $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                            $update_harga = [
                                'harga'=>$hpp_real,
                            ];

                            // $pemakaiandetail_update = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->update($update_harga);

                            $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();
                            $hpp_new = $pemakaiandetail2->harga;

                            $konversi = Konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();
                            $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                            $waktu = $tgl;
                            $barang = $data[$i]['kode_produk'];
                            $stok_masuk = $stok_in;
                            $stok_keluar = $stock_out + $qty_baru;
                            $amount_keluar = $outamount_awal_1 + ($hpp_new*$qty_baru);
                            $end_stok = $stok_ending - $qty_baru;
                            $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount - $amount_dis + $amount_ass + $amount_rpk;

                            if($end_stok != 0){
                                $hpp2 = $end_amount / $end_stok;
                            }else{
                                $hpp2 = $tb_item_bulanan->hpp;
                                $end_amount = 0;
                            }

                            $tabel_baru = [
                                'out_stock'=>$stok_keluar,
                                'out_amount'=>$amount_keluar,
                                'ending_stock'=>$end_stok,
                                'ending_amount'=>$end_amount,
                                'hpp'=>$hpp2,
                            ];

                            if($end_stok < 0){
                                $message = [
                                    'success' => false,
                                    'title' => 'Update',
                                    'message' => 'Data gagal di POSTING, silahkan lakukan Penerimaan pada [Bulan '.$bulan.'; Tahun '.$tahun.'] terlebih dahulu. Stok saat ini tidak cukup untuk di pakai.'
                                ];
                                return response()->json($message);
                            }

                            $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                            $tabel_history = [
                                'kode_produk'=>$barang,
                                'no_transaksi'=>$no_pemakaian,
                                'tanggal_transaksi'=>$waktu,
                                'jam_transaksi'=>$crate_pemakaian,
                                'qty_transaksi'=>0-$qty_baru,
                                'harga_transaksi'=>$hpp_new,
                                'total_transaksi'=>0-($hpp_new*$qty_baru),
                                'kode_lokasi'=>$koneksi,
                            ];

                            $update_produk_history = tb_produk_history::on($konek)->create($tabel_history);
                            $tgl_pakai1 = $pemakaian->tanggal_pemakaian;
                            $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                            $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                            $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                            $status_reopen = $reopen->reopen_status;

                            if($status_reopen == 'true'){
                                $tgl_pakai = $pemakaian->tanggal_pemakaian;
                                $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                                $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

                                $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                                $periode_berjalan = $tb_akhir_bulan2->periode;

                                $datetime1 = new DateTime($periode_berjalan);
                                $datetime2 = new DateTime($tanggal_baru);
                                $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                                $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                                $year1 = Carbon\Carbon::parse($periode_berjalan)->format('Y');
                                $year2 = Carbon\Carbon::parse($tanggal_baru)->format('Y');

                                    //convert
                                $timeStart = strtotime($tanggal_baru);
                                $timeEnd = strtotime($periode_berjalan);

                                    // Menambah bulan ini + semua bulan pada tahun sebelumnya
                                $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                                    // hitung selisih bulan
                                $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                                $final_month = $numBulan;

                                $bulan3 = 0;
                                $j = 1;
                                while($j <= $final_month){
                                    $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                                    $tahun_kemarin = $tahun_berjalan - 1;

                                    $bulancek = $bulan + $j;
                                    if($bulancek >= 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                        $bulan3 += 1;
                                        $bulan2 = strval($bulan3);
                                        $tahun2 = strval($tahun_berjalan);
                                    }else if($bulancek < 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                        $bulan2 = strval($bulancek);
                                        $tahun2 = strval($tahun_kemarin);
                                    }else{
                                        $bulan2 = strval($bulancek);
                                        $tahun2 = strval($tahun_berjalan);
                                    }

                                    $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                    if($tb_item_bulanan2 != null){
                                        $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                        $bs = $tb_item_bulanan2->begin_stock;
                                        $ba = $tb_item_bulanan2->begin_amount;
                                        $es = $tb_item_bulanan2->ending_stock;
                                        $ea = $tb_item_bulanan2->ending_amount;

                                        $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                        $hpp_new2 = $pemakaiandetail2->harga;

                                        $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                                        $amount_o = $hpp_new2*$stock_o;

                                        $begin_stock1 = $bs - $stock_o;
                                        $begin_amount1 = $ba - $amount_o;

                                        $end_stok1 = $es - $stock_o;
                                        $end_amount1 = $ea - $amount_o;

                                        if($end_stok1 != 0){
                                            $hpp = $end_amount1 / $end_stok1;
                                        }else{
                                            $hpp = $tb_item_bulanan2->hpp;
                                            $end_amount1 = 0;
                                        }

                                        $tabel_baru2 = [
                                            'begin_stock'=>$begin_stock1,
                                            'begin_amount'=>$begin_amount1,
                                            'ending_stock'=>$end_stok1,
                                            'ending_amount'=>$end_amount1,
                                            'hpp'=>$hpp,
                                        ];

                                        $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                    }

                                    $j++;
                                }
                            }

                        }

                        else
                        {
                            alert()->success('Post', 'GAGAL!')->persistent('Close');
                            return redirect()->back();
                        }
                    }
                }

                $pemakaian = Pemakaian::on($konek)->find(request()->id);
                $pemakaian->status = "POSTED";
                $pemakaian->save(); 
                
                // if($pemakaian->no_wo != null){
                //     $workorder = Workorder::on($konek)->find($pemakaian->no_wo);
                //     $workorder->status = 'CLOSED';
                //     $workorder->save();
                // }
                
                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Post No. Pemakaian: '.$no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);
                

                //UPDATE LEDGER JURNAL
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();
                    $cek_company = Auth()->user()->kode_company;

                    $total_qty = 0;
                    $total_harga = 0;
                    $grand_total = 0;
                    // $detail = PemakaianDetail::on($konek)->where('no_pemakaian',$pemakaian->no_pemakaian)->get();
                    if ($cek_company == '04' || $cek_company == '0401'){
                        $compan = 'u5611458_gui_inventory_gut_laravel';
                    }else if ($cek_company == '03'){
                        $compan = 'u5611458_gui_inventory_emkl_laravel';
                    }else if ($cek_company == '02'){
                        $compan = 'u5611458_gui_inventory_pbm_laravel';
                    }else if ($cek_company == '05'){
                        $compan = 'u5611458_gui_inventory_sub_laravel';
                    }else if ($cek_company == '01'){
                        $compan = 'u5611458_gui_inventory_depo_laravel';
                    }else if ($cek_company == '06'){
                        $compan = 'u5611458_gui_inventory_pbminfra_laravel';
                    }

                    $detail = KategoriProduk::join($compan.'.produk','kategori_produk.kode_kategori','=',$compan.'.produk.kode_kategori')->join($compan.'.pemakaian_detail',$compan.'.produk.id','=',$compan.'.pemakaian_detail.kode_produk')->where($compan.'.pemakaian_detail.no_pemakaian', $no_pemakaian)->groupBy('kategori_produk.kode_kategori')->get();
                    foreach ($detail as $row){
                        $total_qty += $row->qty;
                        $subtotal = $row->harga * $row->qty;
                        $total_harga += $subtotal;

                        $totalhpp = PemakaianDetail::on($konek)->select(DB::raw('SUM('.$compan.'.pemakaian_detail.qty *'.$compan.'.pemakaian_detail.harga) as total'))->join($compan.'.produk',$compan.'.pemakaian_detail.kode_produk','=',$compan.'.produk.id')->where($compan.'.pemakaian_detail.no_pemakaian', $no_pemakaian)->where($compan.'.produk.kode_kategori', $row->kode_kategori)->first();
                        $totalhpp = $totalhpp->total;
                        $grand_total += $totalhpp;

                        $kategori = KategoriProduk::where('kode_kategori', $row->kode_kategori)->first();

                        if ($cek_company == '04'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                            $cc_inv = $kategori->cc_gut_persediaan;
                            $cc_biaya = $kategori->cc_gut;
                        }else if ($cek_company == '0401'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                            $cc_inv = $kategori->cc_gutjkt_persediaan;
                            $cc_biaya = $kategori->cc_gutjkt;
                        }else if ($cek_company == '03'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                            $cc_inv = $kategori->cc_emkl_persediaan;
                            $cc_biaya = $kategori->cc_emkl;
                        }else if ($cek_company == '02'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                            $cc_inv = $kategori->cc_pbm_persediaan;
                            $cc_biaya = $kategori->cc_pbm;
                        }else if ($cek_company == '01'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                            $cc_inv = $kategori->cc_depo_persediaan;
                            $cc_biaya = $kategori->cc_depo;
                        }else if ($cek_company == '05'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                            $cc_inv = $kategori->cc_sub_persediaan;
                            $cc_biaya = $kategori->cc_sub;
                        }else if ($cek_company == '06'){
                            $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                            $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
                            $cc_inv = $kategori->cc_infra_persediaan;
                            $cc_biaya = $kategori->cc_infra;
                        }

                        $cek_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        if ($cek_balance == null) {
                            //CEK SEBELUM
                            $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
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

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
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

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                            }

                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance - $totalhpp;
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
                        }else{
                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance - $totalhpp;
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

                                        $update_balance = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_inventory->account,
                            'cost_center'=>$cc_inv,
                            'no_journal'=>$pemakaian->no_journal,
                            'journal_date'=>$pemakaian->tanggal_pemakaian,
                            'db_cr'=>'K',
                            'reference'=>$pemakaian->no_pemakaian,
                            'kredit'=>$totalhpp,
                            'kode_lokasi'=>$lokasijurnal,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $pemakaian;
                        $tgl_trans = $pemakaian->tanggal_pemakaian;
                        $harga_acc = $totalhpp;
                        $dbkr = 'K';
                        $update_accbalance = $this->accbalance_kredit_post($coa_inventory, $harga_acc, $lokasijurnal, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_inventory, $harga_acc, $lokasijurnal, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);



                        $cek_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                        if ($cek_balance == null) {
                            //CEK SEBELUM
                            $cek_sebelum = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($bulan - 1))->whereYear('periode', $tahun)->first();
                            if($cek_sebelum != null){
                                $update_acc = [
                                    'periode'=>$tanggal_baru,
                                    'fiscalyear'=>$tahun,
                                    'account'=>$coa_biaya->account,
                                    'beginning_balance'=>$cek_sebelum->ending_balance,
                                    'debet'=>0,
                                    'kredit'=>0,
                                    'ending_balance'=>$cek_sebelum->ending_balance,
                                    'kode_lokasi'=>$lokasijurnal,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                            }else{
                                $update_acc = [
                                    'periode'=>$tanggal_baru,
                                    'fiscalyear'=>$tahun,
                                    'account'=>$coa_biaya->account,
                                    'beginning_balance'=>0,
                                    'debet'=>0,
                                    'kredit'=>0,
                                    'ending_balance'=>0,
                                    'kode_lokasi'=>$lokasijurnal,
                                ];

                                $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->create($update_acc);
                            }

                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance + $totalhpp;
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

                                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }else{
                            //CEK SETELAH
                            $i = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($i = $bulan; $i <= 12; $i++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                                    if($cek_setelah != null){
                                        $begin = $cek_setelah->beginning_balance + $totalhpp;
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

                                        $update_balance = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasijurnal)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }

                        $update_ledger = [
                            'tahun'=>$tahun,
                            'periode'=>$bulan,
                            'account'=>$coa_biaya->account,
                            'cost_center'=>$pemakaian->cost_center,
                            'no_journal'=>$pemakaian->no_journal,
                            'journal_date'=>$pemakaian->tanggal_pemakaian,
                            'db_cr'=>'D',
                            'reference'=>$pemakaian->no_pemakaian,
                            'debit'=>$totalhpp,
                            'kode_lokasi'=>$lokasijurnal,
                        ];
                        $update = Ledger::on($konek2)->create($update_ledger);

                        $type = 'Inventory';
                        $transaksi = $pemakaian;
                        $tgl_trans = $pemakaian->tanggal_pemakaian;
                        $harga_acc = $totalhpp;
                        $dbkr = 'D';
                        $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasijurnal, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                        $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasijurnal, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
                    }
                    
                }
                
                $konversi_simbol = PemakaianDetail::on($konek)->where('keterangan', 'LIKE', '%&%')->update(['keterangan' => DB::raw("REPLACE(keterangan,  '&', 'DAN')")]);
                $konversi = Pemakaian::on($konek)->where('deskripsi', 'LIKE', '%&%')->update(['deskripsi' => DB::raw("REPLACE(deskripsi,  '&', 'DAN')")]);
                        
                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di POST.'
                ];

                return response()->json($message);

            }else{
                $message = [
                    'success' => false,
                    'title' => 'Update',
                    'message' => 'Re-Open [Bulan '.$bulan.'; Tahun '.$tahun.'].'
                ];

                return response()->json($message);
            }
        }
        else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses posting data',
            ];
            return response()->json($message);
        }
        
    }


    public function unposting()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        $bans = Pemakaian::on($konek)->find(request()->id);
        if ($bans->tanggal_pemakaian != $today) {
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal hari ini: '.$today.' Tanggal Pemakaian berbeda, Unposting pemakaian hanya dapat dilakukan di hari yang sama.',
            ];
            return response()->json($message);
        }

        if($cek_bulan == null || $level == 'superadministrator' || $level == 'rince_pbm' || $level == 'user_thomas' || $level == 'merisa_pbm' || $level == 'merisa_cabang'){
            $pemakaian = Pemakaian::on($konek)->find(request()->id);
            $cek_status = $pemakaian->status;
            if($cek_status != 'POSTED'){  
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'UNPOST No. Pemakaian: '.$pemakaian->no_pemakaian.' sudah dilakukan! Pastikan Anda tidak membuka menu PEMAKAIAN lebih dari 1',
                ];
                return response()->json($message);
            }

            $no_pemakaian = $pemakaian->no_pemakaian;
            $koneksi = $pemakaian->kode_lokasi;

            $tgl = $pemakaian->tanggal_pemakaian;
            $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $tanggal = '01';

            $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();
            $validate = $this->periodeChecker($tgl);

            if($level != 'user_rince' && $level != 'superadministrator' && $level != 'user_thomas'){
                $cekopen = Pemakaian::on($konek)->where('status','OPEN')->whereMonth('tanggal_pemakaian', $bulan)->whereYear('tanggal_pemakaian', $tahun)->where('kode_lokasi', $koneksi)->first();
                if ($cekopen != null){
                    $message = [
                            'success' => false,
                            'title' => 'Gagal',
                            'message' => 'UNPOST No. Pemakaian: '.$pemakaian->no_pemakaian.' gagal, karena masih ada pemakaian OPEN.',
                    ];
                    return response()->json($message);
                }
            }

            // $validate_produk = $this->produkChecker2($no_pemakaian, $tahun, $bulan, $tanggal_baru, $tgl, $pemakaian, $koneksi);

            if($validate == true){
                $pemakaiandetail = PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian', request()->id)->get();
                $no_pemakaian = request()->id;
                $data = array();

                
                foreach ($pemakaiandetail as $rowdata){
                    $data[] = array(
                        'no_pemakaian'=>$no_pemakaian,
                        'kode_produk'=>$rowdata->kode_produk,
                        'kode_satuan'=>$rowdata->kode_satuan,
                        'qty'=>$rowdata->qty,
                        'partnumber'=>$rowdata->partnumber,
                    );  
                    if($pemakaian->no_wo != null){
                        $wodetail = WorkorderDetail::on($konek)->where('no_wo',$pemakaian->no_wo)->where('kode_produk',$rowdata->kode_produk)->first();
                        // $woheader = Workorder::on($konek)->find($pemakaian->no_wo);
                        $wodetail->qty_pakai = $wodetail->qty_pakai - $rowdata->qty;
                        $wodetail->status_produk='OFF';
                        // $woheader->status='POSTED';
                        // $woheader->save();
                        $wodetail->save();  
                        
                    
                    }
                }
                

                if(!empty($pemakaiandetail)){
                    $leng = count($pemakaiandetail);

                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        $tb_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->first();
                                
                        $produk_awal = $tb_item_bulanan->kode_produk;

                        $stock_begin = $tb_item_bulanan->begin_stock;
                        $amount_begin = $tb_item_bulanan->begin_amount;
                        $amount_akhir1 = $tb_item_bulanan->ending_amount;

                        $stok_in = $tb_item_bulanan->in_stock;
                        $stok_akhir = $tb_item_bulanan->ending_stock;

                        $amount_masuk = $tb_item_bulanan->in_amount;
                        $amount = $tb_item_bulanan->ending_amount;

                        $outstok_awal_1 = $tb_item_bulanan->out_stock;
                        $outamount_awal_1 = $tb_item_bulanan->out_amount;
                        $amount_sale = $tb_item_bulanan->sale_amount;

                        $amount_trfin = $tb_item_bulanan->trf_in_amount;
                        $amount_trfout = $tb_item_bulanan->trf_out_amount;
                                
                        $amount_adj = $tb_item_bulanan->adjustment_amount;
                        $amount_op = $tb_item_bulanan->amount_opname;

                        $retur_beli_amount = $tb_item_bulanan->retur_beli_amount;
                        $retur_jual_amount = $tb_item_bulanan->retur_jual_amount;
                            
                        $amount_dis = $tb_item_bulanan->disassembling_amount;
                        $amount_ass = $tb_item_bulanan->assembling_amount;
                        
                        $amount_rpk = $tb_item_bulanan->retur_pakai_amount;

                        $produk = Produk::on($konek)->find($data[$i]['kode_produk']);

                        $pemakaiandetail = PemakaianDetail::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->where('no_pemakaian',$no_pemakaian)->first();

                        $hpp = $pemakaiandetail->harga;

                        $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                        $qty_baru = $data[$i]['qty']*$konversi->nilai_konversi;

                        $stok_masuk = $stok_in;
                        $stok_keluar = $outstok_awal_1 - $qty_baru;
                        $amount_keluar = $outamount_awal_1 - ($hpp*$qty_baru);
                        $end_stok = $stok_akhir + $qty_baru;
                        $end_amount = $amount_begin + $amount_masuk - $amount_keluar - $amount_sale + $amount_trfin - $amount_trfout + $amount_adj + $amount_op - $retur_beli_amount + $retur_jual_amount - $amount_dis + $amount_ass + $amount_rpk;

                        if($end_stok != 0){
                            $hpp = $end_amount / $end_stok;
                        }else{
                            $hpp = $tb_item_bulanan->hpp;
                            $end_amount = 0;
                        }

                        $tabel_baru = [
                            'in_stock'=>$stok_masuk,
                            'out_stock'=>$stok_keluar,
                            'out_amount'=>$amount_keluar,
                            'ending_stock'=>$end_stok,
                            'ending_amount'=>$end_amount,
                            'hpp'=>$hpp,
                        ];

                        $update_produk_history = tb_produk_history::on($konek)->where('no_transaksi',$no_pemakaian)->delete();

                        $update_item_bulanan = tb_item_bulanan::on($konek)->where('kode_produk',$produk_awal)->where('partnumber',$data[$i]['partnumber'])->where('kode_lokasi',$koneksi)->whereMonth('periode', $bulan)->whereYear('periode', $tahun)->update($tabel_baru);

                        $tgl_pakai1 = $pemakaian->tanggal_pemakaian;
                        $tahun_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->year;
                        $bulan_transaksi1 = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai1)->month;

                        $reopen = tb_akhir_bulan::on($konek)->whereMonth('periode', $bulan_transaksi1)->whereYear('periode', $tahun_transaksi1)->first();
                        $status_reopen = $reopen->reopen_status;

                        if($status_reopen == 'true'){
                            $tgl_pakai = $pemakaian->tanggal_pemakaian;
                            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->year;
                            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl_pakai)->month;

                            $tb_akhir_bulan2 = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
                            $periode_berjalan = $tb_akhir_bulan2->periode;

                            $datetime1 = new DateTime($periode_berjalan);
                            $datetime2 = new DateTime($tanggal_baru);
                            $month1 = Carbon\Carbon::parse($periode_berjalan)->format('m');
                            $month2 = Carbon\Carbon::parse($tanggal_baru)->format('m');
                            $year1 = Carbon\Carbon::parse($periode_berjalan)->format('Y');
                            $year2 = Carbon\Carbon::parse($tanggal_baru)->format('Y');

                            //convert
                            $timeStart = strtotime($tanggal_baru);
                            $timeEnd = strtotime($periode_berjalan);
                                                                             
                            // Menambah bulan ini + semua bulan pada tahun sebelumnya
                            $numBulan = (date("Y",$timeEnd)-date("Y",$timeStart))*12;
                            // hitung selisih bulan
                            $numBulan += date("m",$timeEnd)-date("m",$timeStart);
                            $final_month = $numBulan;

                            $bulan3 = 0;
                            $j = 1;
                            while($j <= $final_month){
                                $pemakaiandetail2 = PemakaianDetail::on($konek)->where('no_pemakaian', $no_pemakaian)->where('kode_produk',$data[$i]['kode_produk'])->where('partnumber',$data[$i]['partnumber'])->first();

                                $hpp = $pemakaiandetail2->harga;
                                $konversi = konversi::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_satuan',$data[$i]['kode_satuan'])->first();

                                $stock_o = $data[$i]['qty']*$konversi->nilai_konversi;
                                $amount_o = $hpp*$stock_o;

                                $tahun_berjalan = Carbon\Carbon::createFromFormat('Y-m-d',$periode_berjalan)->year;
                                $tahun_kemarin = $tahun_berjalan - 1;

                                $bulancek = $bulan + $j;
                                if($bulancek >= 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                    $bulan3 += 1;
                                    $bulan2 = strval($bulan3);
                                    $tahun2 = strval($tahun_berjalan);
                                }else if($bulancek < 13 && $tahun_transaksi == strval($tahun_kemarin)){
                                    $bulan2 = strval($bulancek);
                                    $tahun2 = strval($tahun_kemarin);
                                }else{
                                    $bulan2 = strval($bulancek);
                                    $tahun2 = strval($tahun_berjalan);
                                }

                                $tb_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->first();

                                if($tb_item_bulanan2 != null){
                                    $bs = $tb_item_bulanan2->begin_stock;
                                    $ba = $tb_item_bulanan2->begin_amount;
                                    $es = $tb_item_bulanan2->ending_stock;
                                    $ea = $tb_item_bulanan2->ending_amount;

                                    $begin_stock1 = $bs + $stock_o;
                                    $begin_amount1 = $ba + $amount_o;

                                    $end_stok1 = $es + $stock_o;
                                    $end_amount1 = $ea + $amount_o;

                                    if($end_stok1 != 0){
                                        $hpp = $end_amount1 / $end_stok1;
                                    }else{
                                        $hpp = $tb_item_bulanan2->hpp;
                                        $end_amount1 = 0;
                                    }

                                    $tabel_baru2 = [
                                        'begin_stock'=>$begin_stock1,
                                        'begin_amount'=>$begin_amount1,
                                        'ending_stock'=>$end_stok1,
                                        'ending_amount'=>$end_amount1,
                                        'hpp'=>$hpp,
                                    ];
                                    // dd($tabel_baru2);

                                    $update_item_bulanan2 = tb_item_bulanan::on($konek)->where('kode_produk',$data[$i]['kode_produk'])->where('kode_lokasi',$koneksi)->where('partnumber',$data[$i]['partnumber'])->whereMonth('periode',$bulan2)->whereYear('periode', $tahun2)->update($tabel_baru2);
                                }
                                $j++;
                            }
                        }
                    }
                }
                     
                $pemakaian = Pemakaian::on($konek)->find(request()->id);
                $pemakaian->status = "OPEN";
                $pemakaian->save(); 

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Unpost No. Pemakaian: '.$no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                user_history::on($konek)->create($tmp);
                
                //UPDATE JURNAL
                $cek_company = Auth()->user()->kode_company;
                if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05' || $cek_company == '06' || $cek_company == '02'){
                    $konek2 = self::konek2();

                    $get_ledger = Ledger::on($konek2)->where('no_journal',$pemakaian->no_journal)->get();

                    $data = array();

                    foreach ($get_ledger as $rowdata){

                        $account = $rowdata->account;
                        $db_cr = $rowdata->db_cr;
                        $debit = $rowdata->debit;
                        $kredit = $rowdata->kredit;
                                
                        $data[] = array(
                            'account'=>$account,
                            'db_cr'=>$db_cr,
                            'debit'=>$debit,
                            'kredit'=>$kredit,
                        );
                    }

                    $leng = count($get_ledger);

                    $i = 0;

                    for($i = 0; $i < $leng; $i++){
                        if($data[$i]['db_cr'] == 'D'){
                            $account = $data[$i]['account'];
                            $harga = $data[$i]['debit'];

                            $type = 'Inventory';
                            $transaksi = $pemakaian;
                            $tgl_trans = $pemakaian->tanggal_pemakaian;
                            $update_accbalance = $this->accbalance_debit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();
                            $update_lrb = $this->lrb_unpost($cek_acc, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $data[$i]['db_cr']);

                            //CEK SETELAH
                            $j = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($j = $bulan; $j <= 12; $j++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                                    if ($cek_setelah != null) {
                                        $debit = $cek_setelah->debet;
                                        $kredit = $cek_setelah->kredit;
                                        if($cek_acc->normal_balance == 'D'){
                                            $begin = $cek_setelah->beginning_balance - $harga;
                                            $ending_balance = $begin + $debit - $kredit;
                                        }else{
                                            $begin = $cek_setelah->beginning_balance + $harga;
                                            $ending_balance = $begin - $debit + $kredit;
                                        }
                                         
                                        $tabel_baru = [
                                            'beginning_balance'=>$begin,
                                            'ending_balance'=>$ending_balance,
                                        ];
                                        
                                        $update_balance = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }else{
                            $account = $data[$i]['account'];
                            $harga = $data[$i]['kredit'];

                            $type = 'Inventory';
                            $transaksi = $pemakaian;
                            $tgl_trans = $pemakaian->tanggal_pemakaian;
                            $update_accbalance = $this->accbalance_kredit_unpost($account, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                            $cek_acc = Coa::on('mysql4')->where('account',$account)->first();
                            $update_lrb = $this->lrb_unpost($cek_acc, $harga, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $data[$i]['db_cr']);

                            //CEK SETELAH
                            $j = $bulan;
                            $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                            if ($cek_setelah != null) {
                                for($j = $bulan; $j <= 12; $j++){
                                    $cek_setelah = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->first();
                                    if ($cek_setelah != null) {
                                        $debit = $cek_setelah->debet;
                                        $kredit = $cek_setelah->kredit;
                                        if($cek_acc->normal_balance == 'D'){
                                            $begin = $cek_setelah->beginning_balance + $harga;
                                            $ending_balance = $begin + $debit - $kredit;
                                        }else{
                                            $begin = $cek_setelah->beginning_balance - $harga;
                                            $ending_balance = $begin - $debit + $kredit;
                                        }
                                         
                                        $tabel_baru = [
                                            'beginning_balance'=>$begin,
                                            'ending_balance'=>$ending_balance,
                                        ];
                                            
                                        $update_balance = AccBalance::on($konek2)->where('account',$account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($j + 1))->whereYear('periode', $tahun)->update($tabel_baru);
                                    }
                                }
                            }
                        }
                    }

                    $update_ledger = Ledger::on($konek2)->where('no_journal',$pemakaian->no_journal)->delete();
                }


                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data berhasil di UNPOST.'
                ];

                return response()->json($message);

            }
        }else{
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Anda tidak mempunyai akses unposting data',
            ];
            return response()->json($message);
        }
        
    }
    
    //RE-POSTING UNTUK UPDATE JURNAL SAJA, TIDAK MERUBAH STOK PADA INVENTORY
    public function postingulang()
    {
        $konek = self::konek();
        $level = auth()->user()->level;
        $lokasi = auth()->user()->kode_lokasi;
        $cek_company = Auth()->user()->kode_company;
        $cek_bulan = tb_akhir_bulan::on($konek)->where('status_periode','Disable')->first();
        $pemakaian = Pemakaian::on($konek)->find(request()->id);

        $tgl = $pemakaian->tanggal_pemakaian;
        $tahun = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
        $bulan = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
        $tanggal = '01';

        $tanggal_baru = Carbon\Carbon::createFromDate($tahun, $bulan, $tanggal)->toDateString();

        //UPDATE JURNAL
        if($cek_company == '04' || $cek_company == '0401' || $cek_company == '03' || $cek_company == '05'){
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
                    $data[] = array(
                       'kode_produk'=>$rowdata->kode_produk,
                       'qty'=>$rowdata->qty,
                       'harga'=>$rowdata->harga,
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
            $gt_sldg = 0;

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
                
                if($cek_produk->kode_kategori == 'SLDG'){
                    $gt_sldg += $data[$i]['qty'] * $data[$i]['harga'];
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'APD')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BAN')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'BBM')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'OLI')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SPRT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'UNIT')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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
            
            if($gt_sldg > 0){
                if ($cek_company == '04') {
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gut)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gut)->first();
                }else if($cek_company == '0401'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_gutjkt)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_gutjkt)->first();
                }else if($cek_company == '03'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_emkl)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_emkl)->first();
                }else if($cek_company == '02'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_pbm)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_pbm)->first();
                }else if($cek_company == '01'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_depo)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_depo)->first();
                }else if($cek_company == '05'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_sub)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_sub)->first();
                }else if($cek_company == '06'){
                    $kategori = KategoriProduk::where('kode_kategori', 'SLDG')->first();
                    $coa_inventory = Coa::where('kode_coa', $kategori->coa_infra)->first();
                    $coa_biaya = Coa::where('kode_coa', $kategori->coabiaya_infra)->first();
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

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_sldg;
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
                }else{
                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_inventory->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance - $gt_sldg;
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
                    'kredit'=>$gt_sldg,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_sldg;
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

                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                }else{
                    //CEK SETELAH
                    $i = $bulan;
                    $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                    if ($cek_setelah != null) {
                        for($i = $bulan; $i <= 12; $i++){
                            $cek_setelah = AccBalance::on($konek2)->where('account',$coa_biaya->account)->where('kode_lokasi',$lokasi)->whereMonth('periode', ($i + 1))->whereYear('periode', $tahun)->first();
                            if($cek_setelah != null){
                                $begin = $cek_setelah->beginning_balance + $gt_sldg;
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
                    'debit'=>$gt_sldg,
                    'kode_lokasi'=>$lokasi,
                ];
                $update = Ledger::on($konek2)->create($update_ledger);

                $type = 'Inventory';
                $transaksi = $pemakaian;
                $tgl_trans = $pemakaian->tanggal_pemakaian;
                $harga_acc = $gt_sldg;
                $dbkr = 'D';
                $update_accbalance = $this->accbalance_debit_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans);
                $update_lrb = $this->lrb_post($coa_biaya, $harga_acc, $lokasi, $bulan, $tahun, $type, $transaksi, $tgl_trans, $tanggal_baru, $dbkr);
            }
        }

        $message = [
            'success' => true,
            'title' => 'Update',
            'message' => 'Data berhasil di POST ULANG.'
        ];

        return response()->json($message);
    }

    public function getmobil()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->where('kode_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_mobil'=>$mobil->no_asset_mobil,
        );
        return response()->json($output);
    }

    public function getalat()
    {   
        $konek = self::konek();
        $alat = Alat::on($konek)->where('kode_alat', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_alat'=>$alat->no_asset_alat,
        );

        return response()->json($output);
    }

    public function getkapal()
    {   
        $konek = self::konek();
        $kapal = Kapal::on($konek)->where('kode_kapal', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_kapal'=>$kapal->no_asset_kapal,
        );

        return response()->json($output);
    }


    public function getmobil2()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->where('kode_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_mobil'=>$mobil->no_asset_mobil,
        );

        return response()->json($output);
    }

    public function getalat2()
    {   
        $konek = self::konek();
        $alat = Alat::on($konek)->where('kode_alat', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_alat'=>$alat->no_asset_alat,
        );

        return response()->json($output);
    }

    public function getkapal2()
    {   
        $konek = self::konek();
        $kapal = Kapal::on($konek)->where('kode_kapal', request()->id)->first();
        // dd($mobil);

        $output = array(
            'no_asset_kapal'=>$kapal->no_asset_kapal,
        );

        return response()->json($output);
    }

    public function getkodealat2()
    {   
        $konek = self::konek();
        $alat = Alat::on($konek)->where('no_asset_alat', request()->id)->first();
        // dd($mobil);

        $output = array(
            'kode_alat'=>$alat->kode_alat,
        );

        return response()->json($output);
    }
    
    public function getkodemobil2()
    {   
        $konek = self::konek();
        $alat = Mobil::on($konek)->where('no_asset_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'nopol'=>$alat->nopol,
        );

        return response()->json($output);
    }
    
    public function getkodekapal2()
    {   
        $konek = self::konek();
        $alat = Kapal::on($konek)->where('no_asset_kapal', request()->id)->first();
        // dd($mobil);

        $output = array(
            'kode_kapal'=>$alat->kode_kapal,
        );

        return response()->json($output);
    }

    public function getnopol()
    {   
        $konek = self::konek();
        $mobil = Mobil::on($konek)->where('no_asset_mobil', request()->id)->first();
        // dd($mobil);

        $output = array(
            'nopol'=>$mobil->nopol,
        );

        return response()->json($output);
    }


    public function store(Request $request)
    {       
        // dd($request);
        $konek = self::konek();
        $tanggal = $request->tanggal_pemakaian;
        $validate = $this->periodeChecker($tanggal);
        // $workorder = Pemakaian::on($konek)->where('no_wo',$request->no_wo)->first();
        
        // if($workorder != null){
        //     $message = [
        //             'success' => false,
        //             'title' => 'Simpan',
        //             'message' => 'No Workorder telah ditarik di nomor pemakaian '.$workorder->no_pemakaian,
        //         ];
        //     return response()->json($message);
        // }
        
        $cekopname = Opname::on($konek)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->first();
            if ($cekopname != null){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Sedang Opname/Ada Transaksi Opname status: OPEN.',
                ];
                return response()->json($message);
            }
        
        $reopen = tb_akhir_bulan::on($konek)->where('reopen_status','true')->first();

        if ($reopen != null){
            $tgl = $reopen->periode;
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl)->month;
            $pakai = Pemakaian::on($konek)->whereMonth('tanggal_pemakaian',$bulan_transaksi)->whereYear('tanggal_pemakaian',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($pakai) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada pemakaian yang OPEN.'
                ];
               return response()->json($message);
            }
        }else {
            $tgl = tb_akhir_bulan::on($konek)->where('status_periode','Open')->first();
            $tahun_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->year;
            $bulan_transaksi = Carbon\Carbon::createFromFormat('Y-m-d',$tgl->periode)->month;
            //$sekarang = date('Y-m-d');
            $pakai = Pemakaian::on($konek)->whereMonth('tanggal_pemakaian',$bulan_transaksi)->whereYear('tanggal_pemakaian',$tahun_transaksi)->where('kode_lokasi', auth()->user()->kode_lokasi)->where('status','OPEN')->get();
            if (count($pakai) >= 1){
                $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Masih ada pemakaian yang OPEN.'
                ];
               return response()->json($message);
            }
        }
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        if ($request->tanggal_pemakaian != $today) {
            $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Tanggal pemakaian berbeda dgn tanggal hari ini.'
            ];
            return response()->json($message);
        }
             
        if($validate == true){
            if ($request->type == 'Other' && $request->deskripsi == ''){
               $message = [
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Deskripsi harus diisi.'
                ];
               return response()->json($message);                        
            }
            else if($request->type == 'Mobil' && $request->no_asset_mobil == ''){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Partnumber harus diisi.'
                ];
                return response()->json($message); 
            }
            else if($request->type == 'Alat' && $request->no_asset_alat == ''){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Partnumber harus diisi.'
                ];
               return response()->json($message); 
            }
            else if($request->type == 'Kapal' && $request->no_asset_kapal == ''){
                $message = [
                    'success' => false,
                    'title' => 'Gagal',
                    'message' => 'Partnumber harus diisi.'
                ];
               return response()->json($message); 
            }
            
            $pemakaian = Pemakaian::on($konek)->create($request->all());
            
            

            $no = Pemakaian::on($konek)->orderBy('created_at','desc')->first();
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Simpan No. Pemakaian: '.$no->no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
            //dd($tmp);
            user_history::on($konek)->create($tmp);

            $message = [
            'success' => true,
            'title' => 'Simpan',
            'message' => 'Data telah di Disimpan.'
            ];
            return response()->json($message);
        }

        else{
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => '<b>Periode</b> ['.$tanggal.'] <b>Telah Ditutup / Belum Dibuka</b>'
            ];
            return response()->json($message);
        }
    }

    public function Showdetail()
    {
        $konek = self::konek();
        $pemakaiandetail= PemakaianDetail::on($konek)->with('produk','satuan')->where('no_pemakaian',request()->id)
        ->orderBy('created_at', 'desc')->get();

        $output = array();

        if($pemakaiandetail){

            foreach($pemakaiandetail as $row)
            {
                $subtotal =  number_format($row->harga * $row->qty,2,",",".");

                $output[] = array(
                    'no_pemakaian'=>$row->no_pemakaian,
                    'produk'=>$row->produk->nama_produk,
                    'partnumber'=>$row->partnumber,
                    'satuan'=>$row->satuan->nama_satuan,
                    'qty'=>$row->qty,
                    'qty_retur'=>$row->qty_retur,
                    'harga'=>$row->harga,
                    'subtotal'=>$subtotal,
                    'keterangan'=>$row->keterangan,
                );
            }
        }else{
            $output = array(
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Maaf Data Terkait Tidak Ada'
            );
        }
        
        return response()->json($output);
    }

    public function edit_pemakaian()
    {   
        $konek = self::konek();
        $no_pemakaian = request()->id;
        $data = Pemakaian::on($konek)->find($no_pemakaian);
        $output = array(
            'no_pemakaian'=> $data->no_pemakaian,
            'type'=> $data->type,
            'tanggal_pemakaian'=> $data->tanggal_pemakaian,
            'pemakai'=> $data->pemakai,
            'kode_mobil'=> $data->kode_mobil,
            'no_asset_mobil'=> $data->no_asset_mobil,
            'kode_alat'=> $data->kode_alat,
            'no_asset_alat'=> $data->no_asset_alat,
            'kode_kapal'=> $data->kode_kapal,
            'no_asset_kapal'=> $data->no_asset_kapal,
            'status'=> $data->status,
            'deskripsi'=> $data->deskripsi,
            'no_jo'=> $data->no_jo,
            'no_wo'=>$data->no_wo,
            'cost_center'=>$data->cost_center,
            'hmkm'=> $data->hmkm,
            'km'=> $data->km,
        );
        return response()->json($output);
    }

    public function updateAjax(Request $request)
    {
        $konek = self::konek();
        $tanggal = $request->tanggal_pemakaian;
        $validate = $this->periodeChecker($tanggal);
        
        $todays = Carbon\Carbon::now();
        $today = $todays->toDateString();
        
        $header = Pemakaian::on($konek)->find($request->no_pemakaian);
        
        if ($header->status != 'OPEN')
        {
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => 'Gagal! Status Pemakaian sudah Posted.',
            ];
            return response()->json($message);
        }
        
        if ($tanggal != $today){
            $message = [
                'success' => false,
                'title' => 'Simpan',
                'message' => 'Tanggal pemakaian berbeda dgn tanggal hari ini.',
            ];
            return response()->json($message);
        }
             
        if($validate == true){
            $datas = $request->all();

            if ($request->type == 'Mobil') {
                $datas['kode_alat'] = '';
                $datas['kode_kapal'] = '';
                $datas['no_asset_alat'] = '';
                $datas['no_asset_kapal'] = '';
            }else if ($request->type == 'Alat') {
                $datas['kode_mobil'] = '';
                $datas['kode_kapal'] = '';
                $datas['no_asset_mobil'] = '';
                $datas['no_asset_kapal'] = '';
            }else if ($request->type == 'Kapal') {
                $datas['kode_alat'] = '';
                $datas['kode_mobil'] = '';
                $datas['no_asset_alat'] = '';
                $datas['no_asset_mobil'] = '';
            }else {
                $datas['kode_alat'] = '';
                $datas['kode_kapal'] = '';
                $datas['kode_mobil'] = '';
                $datas['no_asset_alat'] = '';
                $datas['no_asset_kapal'] = '';
                $datas['no_asset_mobil'] = '';
            }
            
            $Pemakaian = Pemakaian::on($konek)->find($request->no_pemakaian)->update($datas);
            $nama = auth()->user()->name;
            $tmp = ['nama' => $nama,'aksi' => 'Edit No. Pemakaian: '.$request->no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
            //dd($tmp);
            user_history::on($konek)->create($tmp);
              
            $message = [
                'success' => true,
                'title' => 'Update',
                'message' => 'Data telah di Update.'
                ];
            return response()->json($message);
        }
        else{
            $message = [
            'success' => false,
            'title' => 'Simpan',
            'message' => 'Re-Open Periode: '.$tanggal,
            ];
            return response()->json($message);
        }
    }


    public function hapus_pemakaian()
    {
        $konek = self::konek();
        $level = auth()->user()->level;

        $no_pemakaian = request()->id;
        $data = Pemakaian::on($konek)->find($no_pemakaian);
        $tanggal = $data->tanggal_pemakaian;

        $validate = $this->periodeChecker($tanggal);

        if($validate == true){
            $cek_detail = PemakaianDetail::on($konek)->where('no_pemakaian',$no_pemakaian)->first();
            if($cek_detail == null){
                $data->delete();

                $nama = auth()->user()->name;
                $tmp = ['nama' => $nama,'aksi' => 'Hapus No. Pemakaian: '.$no_pemakaian.'.','created_by'=>$nama,'updated_by'=>$nama];
                        //dd($tmp);
                user_history::on($konek)->create($tmp);

                $message = [
                    'success' => true,
                    'title' => 'Update',
                    'message' => 'Data ['.$data->no_pemakaian.'] telah dihapus.'
                ];
                return response()->json($message);
            }
            else if($validate == false){
                $message = [
                    'success' => false,
                    'title' => 'Simpan',
                    'message' => 'Re-Open Periode: '.$tanggal,
                ];
                return response()->json($message);
            }
        }
    }
}
