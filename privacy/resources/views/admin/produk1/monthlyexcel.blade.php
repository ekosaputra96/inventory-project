<table>
    <thead>
        <tr>
            <th colspan="17" style="text-align: center;">Laporan Bulanan {{ $nama_produk }}</th>
        </tr>
        <tr>
            <th width="90px">Periode</th>
            <th width="90px">Kode Produk</th>
            <th width="90px">Nama Produk</th>
            <th width="90px">Part Number</th>
            <th width="90px">Kategori</th>
            <th width="90px">Begin Stock</th>
            @permission('read-hpp')
                <th>Begin Amount</th>
            @endpermission
            @if ($penerimaan == true || $semua == true)
                <th>In Stock</th>
                @permission('read-hpp')
                    <th>In Amount</th>
                @endpermission
            @endif
            @if ($pemakaian == true || $semua == true)
                <th>Out Stock</th>
                @permission('read-hpp')
                    <th>Out Amount</th>
                @endpermission
            @endif
            @if ($penjualan == true || $semua == true)
                <th>Sale Stock</th>
                @permission('read-hpp')
                    <th>Sale Amount</th>
                @endpermission
            @endif
            @if ($transferin == true || $semua == true)
                <th>Trans. In</th>
                @permission('read-hpp')
                    <th>Trans. In Amount</th>
                @endpermission
            @endif
            @if ($transferout == true || $semua == true)
                <th>Trans. Out</th>
                @permission('read-hpp')
                    <th>Trans. Out Amount</th>
                @endpermission
            @endif
            @if ($adjustment == true || $semua == true)
                <th>Adj. Stock</th>
                @permission('read-hpp')
                    <th>Adj. Amount</th>
                @endpermission
            @endif
            @if ($opname == true || $semua == true)
                <th>Stock Opname</th>
                @permission('read-hpp')
                    <th>Amount Opname</th>
                @endpermission
            @endif
            @if ($returbeli == true || $semua == true)
                <th>Retur Beli Stock</th>
                @permission('read-hpp')
                    <th>Retur Beli Amount</th>
                @endpermission
            @endif
            @if ($returjual == true || $semua == true)
                @permission('read-hpp')
                    <th>Retur Jual Amount</th>
                @endpermission
            @endif
            @if ($disassembling == true || $semua == true)
                <th>Disassembling Stock</th>
                @permission('read-hpp')
                    <th>Disassembling Amount</th>
                @endpermission
            @endif
            @if ($assembling == true || $semua == true)
                <th>Assembling Stock</th>
                @permission('read-hpp')
                    <th>Assembling Amount</th>
                @endpermission
            @endif
            <th width="90px">Ending Stock</th>
            @permission('read-hpp')
                <th width="90px">Ending Amount</th>
                <th width="90px">Hpp</th>
            @endpermission
            @if ($lokasi == 'SEMUA')
                <th width="90px">Kode Lokasi</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->periode }}</td>
                <td>{{ $item->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->partnumber }}</td>
                <td>{{ $item->produk->kode_kategori }}</td>
                <td>{{ $item->begin_stock }}</td>
                @permission('read-hpp')
                    <td>{{ $item->begin_amount }}</td>
                @endpermission
                @if ($penerimaan == true || $semua == true)
                    <td>{{ $item->in_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->in_amount }}</td>
                    @endpermission
                @endif
                @if ($pemakaian == true || $semua == true)
                    <td>{{ $item->out_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->out_amount }}</td>
                    @endpermission
                @endif
                @if ($penjualan == true || $semua == true)
                    <td>{{ $item->sale_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->sale_amount }}</td>
                    @endpermission
                @endif
                @if ($transferin == true || $semua == true)
                    <td>{{ $item->trf_in }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->trf_in_amount }}</td>
                    @endpermission
                @endif
                @if ($transferout == true || $semua == true)
                    <td>{{ $item->trf_out }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->trf_out_amount }}</td>
                    @endpermission
                @endif
                @if ($adjustment == true || $semua == true)
                    <td>{{ $item->adjustment_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->adjustment_amount }}</td>
                    @endpermission
                @endif
                @if ($opname == true || $semua == true)
                    <td>{{ $item->stock_opname }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->amount_opname }}</td>
                    @endpermission
                @endif
                @if ($returbeli == true || $semua == true)
                    <td>{{ $item->retur_beli_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->retur_beli_amount }}</td>
                    @endpermission
                @endif
                @if ($returjual == true || $semua == true)
                    <td>{{ $item->retur_jual_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->retur_jual_amount }}</td>
                    @endpermission
                @endif
                @if ($disassembling == true || $semua == true)
                    <td>{{ $item->disassembling_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->disassembling_amount }}</td>
                    @endpermission
                @endif
                @if ($assembling == true || $semua == true)
                    <td>{{ $item->assembling_stock }}</td>
                    @permission('read-hpp')
                        <td>{{ $item->assembling_amount }}</td>
                    @endpermission
                @endif
                <td>{{ $item->ending_stock }}</td>
                @permission('read-hpp')
                    <td>{{ $item->ending_amount }}</td>
                    <td>{{ $item->hpp }}</td>
                @endpermission
                @if ($lokasi == 'SEMUA')
                    <td>{{ $item->kode_lokasi }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
