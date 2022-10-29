<!DOCTYPE html>
<html lang="en">
<head>
	<style> 
        
     @page {
            border: solid 1px #0b93d5;

        }

        .title {
            margin-top: 0.5cm;
        }
        .title h1 {
            text-align: left;
            font-size: 14pt;
            
        }
        

        .header {
            margin-left: 50px;
            margin-right: 0px;
            /*font-size: 10pt;*/
            padding-top: 10px;
            /*border: solid 1px #0b93d5;*/
        }

        .left {
            float: left;
        }

        .right {
            float: right;
        }

        .clearfix {
            overflow: auto;
        }

        .content {
                margin-left: 10px;
            padding-top: 10px
        }
        .catatan {
            font-size: 10pt;
        }

        footer {
                position: fixed; 
                top: 19cm; 
                left: 0cm; 
                right: 0cm;
                height: 2cm;
            }

        /* Table desain*/
        table.grid {
            width: 100%;
        }
</style>
</head>
<body>

	<table rules="rows" class="grid" style="font-size: 10pt; vertical-align: top; width: 27cm" border="1">
    <thead>
        <tr>
            <th>Kode Alat</th>
            <th>Nama Alat</th>
            <th>Merk</th>
            <th>Type</th>
            <th>Tahun</th>
            <th>No Aset Alat</th>
            <th>Kapasitas</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Terakhir diubah</th>
            <th>Diubah Oleh</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $item)
            <tr>
                <td>{{ $item->kode_alat }}</td>
                <td>{{ $item->nama_alat }}</td>
                <td>{{ $item->merk }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->tahun }}</td>
                <td>{{ $item->no_asset_alat }}</td>
                <td>{{ $item->kapasitas }}</td>
                <td>{{ $item->kode_lokasi }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->updated_at }}</td>
                <td>{{ $item->updated_by }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
<hr>
</body>
</html>